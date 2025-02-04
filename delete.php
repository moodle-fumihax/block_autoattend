<?php // $Id: delete.php,v 1.3 2006/02/08 18:39:15 dlnsk Exp $

// Modified by Fumi.Iseki     2007/03/23
//                             2013/04/15


require_once('../../config.php');    
require_once($CFG->libdir.'/blocklib.php');
require_once(dirname(__FILE__).'/locallib.php');


$courseid = required_param('course', PARAM_INTEGER);  // Course id
$classid  = optional_param('class', 0, PARAM_INTEGER);
$confirm  = optional_param('confirm','', PARAM_INTEGER);
$action   = optional_param('action','',  PARAM_ALPHA);
$submit   = optional_param('submit','',  PARAM_TEXT);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
    jbxl_print_error('invalidsesskey');
}

$urlparams['course'] = $courseid;
$urlparams['class']  = $classid;
if ($confirm) $urlparams['confirm'] = $confirm;
if ($action)  $urlparams['action']  = $action;
$PAGE->set_url('/blocks/autoattend/delete.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/delete.php';

//
if (isset($formdata->cancel)) {
    redirect('index.php?course='.$courseid);
}

$course = $DB->get_record('course', array('id'=>$courseid));
if (!$course) {
    jbxl_print_error('courseidwrong', 'block_autoattend');
}

require_login($course->id);

$context = jbxl_get_course_context($course->id);
$isteacher = jbxl_is_teacher($USER->id, $context);
if (!$isteacher) {
    jbxl_print_error('notaccessnoteacher', 'block_autoattend');
}

$user = $DB->get_record('user', array('id'=>$USER->id));
if (!$user) {
    jbxl_print_error('nosuchuser', 'block_autoattend');
}


// Delete Record Check
foreach($_POST as $key => $value) {
    if (substr($key,0,6) == 'delete') {
          $delid = substr($key, 6, strlen($key)-6);
        if (is_numeric($delid)) {
            if ($att = $DB->get_record('autoattend_sessions', array('id'=>$delid))) {
                   $deletes[$delid]  = $att;
            }
        }
     }
}

if (empty($deletes)) {
    redirect('index.php?course='.$course->id.'&amp;class='.$classid);
}


// Print Header
if ($course->category) {
    $title = get_string('delete').' '.get_string('session','block_autoattend');
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


// Delete!!!
if (!empty($confirm)) {
    if (!empty($submit) && $submit==get_string('deleteok','block_autoattend')) {
        foreach($deletes as $key => $value) {
            $DB->delete_records('autoattend_students', array('attsid'=>$key));
            $DB->delete_records('autoattend_sessions', array('id'=>$key));
            $sdate = $deletes[$key]->sessdate;
            $stime = $deletes[$key]->starttime - $sdate;
            // for Log
            $str_sdate = jbxl_strftime(get_string('strftimedmyw',   'block_autoattend'), $sdate + $TIME_OFFSET);
            $str_stime = jbxl_strftime(get_string('strftimehourmin','block_autoattend'), $stime + $TIME_OFFSET);
            $loginfo = 'date='.$str_sdate.',time='.$str_stime.',method='.$deletes[$key]->method;
            $event = autoattend_get_event($context, 'delete', '', $loginfo);
            jbxl_add_to_log($event);
        }
        autoattend_update_grades($course->id);
        redirect('index.php?course='.$course->id.'&amp;class='.$classid, get_string('sessiondeleted','block_autoattend'), 1);
    }
}

$use_summertime = autoattend_use_summertime($course->id);

//// Table
include('html/delete.html');

echo $OUTPUT->footer($course);

