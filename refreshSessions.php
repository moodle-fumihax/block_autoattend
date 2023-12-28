<?php // $Id: refreshSessions.php, v1.0 2007/03/27 Fumi.Iseki $


require_once('../../config.php');	
require_once(dirname(__FILE__).'/locallib.php');


$courseid = required_param('course', PARAM_INTEGER);
$classid  = optional_param('class', 0, PARAM_INTEGER);
$backurl  = optional_param('backurl', '', PARAM_URL);
$grades   = optional_param('grades', '', PARAM_ALPHA);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
	print_error('invalidsesskey');
}

$urlparams['course'] = $courseid;
if ($classid) $urlparams['class'] = $classid;
$PAGE->set_url('/blocks/autoattend/refreshSessions.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';


// コースの確認
$course = $DB->get_record('course', array('id'=>$courseid));
if (!$course) {
	print_error('courseidwrong', 'block_autoattend');
}

require_login($course->id);

$context   = jbxl_get_course_context($course->id);
$isteacher = jbxl_is_teacher($USER->id, $context);
$isassist  = false;

if (!$isteacher) {
	$isassist = jbxl_is_assistant($USER->id, $context);
	if (!$isassist) {
		print_error('notaccessstudent', 'block_autoattend');
	}
}

$ret = autoattend_update_sessions($courseid);
if ($grades)  autoattend_update_grades($courseid);
if ($backurl) redirect($backurl);

if (!isset($_SESSION)) session_start();
$_SESSION['update'] = false;

redirect($wwwBlock.'/index.php?course='.$course->id.'&amp;class='.$classid);

