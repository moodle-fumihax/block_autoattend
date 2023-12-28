<?php

//
// If you have not installed the attendance module, it is necessary to put paste the following link (this file) as an attendee link.
//   MOODLE_URL/blocks/autoattend/semiautobutton.php?course=[course id]
//


require_once('../../config.php');	
require_once(dirname(__FILE__).'/locallib.php');


$courseid = required_param('course', PARAM_INTEGER); 

$urlparams['course'] = $courseid;
$PAGE->set_url('/blocks/autoattend/semiautobutton.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/semiautobutton.php';


$course = $DB->get_record('course', array('id'=>$courseid));
if (!$course) {
	print_error('courseidwrong', 'block_autoattend');
}

require_login($course->id);
	
$context   = jbxl_get_course_context($course->id);
$isstudent = jbxl_is_student($USER->id, $context);



// for Student
if ($isstudent) {
	$userid = $USER->id;
	$classinfo = autoattend_get_user_class($userid, $course->id);
	//
	if ($classinfo->classid>=0) {
		$ntime = time();
		$sessions = autoattend_get_nowopen_sessions($course->id, $userid, 'S', $ntime);			// get semiauto sessions
		if ($sessions) {
			foreach ($sessions as $session) {
				$session = autoattend_update_session_state($courseid, $session, $ntime, false);	// not regist student
				if ($session->classid==0 or $session->classid==$classinfo->classid) {
					$student = $DB->get_record('autoattend_students', array('attsid'=>$session->id, 'studentid'=>$userid));
					if (empty($student)) {
						$student = autoattend_add_user_insession($session->id, $userid);
					}
					if ($student and $student->status=='Y') {
						redirect('semiautoattend.php?course='.$course->id.'&amp;attsid='.$session->id);
					}
				}
			}
		}
	}
}

redirect('index.php?course='.$course->id);

