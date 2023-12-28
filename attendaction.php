<?php	// attendaction.php

// Midified by Fumi.Iseki 2007/03/21
//                        2013/04/12
//                        2019/08/19

/*
 出席データ（複数個）の修道更新処理

*/

require_once('../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$courseid = required_param('course', PARAM_INTEGER);
$classid  = optional_param('class',  0, PARAM_INTEGER);
$attsid   = optional_param('attsid', 0, PARAM_INTEGER);
$userid   = optional_param('userid', 0, PARAM_INTEGER);
$fromform = optional_param('fromform', '', PARAM_ALPHA);
$attend   = optional_param('attend', 'A',  PARAM_ALPHA);
$submit   = optional_param('esv', '',  PARAM_TEXT);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
    print_error('invalidsesskey');
}

$urlparams['course'] = $courseid;
if ($classid)  $urlparams['class']  = $classid;
if ($attsid)   $urlparams['attsid'] = $attsid;
if ($userid)   $urlparams['userid'] = $userid;
if ($fromform) $urlparams['fromform'] = $fromform;

$base_url = '/blocks/autoattend/grade_settings.php';
$PAGE->set_url($base_url, $urlparams);
$wwwMyURL = $CFG->wwwroot.$base_url;

// return
if (!empty($submit) && $submit==get_string('return', 'block_autoattend')) {
	if ($fromform=='updateUser') redirect('index.php?course='.$courseid.'&amp;student='.$userid);
	redirect('index.php?course='.$courseid);
}

if (empty($attsid) && $fromform=='updateAttendance') {
	redirect('index.php?course='.$courseid, get_string('missinfo', 'block_autoattend'), 3);
}
if (empty($userid) && $fromform=='updateUser') {
	redirect('index.php?course='.$courseid, get_string('missinfo', 'block_autoattend'), 3);
}

//
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

//
if (empty($userid)) $userid = $USER->id;
$user = $DB->get_record('user', array('id'=>$userid));
if (!$user) {
	print_error('nosuchuser', 'block_autoattend');
}
if ($fromform=='updateAttendance') {
	$sess = $DB->get_record('autoattend_sessions', array('id'=>$attsid));
}


$attsids 	 = array();	// stores ids
$status	 	 = array();	// stores status 
$remarks 	 = array();	// stores remarks
$prv_status	 = array();	// 更新画面に表示したデータ
$prv_remarks = array();	// 更新画面に表示したデータ


foreach($_POST as $key => $value) {
	//
	if (substr($key,0,8)=='statusid') {
		$aid = substr($key, 8, strlen($key)-8);	
		if (is_numeric($aid) and preg_match("/[PXLEGSY]/", $value)) {
			$attsids[] = $aid;
			$status[$aid] = $value;
		}
	}
	else if (substr($key,0,7)=='remarks') {
		$aid = substr($key, 7, strlen($key)-7);
		if (is_numeric($aid)) {
			$remarks[$aid] = htmlspecialchars($value, ENT_QUOTES);
		}
	}
	//
	else if (substr($key,0,11)=='prvstatusid') {
		$aid = substr($key, 11, strlen($key)-11);	
		if (is_numeric($aid) and preg_match("/[PXLEGSY]/", $value)) {
			$prv_status[$aid] = $value;
		}
	}
	else if (substr($key,0,10)=='prvremarks') {
		$aid = substr($key, 10, strlen($key)-10);
		if (is_numeric($aid)) {
			$prv_remarks[$aid] = htmlspecialchars($value, ENT_QUOTES);
		}
	}
}

/*
注意：
  更新のためにデータを表示している間にデータベースが書き換えられる可能性があるので，
  更新画面で変更したもののみデータベース更新する．（文章で書くと，至極当たり前!）
  以前はデータベースのデータと違っていた場合に更新していた．
*/
// 学生データの書き込み

$is_email_user = autoattend_is_email_user($courseid);
$result = false;

foreach($attsids as $att) {
	//
	if ($fromform=='updateUser') {
		$attsid = $att;
		$sess = $DB->get_record('autoattend_sessions', array('id'=>$attsid));
	}
	else {		// updateAttendance
		$userid = $att;
		$user = $DB->get_record('user', array('id'=>$userid));
	}

	$calledtime = time();
	$result = true;
	
	$std = $DB->get_record('autoattend_students', array('attsid'=>$attsid, 'studentid'=>$userid));
	//
	if ($std) {
		$rec = clone $std;

        if (!array_key_exists($att, $prv_status)) $prv_status[$att] = '';

		if ($status[$att]!=$prv_status[$att]) {
			$rec->status 	 = $status[$att];
			$rec->calledby	 = $USER->id;
			$rec->calledtime = $calledtime; 
			//
			if ($rec->status=='Y') $rec->called = 'D';
			else 				   $rec->called = 'M';
		}

		if ($status[$att]!=$prv_status[$att] or $remarks[$att]!=$prv_remarks[$att]) {
			if ($remarks[$att]!=$prv_remarks[$att]) $rec->remarks = $remarks[$att];		
			$rec->id 		   = $std->id;
			$rec->attsid 	   = $attsid;
			$rec->studentid    = $userid;
			$rec->sentemail    = $std->sentemail;
			$rec->timemodified = $calledtime;
		
			$sentemail = false;
			if ($is_email_user and $status[$att]!=$prv_status[$att]) {
				$rec->sentemail = 1;
				$sentemail = true;
			}

			$result = $DB->update_record('autoattend_students', $rec);
			if ($result) {
				if ($sentemail) autoattend_email_user($sess, $user, $rec->status, $courseid);
				//
				$loginfo = MANUAL_SUBMIT_LOG.',id='.$attsid.',user='.$userid.',status='.$status[$att].',called='.$rec->called;
				$event = autoattend_get_event($context, 'update', '', $loginfo);
				jbxl_add_to_log($event);
			}
		}
	} 
	//
	else {	// new student record
		if ($status[$att]=='Y') $called = 'D';	// 出席はまだ取られていない．授業が始まっていない．
		else  					$called = 'M';

		$rec = new stdClass(); 
		$rec->attsid 	   = $attsid;
		$rec->studentid    = $userid;
		$rec->status 	   = $status[$att];
		$rec->called 	   = $called;
		$rec->calledby 	   = $USER->id;
		$rec->calledtime   = $calledtime;
		$rec->sentemail    = 0;
		$rec->remarks 	   = $remarks[$att];		
		$rec->timemodified = $calledtime;

		$sentemail = false;
		if ($is_email_user) {
			$rec->sentemail = 1;
			$sentemail = true;
		}

    	$result = $DB->get_record('autoattend_students', array('attsid'=>$attsid, 'studentid'=>$userid));
    	if (empty($result)) {
			$result = $DB->insert_record('autoattend_students', $rec);
			if ($result) {
				if ($sentemail) autoattend_email_user($sess, $user, $rec->status, $courseid);
				//
				$loginfo = MANUAL_SUBMIT_LOG.',id='.$attsid.',user='.$userid.',status='.$status[$att].',called='.$called;
				$event = autoattend_get_event($context, 'update', '', $loginfo);
				jbxl_add_to_log($event);
			}
		}
	}
	unset($rec);

	//
	if (!$result) break;
}	


////////////////////////////////////
autoattend_update_grades($courseid);


// 結果の表示
if ($result) {
	$dsptm = 1;
	$mssg = get_string('attendsuccess', 'block_autoattend');
}
else {
	$dsptm = 5;
	$mssg = get_string('attenderror', 'block_autoattend');
}

$url_params = '?course='.$course->id.'&amp;class='.$classid.'&amp;attsid='.$attsid.'&amp;userid='.$userid.'&amp;attend='.$attend;
redirect($fromform.'.php'.$url_params, $mssg, $dsptm);

