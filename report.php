<?php // $Id: report.php,v 1.11 2006/06/13 01:23:45 dlnsk Exp $

// Modified by Fumi.Iseki 2007/03/27
//                        2012/04/20
//                        2013/04/12
//                        2014/06/05


require_once('../../config.php');    
require_once($CFG->libdir.'/blocklib.php');
require_once(dirname(__FILE__).'/locallib.php');    

//
define('PAGE_ROW_SIZE', $CFG->page_row_size);
define('PAGE_COLUMN_SIZE',  $CFG->page_column_size);

$courseid = required_param('course',        PARAM_INTEGER);  // Course ID
$classid  = optional_param('class',   0,    PARAM_INTEGER);  // Class ID
$current  = optional_param('current', 0,    PARAM_INTEGER);  // shown date or time
$sort     = optional_param('sort', 'firstname', PARAM_ALPHA);
$order    = optional_param('order', 'ASC',  PARAM_ALPHA);
$refresh  = optional_param('refresh','0',   PARAM_INTEGER);  // refresh sessions
$action   = optional_param('action', '',    PARAM_ALPHA);
$viewmode = optional_param('viewmode','all',PARAM_ALPHA);    // View mode ('all', 'months', 'weeks') 

if (($formdata = data_submitted()) and !confirm_sesskey()) {
    print_error('invalidsesskey');
}

//
$wwwBlock  = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL  = $wwwBlock.'/report.php';
$wwwParams = jbxl_get_url_params_array($_SERVER["REQUEST_URI"]);

if (!$sort) {
    if (array_key_exists('sort', $wwwParams)) {
        $sort = $wwwParams['sort'];
    }
    else {
        if ($CFG->output_idnumber) $sort = 'idnumber';
        else                       $sort = 'lastname';
    }
}
/*
if ($sort=='percent') {
    $altsort  = 'percent';
    $altorder = $order;
    $sort     = 'lastname';
    $order    = 'ASC';
}
else {
    $altsort  = '';
    $altorder = '';
}
*/

//
$urlparams['course'] = $courseid;
if ($classid)  $urlparams['class']    = $classid;
if ($viewmode) $urlparams['viewmode'] = $viewmode;
if ($current)  $urlparams['current']  = $current;
if ($sort)     $urlparams['sort']     = $sort;
if ($order)    $urlparams['order']    = $order;
if ($action)   $urlparams['action']   = $action;
if ($classid<0 and $classid!=NON_CLASSID) $classid = 0;

$PAGE->set_url('/blocks/autoattend/report.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$courseid));
if (!$course) {
    print_error('courseidwrong', 'block_autoattend');
}

require_login($course->id);

$user = $DB->get_record('user', array('id'=>$USER->id));
if (!$user) {
    print_error('nosuchuser', 'block_autoattend');
}

//
$context   = jbxl_get_course_context($course->id);
$isassist  = false;
$isteacher = jbxl_is_teacher($USER->id, $context);
if (!$isteacher) $isassist = jbxl_is_assistant($USER->id, $context);

$settings = autoattend_get_grade_settings($courseid);

//
// DownLoad (Excel or Text Format)
if ($isteacher or $isassist) {
    $classes = autoattend_get_session_classes($course->id);
    $datas   = autoattend_make_download_data($course->id, $classes, $classid, $viewmode, $current, 'all', null, $sort, $order);
    if($action=='excel') {
        if (autoattend_is_old_excel($course->id)) jbxl_set_excel_version('Excel2007');
        jbxl_download_data('xlsx', $datas);
        die();
    }
    else if($action=='text') {
        jbxl_download_data('txt', $datas);
        die();
    }
}

/////////////////////////////////////////////////////
if ($refresh) {
    autoattend_update_sessions($courseid);
}


// Print header
if ($course->category) {
    $title = $course->shortname.': '.get_string('autoattend','block_autoattend').' '.get_string('report','block_autoattend');
} 
else {
    $title = $course->shortname.': '.get_string('autoattend','block_autoattend');
}

$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_button('&nbsp;');
//$PAGE->set_headingmenu();

echo $OUTPUT->header();

$currenttab = 'report';    
include('tabs.php');


//////////////////////////////////////////////////////////////////////////////////////////////
//
function report_make_header(&$table, $course_sess, $classes, $settings, $url_options, $name_pattern, $order='', $use_summertime=false)
{
    global $CFG, $TIME_OFFSET;

    $wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
    $wwwMyURL = $wwwBlock.'/report.php';

    if (empty($order) or $order=='DESC') {
        $order = 'ASC';
    }
    else $order = 'DESC';

    $firstname = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=firstname&amp;order='.$order.'">'.get_string('firstname').'</a>';
    $lastname  = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=lastname&amp;order='.$order.'">' .get_string('lastname').'</a>';
    $fullnamehead = jbxl_get_fullnamehead($name_pattern, $firstname, $lastname, '/');
            
    unset($table->head);
    unset($table->align);
    unset($table->size);
    unset($table->wrap);

    // Header
    $table->head [] = '';
    $table->align[] = '';
    $table->size [] = '20px';
    $table->wrap [] = 'nowrap';

    $table->head [] = $fullnamehead;
    $table->align[] = 'left';
    $table->size [] = '140px'; 
    $table->wrap [] = 'nowrap';

    if ($CFG->output_idnumber) {
        $table->head [] = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=idnumber&amp;order='.$order.'">ID</a>';
        $table->align[] = 'center';
        $table->size [] = '60px'; 
        $table->wrap [] = 'nowrap';
    }

    if ($classes) {
        $table->head [] = get_string('classname','block_autoattend');
        $table->align[] = 'center';
        $table->size [] = '80px'; 
        $table->wrap [] = 'nowrap';
    }

    //$table->head [] = get_string('attendgradeshort','block_autoattend');
    $table->head [] = 'G';
    $table->align[] = 'center';
    $table->size [] = '20px';
    $table->wrap [] = 'nowrap';

//    $table->head [] = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=percent&amp;order='.$order.'">%</a>';
    $table->head [] = 'G%';
    $table->align[] = 'center';
    $table->size [] = '40px';
    $table->wrap [] = 'nowrap';

    $table->head [] = 'N%';
    $table->align[] = 'center';
    $table->size [] = '40px';
    $table->wrap [] = 'nowrap';

    for ($i=0; $i<5; $i++) {
        $table->align[] = 'center';
        $table->size [] = '20px';
        $table->wrap [] = 'nowrap';
    }

    foreach($settings as $set) if ($set->display) array_push($table->head, $set->title);

    if (!empty($course_sess)) {
        $i = 0;
        foreach($course_sess as $sessdata) {
            $summertime_mark = '&nbsp;';
            $summertime = autoattend_get_summertime($sessdata->id, $use_summertime)*ONE_HOUR_TIME;
            if ($summertime!=0) $summertime_mark = '*';

            if ($i>0 and $i%PAGE_COLUMN_SIZE==0) {
                $table->head [] = $fullnamehead;
                $table->align[] = 'left';
                $table->size [] = '180px'; 
                $table->wrap [] = 'nowrap';
            }
            //
            $table->head [] = '<a href="'.$wwwBlock.'/updateAttendance.php'.$url_options.'&amp;sessdate='.$sessdata->sessdate.'&amp;attsid='.$sessdata->id.'">'.
                              strftime(get_string('strftimedmshort','block_autoattend'), $sessdata->sessdate + $summertime + $TIME_OFFSET).'</a>'.$summertime_mark;
            $table->align[] = 'center';
            $table->size [] = '40px';
            $table->wrap [] = 'nowrap';
            $i++;
        }
    }

    return;
}



//
function report_show_table($course, $students, $course_sess, $classes, $settings, $url_options, $name_pattern, $order)
{
    global $DB, $CFG, $OUTPUT, $wwwBlock;
    
    $use_summertime = autoattend_use_summertime($course->id);

    $table = new html_table();

    $i = 0;
    foreach($students as $student) {
        $i++;

        $pic_options = array('size'=>20, 'link'=>true, 'alttext'=>true, 'courseid'=>$course->id, 'popup'=>true);
        $username = jbxl_get_user_name($student->id, $name_pattern);
        $table->data[$student->id][] = $OUTPUT->user_picture($student->user, $pic_options);
        $table->data[$student->id][] = '<a href="'.$wwwBlock.'/index.php?course='.$course->id.'&amp;student='.$student->id.'" target="_blank">'.$username.'</a>';

        if ($CFG->output_idnumber) {
            if (empty($student->idnumber)) $table->data[$student->id][] = '-'; 
            else                           $table->data[$student->id][] = $student->idnumber; 
        }

        $user_summary = autoattend_get_user_summary($student->id, $course->id);
        if ($classes) $table->data[$student->id][] = $user_summary['classname'];
        $table->data[$student->id][] = isset($user_summary['grade'])    ? $user_summary['grade'] : 0; 
        $table->data[$student->id][] = isset($user_summary['gpercent']) ? $user_summary['gpercent'].'%' : '';
        $table->data[$student->id][] = isset($user_summary['npercent']) ? $user_summary['npercent'].'%' : '';
       
        foreach($settings as $set) {
            if ($set->display) $table->data[$student->id][] = isset($user_summary[$set->status]) ? $user_summary[$set->status] : '';
        }

        $j = 0;
        foreach($course_sess as $sessdata) {
            if ($j>0 and $j%PAGE_COLUMN_SIZE==0) {
                $table->data[$student->id][] = '<a href="'.$wwwBlock.'/index.php?course='.$course->id.'&amp;student='.$student->id.'" target="_blank">'.$username.'</a>';
            }
            //
            $j++;
            $att = $DB->get_record('autoattend_students', array('attsid'=>$sessdata->id, 'studentid'=>$student->id));
            if ($att and ($sessdata->classid==$student->classid or $sessdata->classid==0)) {
                $table->data[$student->id][] = $settings[$att->status]->acronym;
            } 
            else {
                $table->data[$student->id][] = get_string('novalue','block_autoattend');
            }
        }
        //
        if ($i%PAGE_ROW_SIZE==0) {
            report_make_header($table, $course_sess, $classes, $settings, $url_options, $name_pattern, $order, $use_summertime);
            echo '<div align="center" style="overflow-x: auto;">';
            echo html_writer::table($table);
            echo '</div><br /><br />';
            unset($table->data);
        }
    }

    if ($i%PAGE_ROW_SIZE!=0 or $i==0) {
        report_make_header($table, $course_sess, $classes, $settings, $url_options, $name_pattern, $order, $use_summertime);
        echo '<div align="center" style="overflow-x: auto;">';
        echo html_writer::table($table);
        echo '</div><br /><br />';
    }

    return;
}


//////////////////////////////////////////////////////////////////////////////////////////////
//
if ($isteacher or $isassist) {
    //
    if      ($classid==0)           $where_classid = '';    // ALL Class
    else if ($classid==NON_CLASSID) $where_classid = " AND classid=0";
    else                            $where_classid = " AND (classid=$classid OR classid=0)";

    $name_pattern = autoattend_get_namepattern($course->id);

    $students = autoattend_get_attend_students($course->id, $classid, $context, $sort, $order);

    $rec = $DB->get_record_sql("SELECT MIN(sessdate) AS min, MAX(sessdate) AS max ".
                                       " FROM {$CFG->prefix}autoattend_sessions WHERE courseid={$course->id}".$where_classid);
    $firstdate = $rec->min + $TIME_OFFSET;
    $lastdate  = $rec->max + $TIME_OFFSET;
    if ($current==0) $current = time();
    list(,,,$sday, $wday, $smonth, $syear) = array_values(getdate($firstdate));
    if ($wday == 0) $wday = 7;
    $startdate = mktime(0, 0, 0, $smonth, $sday-$wday+1, $syear);
    
    //
    $options['all']    = get_string('alltaken',   'block_autoattend');
    $options['weeks']  = get_string('everyweeks', 'block_autoattend');
    $options['months'] = get_string('everymonths','block_autoattend');
    $viewurl  = $wwwMyURL.'?course='.$course->id.'&amp;class='.$classid.'&amp;sort='.$sort.'&amp;order='.$order;
    $classurl = $wwwMyURL.'?course='.$course->id.'&amp;viewmode='.$viewmode.'&amp;sort='.$sort.'&amp;order='.$order;
    $classes = autoattend_get_session_classes($course->id);

    //
    $date_title = '';
    $weeks = array();
    if ($viewmode==='weeks') {
        $startdate = mktime(0, 0, 0, $smonth, $sday-$wday+1, $syear);
        $format = get_string('strftimedmshort','block_autoattend');
        //
        for ($i=1, $monday=$startdate; $monday<=$lastdate; $i++, $monday+=ONE_WEEK_TIME) {
            if ($DB->count_records_select('autoattend_sessions', "courseid={$course->id}".$where_classid.
                                " AND sessdate >= ".($monday - $TIME_OFFSET)." AND sessdate < ".($monday + ONE_WEEK_TIME - $TIME_OFFSET))) {
                $weeks[] = $monday;
            }
        }
    } 
    //
    elseif ($viewmode==='months') {
        $startdate = mktime(0, 0, 0, $smonth, 1, $syear);
        $format = '%B';
        $monday = $startdate; 
        //
        for ($i=1; $monday<=$lastdate; $i++, $monday=mktime(0, 0, 0, $smonth-1+$i, 1, $syear)) {
            if ($DB->count_records_select('autoattend_sessions', "courseid={$course->id}".$where_classid. 
                                " AND sessdate>=".($monday - $TIME_OFFSET)." AND sessdate<".(mktime(0, 0, 0, $smonth+$i, 1, $syear)-$TIME_OFFSET))) {
                $weeks[] = $monday;
            }
        }
    }

    $found = false;
    for ($i=count($weeks)-1; $i>=0; $i--) {
        if ($weeks[$i] <= $current+$TIME_OFFSET && !$found) {
            $found = true;
            $current = $weeks[$i] - $TIME_OFFSET;
            $date_title = '<div style="font-weight:bold;">'.strftime($format, $weeks[$i]).'</div> | '.$date_title;
        } 
        else {
            $date_title = '<a href="'.$wwwMyURL.'?course='.$course->id.'&amp;class='.$classid.'&amp;current='.($weeks[$i]-$TIME_OFFSET).
                    '&amp;sort='.$sort.'&amp;order='.$order.'&amp;viewmode='.$viewmode.'">'.strftime($format, $weeks[$i]).'</a> | '."\n".$date_title;
        }
    }

    //
    $where = "courseid={$course->id}".$where_classid;
    if ($viewmode==='weeks') {
        $where .= " AND sessdate >= $current AND sessdate < ".($current + ONE_WEEK_TIME);
    } 
    elseif ($viewmode==='months') {
        $nxtmon = mktime(0, 0, 0, date('m', $current+$TIME_OFFSET)+1, 1, date('Y', $current+$TIME_OFFSET)) - $TIME_OFFSET;
        $where .= " AND sessdate >= $current AND sessdate < ".$nxtmon;
    } 

    $course_sess = '';
    if ($students) {
        $course_sess = $DB->get_records_select('autoattend_sessions', $where, null, 'sessdate, starttime ASC');
    }

    /////// Table
    $url_options_class = '?course='.$courseid.'&amp;viewmode='.$viewmode.'&amp;current='.$current.'&amp;sort='.$sort.'&amp;order='.$order;
    $url_options_table = '?course='.$courseid.'&amp;viewmode='.$viewmode.'&amp;current='.$current.'&amp;class='.$classid;
    include('html/report.html');
}

echo $OUTPUT->footer($course);

