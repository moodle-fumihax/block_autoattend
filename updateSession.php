<?php // $Id: updateSession.php,v 1.3 2006/02/02 09:28:06 dlnsk Exp $

// Modified by Fumi.Iseki   2007/03/19
//                          2013/04/05

/*
 授業情報の更新

*/

require_once('../../config.php');    
require_once($CFG->libdir.'/blocklib.php');
require_once(dirname(__FILE__).'/locallib.php');    


$courseid = required_param('course', PARAM_INTEGER);
$attsid   = required_param('attsid', PARAM_INTEGER);
$classid  = optional_param('class', 0,   PARAM_INTEGER);
$action   = optional_param('action', '', PARAM_ALPHA);
$submit   = optional_param('submit', '', PARAM_TEXT);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
    jbxl_print_error('invalidsesskey');
}

$urlparams['course'] = $courseid;
$urlparams['attsid'] = $attsid;
if ($classid) $urlparams['class']  = $classid;
if ($action)  $urlparams['action'] = $action;
$PAGE->set_url('/blocks/autoattend/updateSession.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/updateSession.php';
$back2URL = $wwwBlock.'/index.php';


if (!empty($submit) && $submit==get_string('return', 'block_autoattend')) {
    redirect($wwwBlock.'/index.php?course='.$courseid.'&amp;class='.$classid);
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

$att = $DB->get_record('autoattend_sessions', array('id'=>$attsid));
if (!$att) {
    jbxl_print_error('nosuchsession', 'block_autoattend');
}


// Print Header
if ($course->category) {
    $title = get_string('updatesessioninfo','block_autoattend').' '.get_string('autoattend','block_autoattend');
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


if ($action=='update') {
    $newday     = required_param('newday',    PARAM_INTEGER);
    $newmonth   = required_param('newmonth',  PARAM_INTEGER);
    $newyear    = required_param('newyear',   PARAM_INTEGER);
    $method     = required_param('newmethod', PARAM_ALPHA);
    $shour      = required_param('newshour',  PARAM_INTEGER);
    $smin       = required_param('newsmin',   PARAM_INTEGER);
    $dhour      = required_param('newdhour',  PARAM_INTEGER);
    $dmin       = required_param('newdmin',   PARAM_INTEGER);
    $lmin       = required_param('newlmin',   PARAM_INTEGER);
    $allowip    = required_param('allowip',   PARAM_TEXT);
    $desc       = required_param('desc',      PARAM_TEXT);

    $attendkey  = optional_param('attendkey', '', PARAM_ALPHA);
    $randomkey  = optional_param('randomkey', '', PARAM_INTEGER);
    $summertime = optional_param('newsummer',  3, PARAM_INTEGER);
    $denysameip = optional_param('denysameip', 0, PARAM_INTEGER);

    if (empty($denysameip)) $denysameip = '0';

    $summertime = $summertime - 3;  // $summer = array(1=>-2,-1,0,1,2); in html/updateSession.html
    $starttime  = $shour*ONE_HOUR_TIME + $smin*ONE_MIN_TIME - $TIME_OFFSET - $summertime*ONE_HOUR_TIME;
    $endtime    = $starttime + ($dhour-1)*ONE_HOUR_TIME + ($dmin-1)*MIN_INTVL_TIME*ONE_MIN_TIME;
    $latetime   = ($lmin - 1)*MIN_INTVL_TIME*ONE_MIN_TIME;
    $newdate    = mktime(0, 0, 0, $newmonth, $newday, $newyear);

    $times = $endtime - $starttime;
    if($times <= 0) {
        jbxl_print_error('wrongtimesselected', 'block_autoattend', $wwwMyURL.'?course='.$course->id.'&amp;class='.$classid.'&amp;attsid='.$attsid);
    }

    $strtm = $newdate + $starttime;
    $endtm = $newdate + $endtime;

    $where = array('courseid'=>$course->id, 'sessdate'=>$newdate, 'starttime'=>$strtm, 'classid'=>$classid);
    $count = $DB->count_records('autoattend_sessions', $where);
    if ($count!=0 and ($newdate!=$att->sessdate or $strtm!=$att->starttime or $classid!=$att->classid)) { //duplicate session exists
        jbxl_print_error('sessionalreadyexists', 'block_autoattend', $wwwMyURL.'?course='.$course->id.'&amp;class='.$classid.'&amp;attsid='.$attsid);    
    }
    else {    
        $ntime = time();
        $state = $att->state;
        if ($att->state!='C') $state = 'N';
//        if ($ntime < $strtm) $state = 'N';
//        else if ($ntime>=$strtm and $ntime<=$endtm) $state = 'O';
//        else $state = 'O';    // ここでは Close 処理しない

//        $prvstate          = $att->state;
//        $prvmethod         = $att->method;
        $att->sessdate     = $newdate;
        $att->classid      = $classid;
        $att->timemodified = time();
        $att->method       = $method;
        $att->state        = $state;
        $att->starttime    = $strtm;
        $att->endtime      = $endtm;
        $att->summertime   = $summertime;
        $att->latetime     = $latetime;
        $att->allowip      = $allowip;
        $att->denysameip   = $denysameip;
        $att->description  = $desc;
        $att->attendkey    = '';

        if ($att->method=='S') {
            if ($randomkey) {
                $att->attendkey = jbxl_randstr(5, true);
            }    
            else {           
                $att->attendkey = $attendkey;
            }
        }

        $result = $DB->update_record('autoattend_sessions', $att);
        if ($result) {
            //$loginfo = 'id='.$attsid.',method='.$method.',state='.$state;
            //$event = autoattend_get_event($context, 'upsesstion', $urlparams);
            //jbxl_add_to_log($event);
        }
        else {
            jbxl_print_error('sessionupdateerror', 'block_autoattend', $wwwMyURL.'?course='.$course->id.'&amp;class='.$classid.'&amp;attsid='.$attsid);    
        }
        //
        autoattend_update_session($course->id, $attsid);
    }    
    //
    redirect($back2URL.'?course='.$course->id.'&amp;class='.$classid.'&amp;attsid='.$attsid, get_string('sessionupdated', 'block_autoattend'), 1);
}

//
$acheck = '';
$scheck = '';
$mcheck = '';
if      ($att->method=='A') $acheck = 'checked="checked"';
else if ($att->method=='S') $scheck = 'checked="checked"';
else if ($att->method=='M') $mcheck = 'checked="checked"';

$late_time  = (int)($att->latetime/ONE_MIN_TIME);
$late_order = (int)($late_time/5) + 1;

//
$currenttab = 'update';
include('tabs.php');
//
$use_summertime = autoattend_use_summertime($course->id);
$summertime = autoattend_get_summertime($att->id, $use_summertime)*ONE_HOUR_TIME;
$classes = autoattend_get_session_classes($course->id);
//
$date_name1 = get_string('date_1st_name', 'block_autoattend');
$date_name2 = get_string('date_2nd_name', 'block_autoattend');
$date_name3 = get_string('date_3rd_name', 'block_autoattend');

include('html/updateSession.html');

echo $OUTPUT->footer($course);

