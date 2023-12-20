<?php 

//
// clean up autoattend_sessions and autoattend_students
//
// Created by Fumi.Iseki     2019/08/21
//

require_once('../../config.php');    
require_once($CFG->libdir.'/blocklib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/lib_ex.php');


$courseid = required_param('course',     PARAM_INTEGER);  // Course id
$confirm  = optional_param('confirm','', PARAM_INTEGER);
$action   = optional_param('action','',  PARAM_ALPHA);
$submit   = optional_param('submit','',  PARAM_TEXT);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
    print_error('invalidsesskey');
}

$urlparams['course'] = $courseid;
if ($confirm) $urlparams['confirm'] = $confirm;
if ($action)  $urlparams['action']  = $action;
$PAGE->set_url('/blocks/autoattend/deleteDB.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/deleteDB.php';

//
if (isset($formdata->cancel)) {
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
}

$course = $DB->get_record('course', array('id'=>$courseid));
if (!$course) {
    print_error('courseidwrong', 'block_autoattend');
}

// Check User
require_login($course->id);

$isadmin = jbxl_is_admin($USER->id);
if (!$isadmin) {
    print_error('notaccessnoadmin', 'block_autoattend');
}


// Print Header
$title = get_string('session','block_autoattend').' '.get_string('deletedb','block_autoattend');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(true);
$PAGE->set_button('&nbsp;');
//$PAGE->set_headingmenu();

echo $OUTPUT->header();


if (!empty($confirm)) {
    if (!empty($submit) && $submit==get_string('deletedbok','block_autoattend')) {
        $delyear  = required_param('delyear',  PARAM_INTEGER);
        $delmonth = required_param('delmonth', PARAM_INTEGER);
        $delday   = required_param('delday',   PARAM_INTEGER);
        $deldate  = mktime(0, 0, 0, $delmonth, $delday, $delyear) - $TIME_OFFSET;

        // Confirm
        if ($confirm==1) {
            $info = new stdClass();
            $info->delnum = $DB->count_records_select('autoattend_sessions', 'endtime<'.$deldate);
            $info->delstr = strftime(get_string('strftimedmy','block_autoattend'), $deldate);
            include('html/delete_confirmDB.html');
        }

        // Delete old items of sessions table
        else {  
            $num_sess = autoattend_delete_sessionsDB($deldate);
            $num_stdt = autoattend_cleanup_studentsDB();
            //
            echo "--------------------------------------------------<br />";
            echo 'autoattend_sessions: deleted '.$num_sess.' record(s).<br />';
            echo "--------------------------------------------------<br />";
            echo 'autoattend_students: deleted '.$num_stdt.' record(s).<br />';
            echo "--------------------------------------------------<br />";
            //
            notice(get_string('deleteddb','block_autoattend'), $CFG->wwwroot.'/course/view.php?id='.$course->id);
        }
    }
}

//
else {
    $date_name1 = get_string('date_1st_name', 'block_autoattend');
    $date_name2 = get_string('date_2nd_name', 'block_autoattend');
    $date_name3 = get_string('date_3rd_name', 'block_autoattend');

    //// forst Table
    include('html/deleteDB.html');
}

echo $OUTPUT->footer($course);

