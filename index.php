<?php // $Id: index.php,v 1.9 2006/04/16 20:32:51 dlnsk Exp $
    
// Modified by Fumi.Iseki   2007/03/19
//                          2012/04/12
//                          2014/11/27


require_once('../../config.php');    
require_once(dirname(__FILE__).'/locallib.php');


$courseid   = required_param('course',       PARAM_INTEGER); 
$classid    = optional_param('class', 0,     PARAM_INTEGER);
$action     = optional_param('action', '',   PARAM_ALPHA);
$from       = optional_param('from', '',     PARAM_ALPHA);
$studentid  = optional_param('student', 0,   PARAM_INTEGER);
$printing   = optional_param('printing', '', PARAM_ALPHA);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
    jbxl_print_error('invalidsesskey');
}
if ($classid<0) $classid = 0;

$urlparams['course'] = $courseid;
if ($classid)   $urlparams['class']    = $classid;
if ($action)    $urlparams['action']   = $action;
if ($studentid) $urlparams['student']  = $studentid;
if ($from)      $urlparams['from']     = $from;
if ($printing)  $urlparams['printing'] = $printing;
$PAGE->set_url('/blocks/autoattend/index.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/index.php';

//
$update = true;
if (!isset($_SESSION)) session_start();
if (isset($_SESSION['update'])) $update = $_SESSION['update'];
$_SESSION['update'] = true;


$course = $DB->get_record('course', array('id'=>$courseid));
if (!$course) {
    jbxl_print_error('courseidwrong', 'block_autoattend');
}

require_login($course->id);
    
$user = $DB->get_record('user', array('id'=>$USER->id));
if (!$user) {
    jbxl_print_error('nosuchuser', 'block_autoattend');
}

//
$context   = jbxl_get_course_context($course->id);
$isstudent = false;
$isassist  = false;
$isteacher = jbxl_is_teacher($USER->id, $context);
if (!$isteacher) {
    $isassist = jbxl_is_assistant($USER->id, $context);
    if (!$isassist) $isstudent = jbxl_is_student($USER->id, $context);
}

// DownLoad (Excel or Text Format)
if ($isteacher or $isassist) {  
    $classes = autoattend_get_session_classes($course->id);
    $datas   = autoattend_make_download_data($course->id, $classes, $classid);
    if($action=='excel') {
        if (autoattend_is_old_excel($course->id)) jbxl_set_excel_version('Excel2007');
        jbxl_download_data('xlsx', $datas);
        die();
    }
    else if($action=='text') {
        jbxl_download_data('txt', $datas);
        die();
    }
}


//////////////////////////////////////////////////////////////////////////////////////////
// Print Header
//
$title = $course->shortname.': '.get_string('autoattend','block_autoattend');
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);

// Printing
if ($printing) {
    $PAGE->set_pagelayout('print');
    echo $OUTPUT->header();

    if ($isteacher) {
        $student = $DB->get_record('user', array('id'=>$studentid));
        if ($student) {
            autoattend_print_user($student, $course, 'printing');
        }
        else {
            jbxl_print_error('nosuchuser', 'block_autoattend');
        }
    }
    else {
        autoattend_print_user($user, $course, 'printing');
    }
    die();
}
 

if ($update) {
    autoattend_update_sessions($course->id);
}
//
$PAGE->set_cacheable(true);
$PAGE->set_button('&nbsp;');
//$PAGE->set_headingmenu();

echo $OUTPUT->header();

// for Teacher or Assistant
if ($isteacher or $isassist) {
    $sessions = autoattend_get_sessions($course->id, $classid, true);

    if($studentid>0) {    // 個人データを出力
        $student = $DB->get_record('user', array('id'=>$studentid));
        if ($student) {
            autoattend_print_user($student, $course);
            //$event = autoattend_get_event($context, 'view', $urlparams);
            //jbxl_add_to_log($event);
        } 
        else {
            jbxl_print_error('nosuchuser', 'block_autoattend');
        }
    } 
    else {  // 授業一覧の表示
        $currenttab = 'sessions';
        include('tabs.php');
        //
        $classes = autoattend_get_session_classes($course->id);
        $url_options = '?course='.$course->id;
        if (empty($plugin)) $plugin = new stdClass();
        include_once($CFG->dirroot.'/blocks/autoattend/version.php');
        include_once($CFG->dirroot.'/blocks/autoattend/sessions_show_table.php');

        $use_summertime = autoattend_use_summertime($course->id);
        include('html/index_html.html');
    }
}

// for Student
else if ($isstudent) {
    $event = autoattend_get_event($context, 'view', $urlparams);
    jbxl_add_to_log($event);
    autoattend_print_user($user, $course);
}

// for Guest
else {
    echo $OUTPUT->heading(get_string('notaccessguest', 'block_autoattend'));
} 

echo $OUTPUT->footer($course);

