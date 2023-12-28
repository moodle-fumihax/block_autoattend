<?php // $Id: returntoN.php,v 1.0 2007/03/27 Fumi.Iseki $

//
// 未了状態に戻る
//


require_once('../../config.php');	
require_once(dirname(__FILE__).'/locallib.php');


$courseid = required_param('course', PARAM_INTEGER);  // Course id
$attsid	  = required_param('attsid', PARAM_INTEGER);
$classid  = optional_param('class', 0, PARAM_INTEGER);
$confirm  = optional_param('confirm','', PARAM_INTEGER);
$submit   = optional_param('submit','',  PARAM_TEXT);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
	print_error('invalidsesskey');
}

$urlparams['course'] = $courseid;
$urlparams['attsid'] = $attsid;
if ($classid) $urlparams['class'] 	= $classid;
if ($confirm) $urlparams['confirm'] = $confirm;
$PAGE->set_url('/blocks/autoattend/returntoN.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/returntoN.php';


if (!empty($submit) && $submit==get_string('cancel')) {
	redirect('updateSession.php?course='.$courseid.'&amp;class='.$classid.'&amp;attsid='.$attsid);
}
	
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

$att = $DB->get_record('autoattend_sessions', array('id'=>$attsid));
if (!$att) {
	print_error('nosuchsession', 'block_autoattend');
}


// Print Header
if ($course->category) {
	$title = get_string('toNtitle','block_autoattend').' '.get_string('session','block_autoattend');
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

// Return to N
if (!empty($confirm) and $submit==get_string('toNok','block_autoattend')) {
	$rec = new stdClass();
	$rec->id	= $att->id;
	$rec->state = 'N';
	$result = $DB->update_record('autoattend_sessions', $rec);
	//if ($result) {
    //	$loginfo = 'id='.$att->id.',method='.$att->method.',state='.$att->state.',N';
    //	$event = autoattend_get_event($context, 'return', $urlparams);
    //	jbxl_add_to_log($event);
	//}
	autoattend_return_to_Y($att->id);
	//
	redirect('index.php?course='.$course->id.'&amp;class='.$classid);
}


///// Table
$use_summertime = autoattend_use_summertime($courseid);
include('html/returntoN.html');

echo $OUTPUT->footer($course);
