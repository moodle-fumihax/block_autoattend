<?php // $Id: grade_settings.php, 2005/09/21  Author: Ashok kumar Pola

// Modified by Fumi.Iseki     2007/03/23
//                            2012/04/20
//                            2013/04/09
//                            2014/06/02
//                            2019/08/19

require_once('../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$courseid = required_param('course',     PARAM_INTEGER);    // Course id
$classid  = optional_param('class', 0,   PARAM_INTEGER);
$submit   = optional_param('submit', '', PARAM_TEXT);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
    print_error('invalidsesskey');
}

$urlparams['course'] = $courseid;
if ($classid) $urlparams['class'] = $classid;
$PAGE->set_url('/blocks/autoattend/grade_settings.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/grade_settings.php';

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

$user = $DB->get_record('user', array('id'=>$USER->id));
if (!$user) {
    print_error('nosuchuser', 'block_autoattend');
}


/////////////////////////////////////////////////////////////////////////////////////////
//
function grade_settings_show_table($settings)
{
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '20px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('acronym','block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('title','block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('grade');
    $table->align[] = 'center';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('description');
    $table->align[] = 'center';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('display','block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '30px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('order','block_autoattend');
    $table->align[] = 'center';
    $table->size [] = '30px';
    $table->wrap [] = 'nowrap';

    //
    $seqnum = array(1=>1,2,3,4,5,6,7,8);
    $i = 0;
    foreach($settings as $set) { 
        $select_opt = '';
        foreach($seqnum as $num) {
            $selected = '';
            if ($num==$set->seqnum) $selected = 'selected="selected"';
            $select_opt .= '<option value="'.$num.'" '.$selected.'>'.$num.'</option>';
        }

        $checked = '';
        if ($set->display) $checked = 'checked="checked"';
        //
        $status = $set->status;
        //$table->data[$i][] = $i + 1;
        $table->data[$i][] = '<div style="font-weight:bold;">&nbsp;'.$status.'&nbsp;</div>';
        $table->data[$i][] = '<input type="text" name="acronym'.$status.'" size="2" maxlength="4" value="'.$set->acronym.'" />';
        $table->data[$i][] = '<input type="text" name="title'.$status.'" size="6" maxlength="12" value="'.$set->title.'" />';
        $table->data[$i][] = '<input type="text" name="grade'.$status.'" size="4" maxlength="4" value="'.$set->grade.'" />';
        $table->data[$i][] = '<input type="text" name="description'.$status.'" size="12" maxlength="24" value="'.$set->description.'" />';
        $table->data[$i][] = '<input type="checkbox" name="display'.$status.'" value="1" '.$checked.' />';
        $table->data[$i][] = '<select name="seqnum'.$status.'" autocomplete="off">'.$select_opt.'</select>';
        //$table->data[$i][] = html_writer::select($seqnum, 'seqnum'.$status, $set->seqnum, false);
        $i++;
    }
    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}



//////////////////////////////////////////////////////////////////////////////////////////
// Print header
$title = $course->shortname.': '.get_string('autoattend','block_autoattend');
if ($course->category) {
    $title.= ' '.get_string('grade_settings','block_autoattend');
} 

$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_button('&nbsp;');
//$PAGE->set_headingmenu();

echo $OUTPUT->header();


$currenttab = 'grade_settings';    
include('tabs.php');


if($isteacher) {
    if ($submit) {
        //
        $restore = optional_param('restore', 0, PARAM_INTEGER);
        //
        $settings = array();
        $settings['P'] = new stdClass();
        $settings['X'] = new stdClass();
        $settings['L'] = new stdClass();
        $settings['E'] = new stdClass();
        $settings['G'] = new stdClass();
        $settings['S'] = new stdClass();
        $settings['Y'] = new stdClass();

        $settings['P']->grade = optional_param('gradeP', 2, PARAM_INTEGER);
        $settings['X']->grade = optional_param('gradeX', 0, PARAM_INTEGER);
        $settings['L']->grade = optional_param('gradeL', 1, PARAM_INTEGER);
        $settings['E']->grade = optional_param('gradeE', 1, PARAM_INTEGER);
        $settings['G']->grade = optional_param('gradeG', 0, PARAM_INTEGER);
        $settings['S']->grade = optional_param('gradeS', 0, PARAM_INTEGER);
        $settings['Y']->grade = optional_param('gradeY', 0, PARAM_INTEGER);

        $settings['P']->acronym = optional_param('acronymP', get_string('Pacronym', 'block_autoattend'), PARAM_TEXT);
        $settings['X']->acronym = optional_param('acronymX', get_string('Xacronym', 'block_autoattend'), PARAM_TEXT);
        $settings['L']->acronym = optional_param('acronymL', get_string('Lacronym', 'block_autoattend'), PARAM_TEXT);
        $settings['E']->acronym = optional_param('acronymE', get_string('Eacronym', 'block_autoattend'), PARAM_TEXT);
        $settings['G']->acronym = optional_param('acronymG', get_string('Gacronym', 'block_autoattend'), PARAM_TEXT);
        $settings['S']->acronym = optional_param('acronymS', get_string('Sacronym', 'block_autoattend'), PARAM_TEXT);
        $settings['Y']->acronym = optional_param('acronymY', get_string('Yacronym', 'block_autoattend'), PARAM_TEXT);

        $settings['P']->title = optional_param('titleP', get_string('Ptitle', 'block_autoattend'), PARAM_TEXT);
        $settings['X']->title = optional_param('titleX', get_string('Xtitle', 'block_autoattend'), PARAM_TEXT);
        $settings['L']->title = optional_param('titleL', get_string('Ltitle', 'block_autoattend'), PARAM_TEXT);
        $settings['E']->title = optional_param('titleE', get_string('Etitle', 'block_autoattend'), PARAM_TEXT);
        $settings['G']->title = optional_param('titleG', get_string('Gtitle', 'block_autoattend'), PARAM_TEXT);
        $settings['S']->title = optional_param('titleS', get_string('Stitle', 'block_autoattend'), PARAM_TEXT);
        $settings['Y']->title = optional_param('titleY', get_string('Ytitle', 'block_autoattend'), PARAM_TEXT);

        $settings['P']->description = optional_param('descriptionP', get_string('Pdesc', 'block_autoattend'), PARAM_TEXT);
        $settings['X']->description = optional_param('descriptionX', get_string('Xdesc', 'block_autoattend'), PARAM_TEXT);
        $settings['L']->description = optional_param('descriptionL', get_string('Ldesc', 'block_autoattend'), PARAM_TEXT);
        $settings['E']->description = optional_param('descriptionE', get_string('Edesc', 'block_autoattend'), PARAM_TEXT);
        $settings['G']->description = optional_param('descriptionG', get_string('Sdesc', 'block_autoattend'), PARAM_TEXT);
        $settings['S']->description = optional_param('descriptionS', get_string('Sdesc', 'block_autoattend'), PARAM_TEXT);
        $settings['Y']->description = optional_param('descriptionY', get_string('Ydesc', 'block_autoattend'), PARAM_TEXT);
        //
        $settings['P']->display = optional_param('displayP', 0, PARAM_INT);
        $settings['X']->display = optional_param('displayX', 0, PARAM_INT);
        $settings['L']->display = optional_param('displayL', 0, PARAM_INT);
        $settings['E']->display = optional_param('displayE', 0, PARAM_INT);
        $settings['G']->display = optional_param('displayG', 0, PARAM_INT);
        $settings['S']->display = optional_param('displayS', 0, PARAM_INT);
        $settings['Y']->display = optional_param('displayY', 0, PARAM_INT);
        //
        $settings['P']->seqnum = optional_param('seqnumP', 1, PARAM_INT);
        $settings['X']->seqnum = optional_param('seqnumX', 2, PARAM_INT);
        $settings['L']->seqnum = optional_param('seqnumL', 3, PARAM_INT);
        $settings['E']->seqnum = optional_param('seqnumE', 4, PARAM_INT);
        $settings['G']->seqnum = optional_param('seqnumG', 5, PARAM_INT);
        $settings['S']->seqnum = optional_param('seqnumS', 6, PARAM_INT);
        $settings['Y']->seqnum = optional_param('seqnumY', 7, PARAM_INT);
        //
        autoattend_update_grade_settings($course->id, $settings, $restore);
        autoattend_update_grades($course->id);
    }
    //
    $settings = autoattend_get_grade_settings($course->id);
    include('html/grade_settings.html');
}

echo $OUTPUT->footer($course);

