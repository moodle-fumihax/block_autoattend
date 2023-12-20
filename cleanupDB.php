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
$PAGE->set_url('/blocks/autoattend/cleanupDB.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/cleanupDB.php';

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
$title = get_string('session','block_autoattend').' '.get_string('cleanupdb','block_autoattend');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(true);
$PAGE->set_button('&nbsp;');
//$PAGE->set_headingmenu();

echo $OUTPUT->header();


if (!empty($confirm)) {
    if (!empty($submit) && $submit==get_string('cleanupdbok','block_autoattend')) {
        //
        $num_sess = autoattend_cleanup_sessionsDB();
        $num_stdt = autoattend_cleanup_studentsDB();
        //
        echo "--------------------------------------------------<br />";
        echo 'autoattend_sessions: deleted '.$num_sess.' record(s).<br />';
        echo "--------------------------------------------------<br />";
        echo 'autoattend_students: deleted '.$num_stdt.' record(s).<br />';
        echo "--------------------------------------------------<br />";
        //
        notice(get_string('cleanupeddb','block_autoattend'), $CFG->wwwroot.'/course/view.php?id='.$course->id);
    }
}


//// Table
include('html/cleanupDB.html');

echo $OUTPUT->footer($course);

