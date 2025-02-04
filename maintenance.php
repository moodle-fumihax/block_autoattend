<?php // $Id: maintenance.php, 

// Modified by Fumi.Iseki     2019/08/18

require_once('../../config.php'); 
require_once(dirname(__FILE__).'/locallib.php'); 


$courseid = required_param('course',     PARAM_INTEGER);    // Course id
$classid  = optional_param('class', 0,   PARAM_INTEGER);
$submit   = optional_param('submit', '', PARAM_TEXT);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
    jbxl_print_error('invalidsesskey');
}

$urlparams['course'] = $courseid;
if ($classid) $urlparams['class'] = $classid;
$PAGE->set_url('/blocks/autoattend/maintenance.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/maintenance.php';
//$modeditURL = $CFG->wwwroot.'/course/modedit.php';

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


//////////////////////////////////////////////////////////////////////////////////////////
// Print header
$title = $course->shortname.': '.get_string('autoattend','block_autoattend');
if ($course->category) {
    $title.= ' '.get_string('maintenance','block_autoattend');
} 

$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_button('&nbsp;');
//$PAGE->set_headingmenu();

echo $OUTPUT->header();

$currenttab = 'maintenance';    
include('tabs.php');

$has_module = false;
$has_module_instance = false;

if($isteacher) {
    //
    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $has_module = true;
        $mod = autoattend_get_course_module($courseid);
        if ($mod) {
            $has_module_instance = true;
            $modeditURL = $CFG->wwwroot.'/course/modedit.php'.'?update='.$mod->id;
        }
    }

    include('html/maintenance.html');
}

echo $OUTPUT->footer($course);

