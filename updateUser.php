<?php 
// $Id: updateUser.php,v 2.0 2012/04/20 Fumi.Iseki $
// Modified from updateUser.php at attendance block
//

// Modified by Fumi.Iseki   2007/03/28
//                          2013/04/12

require_once('../../config.php');    
require_once($CFG->libdir.'/blocklib.php');
require_once(dirname(__FILE__).'/locallib.php');


define('PAGE_ROW_SIZE', $CFG->page_row_size);


$courseid = required_param('course', PARAM_INTEGER);
$userid   = required_param('userid', PARAM_INTEGER);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
    print_error('invalidsesskey');
}

$urlparams['course'] = $courseid;
$urlparams['userid'] = $userid;
$PAGE->set_url('/blocks/autoattend/updateUser.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/updateUser.php';

$course = $DB->get_record('course', array('id'=>$courseid));
if (!$course) {
    print_error('courseidwrong', 'block_autoattend');
}

require_login($course->id);

$context = jbxl_get_course_context($course->id);
$isteacher = jbxl_is_teacher($USER->id, $context);
if (!$isteacher) {
    print_error('notaccessnoteacher', 'block_autoattend');
}

$user = $DB->get_record('user', array('id'=>$userid));
if (!$user) {
    print_error('nosuchuser', 'block_autoattend');
}
    

///////////////////////////////////////////////////////////////////////////////
//
function updateUser_make_header(&$table, $settings, $start, $end)
{
    $i = 0;
    foreach($settings as $set) {
        if ($set->display) {
            $cell   = $i + 6;
            //$status = get_string($set->status.'acronym', 'block_autoattend');
            $tabhead[] = "<a href=\"javascript:select_all_radio_in('TD', 'cell c{$cell}', $start, $end);\"><u>$set->title</u></a>";
            $i++;
        }
    }

    unset($table->head);
    unset($table->align);
    unset($table->size);

    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '20px';
    
    $table->head [] = get_string('sessiondate', 'block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '40px';
    $table->wrap [1]= 'nowrap';

    $table->head [] = get_string('starttime', 'block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('endtime', 'block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('classname', 'block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '40px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('description', 'block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '40px';
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

    $table->head [] = get_string('client', 'block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '80px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('remarks','block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '120px';
    $table->wrap [] = 'nowrap';
    
    return;
}


function updateUser_show_table($attitems, $settings, $classid, $courseid)
{
    global $TIME_OFFSET;

    $use_summertime = autoattend_use_summertime($courseid);
 
    $table = new html_table();

    $i = 0;
    foreach($attitems as $att) {
        if ($att->classid==$classid or $att->classid==0) {
            $summertime_mark = '&nbsp;';
            $summertime = autoattend_get_summertime($att->id, $use_summertime)*ONE_HOUR_TIME;
            if ($summertime!=0) $summertime_mark = '*';

            $i++;
            $table->data[$att->id][] = $i.$summertime_mark; 
            //$table->data[$att->id][] = strftime(get_string('strftimedmyw',    'block_autoattend'), $att->sessdate  + $summertime + $TIME_OFFSET);
            $table->data[$att->id][] = strftime(get_string('strftimedmyw',    'block_autoattend'), $att->starttime + $summertime + $TIME_OFFSET);
            $table->data[$att->id][] = strftime(get_string('strftimehourmin', 'block_autoattend'), $att->starttime + $summertime + $TIME_OFFSET);
            $table->data[$att->id][] = strftime(get_string('strftimehourmin', 'block_autoattend'), $att->endtime   + $summertime + $TIME_OFFSET);
            $table->data[$att->id][] = autoattend_get_user_classname($att->classid);
            $table->data[$att->id][] = ($att->description ? $att->description : get_string('nodescription', 'block_autoattend'));

            foreach($settings as $set) {
                if ($set->display) {
                    $checked = $set->status==$att->status ? 'checked' : '';
                    $table->data[$att->id][] = '<input name="statusid'.$att->id.'" type="radio" value="'.$set->status.'" '.$checked.' />';
                }
            }

            if ($att->studentid) {
                $table->data[$att->id][] = get_string($att->called.'methodfull', 'block_autoattend');
                //$table->data[$att->id][] = $att->ipaddress ? $att->ipaddress : get_string('novalue','block_autoattend');
                //
                if ($att->ipaddress) {
                    if (!autoattend_is_localip($att->ipaddress) && autoattend_disp_localhostname($courseid)) {
                        $table->data[$att->id][] = gethostbyaddr($att->ipaddress);
                    }
                    else $table->data[$att->id][] = $att->ipaddress;
                }
                else $table->data[$att->id][] = get_string('novalue','block_autoattend');
            }
            else {
                $table->data[$att->id][] = ' - ';
                $table->data[$att->id][] = ' - ';
            }
            $input  = '<input type="text"   name="remarks'.$att->id.'" size="20" maxlength="40" value="'.$att->remarks.'" />';
            $prvvl1 = '<input type="hidden" name="prvstatusid'.$att->id.'" value="'.$att->status.'" />';
            $prvvl2 = '<input type="hidden" name="prvremarks'.$att->id. '" value="'.$att->remarks.'" />';
            $table->data[$att->id][] = $input.$prvvl1.$prvvl2;

            if ($i%PAGE_ROW_SIZE==0) {
                updateUser_make_header($table, $settings, $i-PAGE_ROW_SIZE, $i);
                echo html_writer::table($table);
                unset($table->data);
            }
        }
    }
    if ($i%PAGE_ROW_SIZE!=0 or $i==0) {
        updateUser_make_header($table, $settings, $i-$i%PAGE_ROW_SIZE, $i);
        echo html_writer::table($table);
    }

    return;
}



///////////////////////////////////////////////////////////////////////////////
//

// Print Header
if ($course->category) {
    $title = get_string('updateuserattend','block_autoattend').' '.get_string('autoattend','block_autoattend');
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
$classes = autoattend_get_session_classes($course->id);
$summary = autoattend_get_user_summary($user->id, $course->id);

$classname = $summary['classname'];
$classid   = $summary['classid'];
$attitems  = $summary['attitems'];
if (!$attitems) {
    print_error('reqinfomiss', 'block_autoattend', 'index.php?course='.$courseid);
}

$complete = $summary['complete'];
$settings = $summary['settings'];
$useratt = '';

foreach($settings as $set) {                                 
    if ($set->display) {
        $desc = get_string($set->status.'desc', 'block_autoattend');
        $useratt .= $desc.': <div style="font-weight:bold;">'.$summary[$set->status].'</div>'.'&nbsp;&nbsp;';
    }
} 

$gpercent = sprintf('%0.1f', $summary['gpercent']).' %';
$npercent = sprintf('%0.1f', $summary['npercent']).' %';
$grade    = $summary['grade'];    
$pgrade   = $summary['pgrade'];    
$maxgrade = $summary['maxgrade'];    

//
//$currenttab = 'attendance';
//include('tabs.php');

//
// Display Attendance Table
if ($attitems) {
    $disp_id = '';
    if ($CFG->output_idnumber) {
        if (empty($user->idnumber)) $disp_id = '&nbsp;[ - ]';
        else                        $disp_id = '&nbsp;['.$user->idnumber.']';
    }
    $name_pattern = autoattend_get_namepattern($course->id);
    $username = jbxl_get_user_name($user, $name_pattern);
    $pic_opts = array('size'=>30, 'link'=>true, 'alttext'=>true, 'courseid'=>$course->id, 'popup'=>true);
    include('html/updateUser.html');
} 
else {
    echo $OUTPUT->heading(get_string('nothingtodisplay'));
}

echo $OUTPUT->footer($course);

