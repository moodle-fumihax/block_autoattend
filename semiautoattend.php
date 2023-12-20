<?php

// by Fumi.Iseki 2007/03/28
//               2012/04/19
//               2013/04/14
//               2016/01/04

require_once('../../config.php');	
require_once(dirname(__FILE__).'/locallib.php');	


$courseid = required_param('course',      PARAM_INTEGER);  // Course id
$attsid	  = required_param('attsid',      PARAM_INTEGER);
$classid  = optional_param('class', 0, 	  PARAM_INTEGER);
$checkkey = optional_param('checkkey','', PARAM_ALPHA);
$submit   = optional_param('submit','',   PARAM_TEXT);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
	print_error('invalidsesskey');
}

//
//session_get_instance()->write_close();

$urlparams['course'] = $courseid;
$urlparams['attsid'] = $attsid;
if ($classid)  $urlparams['class'] 	  = $classid;
if ($checkkey) $urlparams['checkkey'] = $checkkey;
$PAGE->set_url('/blocks/autoattend/semiautoattend.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/semiautoattend.php';


if (!empty($submit) && $submit==get_string('cancel')) {
	redirect('index.php?course='.$courseid.'&amp;class='.$classid);
}

$course = $DB->get_record('course', array('id'=>$courseid));
if (!$course) {
	print_error('courseidwrong', 'block_autoattend');
}

require_login($course->id);

$context = jbxl_get_course_context($course->id);
$isguest = isguestuser();
if ($isguest) {
	print_error('notaccessguest', 'block_autoattend');
}

$user = $DB->get_record('user', array('id'=>$USER->id));
if (!$user) {
	print_error('nosuchuser', 'block_autoattend');
}

$att = $DB->get_record('autoattend_sessions', array('id'=>$attsid));
if (!$att) {
	print_error('nosuchsession', 'block_autoattend');
}

$iperrmesg	= "";
$keyerrmesg = "";
$submitmesg	= "";
$attendkey  = $att->attendkey;

// Print Header
if ($course->category) {
	$title = get_string('submitattend','block_autoattend').' '.get_string('session','block_autoattend');
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


// Cancel !!
if (isset($formdata->cancel)) {
	redirect($wwwBlock.'/index.php?course='.$course->id.'&amp;class='.$classid);
}


// Submit!!
if (isset($formdata->submit)) {
	if ($att->method!='S' or $att->state!='O') {
		print_error('nosuchsession', 'block_autoattend');
	}
	if (!$stdnt = $DB->get_record('autoattend_students', array('attsid'=>$att->id, 'studentid'=>$user->id))) {
		print_error('nosuchuser', 'block_autoattend');
	}

	if (($checkkey and $attendkey==$checkkey) or $attendkey=='') {
		$iperrmesg = '';
		if ($att->allowip!='' or $att->denysameip) $iperrmesg = autoattend_check_invalid_semiautoip($att);
		if (!$iperrmesg) {
			$ntime  = time();
			$status = 'P';
			if ($att->latetime!=0) {
				$ctime = $att->starttime + $att->latetime;
				if ($ntime > $ctime) $status = 'L';
			}
			$rec = new stdClass();
			$rec->id 	 	 = $stdnt->id;
			$rec->attsid 	 = $att->id;
			$rec->studentid  = $stdnt->studentid;	
			$rec->status	 = $status;
			$rec->called	 = 'S';
			$rec->calledby   = CALLED_BY_SEMIAUTO;
			$rec->calledtime = $ntime;
			$rec->sentemail  = $stdnt->sentemail;
			$rec->remarks    = $stdnt->remarks;
			$rec->ipaddress  = getremoteaddr();

			// $status は P か L
			$sentemail = false;
			if (autoattend_is_email_user($courseid) and !$rec->sentemail) {
				$rec->sentemail = 1;
				$sentemail = true;
			}

			$result = $DB->update_record('autoattend_students', $rec);
			if ($result) {
				if ($sentemail) autoattend_email_user($att, $user, $status, $courseid);
				//
				$loginfo = SEMIAUTO_SUBMIT_LOG.',id='.$att->id.',user='.$stdnt->studentid.',status='.$rec->status.',ip='.$rec->ipaddress;
				$event = autoattend_get_event($context, 'submit', '', $loginfo);
				jbxl_add_to_log($event);
				redirect($wwwBlock.'/index.php?course='.$course->id.'&amp;class='.$classid, get_string('attendsuccess', 'block_autoattend'), 1);
			}
			else {
				$loginfo = SEMIAUTO_SUBMIT_LOG.',id='.$att->id.',user='.$stdnt->studentid.',DB Error';
				$event = autoattend_get_event($context, 'submit', '', $loginfo);
				jbxl_add_to_log($event);
				redirect($wwwBlock.'/index.php?course='.$course->id.'&amp;class='.$classid, get_string('attenderror', 'block_autoattend'), 5);
			}
		}
		else {
			$ipaddr  = getremoteaddr();
			$loginfo = SEMIAUTO_SUBMIT_LOG.',id='.$att->id.',user='.$stdnt->studentid.',IP Error('.$ipaddr.')';
			$event = autoattend_get_event($context, 'submit', '', $loginfo);
			jbxl_add_to_log($event);
		}
	}
	//
	else {
		$loginfo = SEMIAUTO_SUBMIT_LOG.',id='.$att->id.',user='.$stdnt->studentid.',Key Error('.$checkkey.')';
		$event = autoattend_get_event($context, 'submit', '', $loginfo);
		jbxl_add_to_log($event);
		$keyerrmesg = get_string('mismatchkey', 'block_autoattend');
	}
}
//
else {
	$loginfo = SEMIAUTO_SUBMIT_LOG.',id='.$att->id;
	//$event = autoattend_get_event($context, 'submit', '', $loginfo);
	//jbxl_add_to_log($event);
}


//// Table
$use_summertime = autoattend_use_summertime($courseid);
include('html/semiautoattend.html');

echo $OUTPUT->footer($course);

