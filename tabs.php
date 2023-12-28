<?php  // $Id: tabs.php,v 1.3 2006/02/22 12:10:14 dlnsk Exp $

//
// Modified by Fumi.Iseki    2012/04/20
//                           2013/04/17
//


require_once('jbxl/jbxl_moodle_tools.php');


if (empty($classid)) $classid = 0;
if (empty($user) or empty($course)) {
    print_error('notcallthis', 'block_autoattend');
}

if (empty($currenttab)) {
    $currenttab = 'sessions';
}

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';


$context   = jbxl_get_course_context($course->id);
$isassist  = false;
$isteacher = jbxl_is_teacher($USER->id, $context);
if (!$isteacher) $isassist = jbxl_is_assistant($USER->id, $context);

$inactive  = NULL;
$activetwo = NULL;
$toprow    = array();

$toprow[] = new tabobject('sessions', $wwwBlock.'/index.php?course='.$course->id.'&amp;class='.$classid, get_string('sessiontable', 'block_autoattend'));

if ($isteacher or $isassist) {
    //
    if ($isteacher) {
//        $toprow[] = new tabobject('add_one',   $wwwBlock.'/add_one.php?course='.  $course->id.'&amp;class='.$classid, get_string('add_one',  'block_autoattend'));
//        $toprow[] = new tabobject('add_multi', $wwwBlock.'/add_multi.php?course='.$course->id.'&amp;class='.$classid, get_string('add_multi','block_autoattend'));
        $toprow[] = new tabobject('add_session', $wwwBlock.'/add_session.php?course='.$course->id.'&amp;class='.$classid, get_string('add_session','block_autoattend'));
    }

    $toprow[] = new tabobject('report', $wwwBlock.'/report.php?course='.$course->id.'&amp;class='.$classid.'&amp;refresh=1', get_string('report','block_autoattend'));

    if ($isteacher) {
        $toprow[] = new tabobject('class_division', $wwwBlock.'/class_division.php?course='.$course->id, get_string('students_list', 'block_autoattend'));
        $toprow[] = new tabobject('class_settings', $wwwBlock.'/class_settings.php?course='.$course->id, get_string('class_settings','block_autoattend'));
        $toprow[] = new tabobject('grade_settings', $wwwBlock.'/grade_settings.php?course='.$course->id, get_string('grade_settings','block_autoattend'));
    }

    if ($currenttab=='attendance') $toprow[] = new tabobject('attendance', ' ', get_string('attendtable','block_autoattend'));
    if ($currenttab=='update')     $toprow[] = new tabobject('update', ' ', get_string('updatesession','block_autoattend'));
    if ($isteacher) {
        $toprow[] = new tabobject('maintenance',    $wwwBlock.'/maintenance.php?course='.$course->id,    get_string('maintenance',   'block_autoattend'));
    }

    //
    $toprow[] = new tabobject('', $CFG->wwwroot.'/course/view.php?id='.$course->id, get_string('returnto_course','block_autoattend'));
}

$tabs = array($toprow);
echo '<table align="center" style="margin-bottom:0.0em;"><tr><td>';
print_tabs($tabs, $currenttab, $inactive, $activetwo);
echo '</td></tr></table>';

