<?php // $Id: updateAttendance.php,v 1.10 2006/06/13 01:23:46 dlnsk Exp $

// Modified by Fumi.Iseki     2007/03/28
//                            2012/04/20
//                            2013/04/12
//                            2013/05/01
//                            2019/08/19


require_once('../../config.php');    
require_once($CFG->libdir.'/blocklib.php');
require_once(dirname(__FILE__).'/locallib.php');

//
define('PAGE_ROW_SIZE', $CFG->page_row_size);


$courseid = required_param('course', PARAM_INTEGER);      // Course id
$attsid   = required_param('attsid', PARAM_INTEGER);
$classid  = optional_param('class', 0,    PARAM_INTEGER);
$attend   = optional_param('attend','A',  PARAM_ALPHA);   // A: All, Z: X or Y
$sort     = optional_param('sort',  'firstname', PARAM_ALPHA);
$order    = optional_param('order', 'ASC',PARAM_ALPHA);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
    jbxl_print_error('invalidsesskey');
}

$wwwBlock  = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL  = $wwwBlock.'/updateAttendance.php';
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

$urlparams['course'] = $courseid;
$urlparams['attsid'] = $attsid;
$urlparams['class']  = $classid;
$urlparams['attend'] = $attend;
$urlparams['sort']   = $sort;
$urlparams['order']  = $order;

$this_url = new moodle_url('/blocks/autoattend/updateAttendance.php', $urlparams);
$PAGE->set_url($this_url);

$course = $DB->get_record('course', array('id'=>$courseid));
if (!$course) {
    jbxl_print_error('courseidwrong', 'block_autoattend');
}

require_login($course->id);

$context   = jbxl_get_course_context($course->id);
$isassist  = false;
$isteacher = jbxl_is_teacher($USER->id, $context);
if (!$isteacher) {
    $isassist = jbxl_is_assistant($USER->id, $context);
    if (!$isassist) {
           jbxl_print_error('notaccessstudent', 'block_autoattend');
    }
}

$user = $DB->get_record('user', array('id'=>$USER->id));
if (!$user) {
    jbxl_print_error('nosuchuser', 'block_autoattend');
}


///////////////////////////////////////////////////////////////////////////////
//
function updateAttendance_make_header(&$table, $settings, $url_options, $start, $end, $name_pattern, $order='', $summertime_mark='')
{
    global $CFG, $wwwMyURL;

    if (empty($order) or $order=='DESC') {
        $order = 'ASC';
    }    
    else $order = 'DESC';

    // name pattern
    $firstname = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=firstname&amp;order='.$order.'">'.get_string('firstname').'</a>';
    $lastname  = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=lastname&amp;order='.$order.'">' .get_string('lastname').'</a>';
    $fullnamehead = jbxl_get_fullnamehead($name_pattern, $firstname, $lastname, '/');  // 

    //
    $i = 0;
    $n = 4;
    if ($CFG->output_idnumber) $n++;
    foreach($settings as $set) {
        if ($set->display) {
            $cell   = $i + $n;
            //$status = get_string($set->status.'acronym', 'block_autoattend');
            $tabhead[] = "<a href=\"javascript:select_all_radio_in('td', 'cell c{$cell}', $start, $end);\"><u>$set->title</u></a>";
            $i++;
        }
    }

    unset($table->head);
    unset($table->align);
    unset($table->size);
    unset($table->wrap);

    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '20px';
    
    $table->head [] = '';
    $table->align[] = '';
    $table->size [] = '60px';
        
    $table->head [] = $fullnamehead;    // name title
    $table->align[] = 'left';
    $table->size [] = '160px';
    $table->wrap [2]= 'nowrap';

    if ($CFG->output_idnumber) {
        $table->head [] = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=idnumber&amp;order='.$order.'">ID</a>';
        $table->align[] = 'center';
        $table->size [] = '60px';
        $table->wrap [] = 'nowrap';
    }

    $table->head [] = get_string('classname', 'block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

    foreach ($tabhead as $hd) {
        $table->head [] = $hd;
        $table->align[] = 'center';
        $table->size [] = '20px';
        $table->wrap [] = 'nowrap';
    }

    $table->head [] = get_string('callmethod', 'block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

    //$table->head [] = get_string('calleddate', 'block_autoattend');
    //$table->align[] = 'center';
    //$table->size [] = '40px';
    //$table->wrap [] = 'nowrap';

    $table->head [] = get_string('calledtime', 'block_autoattend').$summertime_mark;
    $table->align[] = 'center';
    $table->size [] = '80px';
    $table->wrap [] = 'nowrap';

    //$table->head [] = get_string('ipaddress', 'block_autoattend');
    $table->head [] = get_string('client', 'block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '120px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('remarks', 'block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '120px';
    $table->wrap [] = 'nowrap';

    return;
}



function updateAttendance_show_table($course, $students, $sessdata, $settings, $attsid, $attend, $classid, $name_pattern, $order, $isteacher)
{
    global $CFG, $DB, $OUTPUT, $wwwBlock, $TIME_OFFSET;
    
    $use_summertime = autoattend_use_summertime($course->id);
    $summertime     = autoattend_get_summertime($attsid, $use_summertime)*ONE_HOUR_TIME;
    $summertime_mark = '&nbsp;';
    if ($summertime!=0) $summertime_mark = '*';

    $url_options = '?course='.$course->id.'&amp;class='.$classid.'&amp;attend='.$attend.'&amp;attsid='.$attsid;
    //$sessndate = jbxl_strftime(get_string('strftimedmshort', 'block_autoattend'), $sessdata->sessdate + $summertime + $TIME_OFFSET);
    $sessndate = jbxl_strftime(get_string('strftimedmshort', 'block_autoattend'), $sessdata->starttime + $summertime + $TIME_OFFSET);

    $table = new html_table();

    $i = 0;
    foreach($students as $student) {
        $classinfo = autoattend_get_user_class($student->id, $course->id, true);  // ID Only
        if ($classinfo->classid==$classid or $classid==0 or ($classid==NON_CLASSID and $classinfo->classid==0)) {
            //
            $att = $DB->get_record('autoattend_students', array('attsid'=>$attsid, 'studentid'=>$student->id));
            if ($att) {
                $status = $att->status;
                $called = $att->called;
            } 
            else {
                $status = 'Y';
                $called = 'D';
            }

            if ($attend=='A' or ($attend=='Z' and ($status=='X' or $status=='Y')) or $attend==$status) { 
                $i++;

                $table->data[$student->id][] = $i; 
                $pic_options = array('size'=>20, 'link'=>true, 'alttext'=>true, 'courseid'=>$course->id, 'popup'=>true);
                $username = jbxl_get_user_name($student->id, $name_pattern);
                $table->data[$student->id][] = $OUTPUT->user_picture($student->user, $pic_options);
                $table->data[$student->id][] = '<a href="'.$wwwBlock.'/index.php?course='.$course->id.'&amp;student='.$student->id.'" target="_blank">'.$username.'</a>';

                if ($CFG->output_idnumber) {
                    if (empty($student->idnumber)) $table->data[$student->id][] = '-';
                    else                           $table->data[$student->id][] = $student->idnumber;
                }
                $table->data[$student->id][] = $student->classname;

                foreach($settings as $set) {
                    if ($set->display) {
                        $checked = $set->status==$status ? 'checked' : '';
                        $table->data[$student->id][] = '<input name="statusid'.$student->id.'" '.'type="radio"  value="'.$set->status.'" '.$checked.' />';
                    }
                }
                $table->data[$student->id][] = get_string($called.'methodfull', 'block_autoattend');

                //
                if (!$att OR $att->status==='X' OR $att->status==='Y') {
                    $table->data[$student->id][] = get_string('novalue', 'block_autoattend');
                }
                else {
                    $calleddate = jbxl_strftime(get_string('strftimedmshort', 'block_autoattend'), $att->calledtime + $summertime + $TIME_OFFSET);
                    $calledtime = jbxl_strftime(get_string('strftimehmshort', 'block_autoattend'), $att->calledtime + $summertime + $TIME_OFFSET);
                    if ($sessndate===$calleddate) {
                        $table->data[$student->id][] = $calledtime;
                    }
                    else {
                        $table->data[$student->id][] = $calledtime.'&nbsp;('.$calleddate.')';
                    }
                }

                //
                if ($att) {
                    $ipaddr = $att->ipaddress ? $att->ipaddress : get_string('novalue', 'block_autoattend');
                    $ipurl  = autoattend_get_ipresolv_url($ipaddr);

                    if ($ipurl) $table->data[$student->id][] = "<a href=$ipurl target=_blank>$ipaddr</a>";
                    else {
                        // localip
                        if ($att->ipaddress && autoattend_disp_localhostname($course->id)) {
                            $table->data[$student->id][] = gethostbyaddr($ipaddr);
                        }
                        else {
                            $table->data[$student->id][] = $ipaddr;
                        }
                    }
                } 
                else {
                    $table->data[$student->id][] = get_string('novalue', 'block_autoattend');
                }
                //
                $remarks = $att ? $att->remarks : '';
                $input  = '<input type="text"   name="remarks'.$student->id.'" size="25" maxlength="40" value="'.$remarks.'" />';
                $prvvl1 = '<input type="hidden" name="prvstatusid'.$student->id.'" value="'.$status.'" />';
                $prvvl2 = '<input type="hidden" name="prvremarks'.$student->id. '" value="'.$remarks.'" />';
                $table->data[$student->id][] = $input.$prvvl1.$prvvl2;
                //
                 if ($i%PAGE_ROW_SIZE==0) {
                     updateAttendance_make_header($table, $settings, $url_options, $i-PAGE_ROW_SIZE, $i, $name_pattern, $order, $summertime_mark);
                     echo '<div align="center" style="overflow-x: auto;">';    // スクロールしません
                     echo html_writer::table($table);
                     echo '<div align="center">';
                     updateAttendance_show_button($isteacher);
                     echo '</div>';
                     echo '</div><br />';
                     unset($table->data);
                }
            }
        }
    }
    //
    if ($i%PAGE_ROW_SIZE!=0 or $i==0) {
         updateAttendance_make_header($table, $settings, $url_options, $i-$i%PAGE_ROW_SIZE, $i, $name_pattern, $order, $summertime_mark);
        echo '<div align="center" style="overflow-x: auto;">';
        echo html_writer::table($table);
        echo '<div align="center">';
        updateAttendance_show_button($isteacher);
        echo '</div>';
        echo '</div>';
    }

    return;
}


function updateAttendance_show_button($isteacher)
{
    if ($isteacher) { 
        echo '<input type="submit" name="esv" value="'.get_string('ok').'" />&nbsp;&nbsp';
    }
    echo '<input type="reset"  name="esv" value="'.get_string('reset').'" />&nbsp;&nbsp';
    echo '<input type="submit" name="esv" value="'.get_string('return', 'block_autoattend').'" />';
}


///////////////////////////////////////////////////////////////////////////////
//
autoattend_update_session($courseid, $attsid);

// Print Header
if ($course->category) {
    $title = get_string('updatesessionattend','block_autoattend').' '.get_string('autoattend','block_autoattend');
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

//
$currenttab = 'attendance';
include('tabs.php');

$sessdata = $DB->get_record('autoattend_sessions', array('id'=>$attsid));
if (!$sessdata) {
    jbxl_print_error('reqinfomiss', 'block_autoattend', $wwwBlock.'/index.php?course='.$courseid);
}

//$sort = ($sort=='firstname' ? 'firstname' : 'lastname');
$students = autoattend_get_attend_students($course->id, $sessdata->classid, $context, $sort, $order);
$settings = autoattend_get_grade_settings($course->id);

//
// Display Attendance Table
if ($students) {
    $name_pattern = autoattend_get_namepattern($courseid);
    $totalmember = autoattend_count_attend_students($courseid, $classid, $context);
    $sessdata->classid = $classid;
    $attcount = autoattend_count_class_students($sessdata, $courseid, $context, "status<>'Y' AND status<>'X'");
    //$attcount = autoattend_count_class_students($sessdata, $courseid, $context, "status='P'");
    //
    $classes = autoattend_get_session_classes($courseid);
    $url_options_base   = '?course='.$courseid.'&amp;attsid='.$attsid.'&amp;sort='.$sort.'&amp;order='.$order;
    $url_options_attend = $url_options_base.'&amp;class='.$classid;
    $url_options_class  = $url_options_base.'&amp;attend='.$attend;
    //
    $use_summertime = autoattend_use_summertime($courseid);
    include('html/updateAttendance.html');
} 
else {
    echo $OUTPUT->heading(get_string('nothingtodisplay'));
}

echo $OUTPUT->footer($course);

