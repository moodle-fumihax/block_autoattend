<?php 

//
// delete duplicate records from autoattend_students
//
// Created by Fumi.Iseki     2014/05/11
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
$PAGE->set_url('/blocks/autoattend/repairDB.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/repairDB.php';

//
if (isset($formdata->cancel)) {
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
}

$course = $DB->get_record('course', array('id'=>$courseid));
if (!$course) {
    print_error('courseidwrong', 'block_autoattend');
}

// check user
require_login($course->id);

$user = $DB->get_record('user', array('id'=>$USER->id));
if (!$user) {
    print_error('nosuchuser', 'block_autoattend');
}

$context = jbxl_get_course_context($course->id);
$isteacher = jbxl_is_teacher($USER->id, $context);
if (!$isteacher) {
    print_error('notaccessnoteacher', 'block_autoattend');
}


// Print Header
$title = get_string('session','block_autoattend').' '.get_string('repairdb','block_autoattend');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(true);
$PAGE->set_button('&nbsp;');
//$PAGE->set_headingmenu();

echo $OUTPUT->header();


if (!empty($confirm)) {
    if (!empty($submit) && $submit==get_string('repairdbok','block_autoattend')) {
        //
        autoattend_repairDB();
        //
        redirect($CFG->wwwroot.'/course/view.php?id='.$course->id, get_string('repaireddb','block_autoattend'), 3);
    }
}


//// Table
include('html/repairDB.html');

echo $OUTPUT->footer($course);

