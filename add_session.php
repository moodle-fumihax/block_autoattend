<?php // $Id: add.php,v 1.7 2006/02/25 18:07:32 dlnsk Exp $

// Modified by Fumi.Iseki to add_session.php  2014/05/11


require_once('../../config.php');    
require_once($CFG->libdir.'/blocklib.php');
require_once(dirname(__FILE__).'/locallib.php');    


$courseid = required_param('course',   PARAM_INTEGER);   // Course id
$classid  = optional_param('class', 0, PARAM_INTEGER);
$mode     = optional_param('mode', '', PARAM_ALPHA);     // one or multi or empty

if (($formdata = data_submitted()) and !confirm_sesskey()) {
    print_error('invalidsesskey');
}

$urlparams['course'] = $courseid;
if ($classid) $urlparams['class'] = $classid;
if ($mode)    $urlparams['mode']  = $mode;
$PAGE->set_url('/blocks/autoattend/add_session.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/add_session.php';
$back2URL = $wwwBlock.'/index.php';


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


// Print Header
$title = $course->shortname.': '.get_string('autoattend','block_autoattend');
if ($course->category) {
    $title.= ' '.get_string('add_session','block_autoattend');
} 

$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_button('&nbsp;');
//$PAGE->set_headingmenu();

echo $OUTPUT->header();


$currenttab = 'add_session';    
include('tabs.php');


// add record
if ($mode==='multi' or $mode==='one') {
    $startday   = required_param('startday',   PARAM_INTEGER);
    $startmonth = required_param('startmonth', PARAM_INTEGER);
    $startyear  = required_param('startyear',  PARAM_INTEGER);        

    $method     = required_param('method',     PARAM_ALPHA);
    $shour      = required_param('shour',      PARAM_INTEGER);
    $smin       = required_param('smin',       PARAM_INTEGER);
    $dhour      = required_param('dhour',      PARAM_INTEGER);
    $dmin       = required_param('dmin',       PARAM_INTEGER);
    $lmin       = required_param('lmin',       PARAM_INTEGER); 

    $attendkey  = required_param('attendkey',  PARAM_ALPHA);
    $allowip    = required_param('allowip',    PARAM_TEXT); 

    $summertime = optional_param('summer',     3,  PARAM_INTEGER);
    $randomkey  = optional_param('randomkey',  0,  PARAM_INTEGER);
    $denysameip = optional_param('denysameip', 0,  PARAM_INTEGER);
    $desc       = optional_param('desc',       '', PARAM_TEXT);

    //
    if ($mode==='multi') {
        $endday   = required_param('endday',     PARAM_INTEGER);
        $endmonth = required_param('endmonth',   PARAM_INTEGER);
        $endyear  = required_param('endyear',    PARAM_INTEGER);    
        $period   = required_param('period',     PARAM_INTEGER);
        $sdays    = optional_param_array('sdays', '',PARAM_ALPHA);
    }

    if (empty($denysameip)) $denysameip = '0';

    $summertime = $summertime - 3;  // $summer = array(1=>-2,-1,0,1,2); in html/add_session.html
    $starttime  = $shour*ONE_HOUR_TIME + $smin*ONE_MIN_TIME - $TIME_OFFSET - $summertime*ONE_HOUR_TIME;
    $endtime    = $starttime + ($dhour-1)*ONE_HOUR_TIME + ($dmin-1)*MIN_INTVL_TIME*ONE_MIN_TIME;
    $startdate  = mktime(0, 0, 0, $startmonth, $startday, $startyear);
    $latetime   = ($lmin - 1)*MIN_INTVL_TIME*ONE_MIN_TIME;

    if ($mode==='multi') {
        $enddate = mktime(0, 0, 0, $endmonth,   $endday,   $endyear);
    }
    else {
        $enddate = $startdate;
    }

    if (empty($sdays)) {
        global $OMITTED_DAYS;
        $sdays = array();
        $dinfo = getdate($startdate);
        $sdays['0'] = $OMITTED_DAYS["{$dinfo['wday']}"];
    }

    //get no of days and times
    $days  = (int) (floor(($enddate - $startdate) / ONE_DAY_TIME)) + 1;    // +1 is to include enddate
    $times = $endtime - $starttime;
        
    if($days <= 0) {
        print_error('wrongdatesselected', 'block_autoattend', $wwwMyURL.'?course='.$courseid.'&amp;class='.$classid);
    }
    else if($times <= 0) {
        print_error('wrongtimesselected', 'block_autoattend', $wwwMyURL.'?course='.$courseid.'&amp;class='.$classid);
    }
    else {
        // Getting first day of week
        $sdate = $startdate;
        $dinfo = getdate($sdate);
        if ($CFG->calendar_startwday === 0) { //week start from sunday
            $startweek = mktime(0, 0, 0, $startmonth, $startday-$dinfo['wday'], $startyear);
        }
        else {
            $wday = $dinfo['wday']===0 ? 7 : $dinfo['wday'];
            $startweek = mktime(0, 0, 0, $startmonth, $startday-$wday+1, $startyear);
        }
        
        // Adding sessions
        $sessnum = 0;
        while ($sdate <= $enddate) {
            if($sdate < $startweek + ONE_WEEK_TIME) {
                //
                if(in_array(date('D',$sdate), $sdays)) {
                    $strtm = $sdate + $starttime;
                    $endtm = $sdate + $endtime;
                    $param = array('courseid'=>$course->id, 'classid'=>$classid, 'sessdate'=>$sdate, 'starttime'=>$strtm);
                    $count = $DB->count_records('autoattend_sessions', $param);
                    //
                    if ($count!=0) {    
                        //check whether this date is in our session days
                        //notify(jbxl_strftime(get_string('strftimedmy', 'block_autoattend'), $sdate).': '.get_string('sessionexist','block_autoattend'));
                        $OUTPUT->notification(jbxl_strftime(get_string('strftimedmy', 'block_autoattend'), $sdate).': '.get_string('sessionexist','block_autoattend'));
                        $sdate += ONE_DAY_TIME;
                        continue;
                    }
                    //
                    $rec              = new stdClass();
                    $rec->courseid    = $course->id;
                    $rec->classid     = $classid;
                    $rec->creator     = $user->id;
                    $rec->sessdate    = $sdate;
                    $rec->method      = $method;
                    $rec->starttime   = $strtm;
                    $rec->endtime     = $endtm;
                    $rec->summertime  = $summertime;
                    $rec->latetime    = $latetime;
                    $rec->allowip     = $allowip;
                    $rec->description = $desc;
                    $rec->denysameip  = $denysameip;
                    $rec->timemodified= time();

                    if ($method=='S') {
                        if (!$attendkey and $randomkey) {
                            $rec->attendkey = jbxl_randstr(5, true);
                        }
                        else {
                            $rec->attendkey = $attendkey;
                        }
                    }
                    else {
                        $rec->attendkey = '';
                    }

                    if ($DB->insert_record('autoattend_sessions', $rec)) {
                        $sessnum++;
                    }
                    else {
                        print_error('erroringeneratingsessions', 'block_autoattend', $wwwMyURL.'?course='.$course->id.'&amp;class='.$classid);
                    }
                    unset($rec);
                }
                $sdate += ONE_DAY_TIME;
            } 
            else {
                $startweek += ONE_WEEK_TIME * $period;
                $sdate = $startweek;
            }
        }
        //
        if ($sessnum>0) {
            notice(get_string('sessionsgenerated', 'block_autoattend'), $back2URL.'?course='.$course->id.'&amp;class='.$classid);
        }
        else {
            print_error('sessionsnogenerated', 'block_autoattend', $wwwMyURL.'?course='.$course->id.'&amp;class='.$classid);
        }
    }
}


if($isteacher) { 
    $classes = autoattend_get_session_classes($course->id);
    $use_summertime = autoattend_use_summertime($course->id);
    //
    $date_name1 = get_string('date_1st_name', 'block_autoattend');
    $date_name2 = get_string('date_2nd_name', 'block_autoattend');
    $date_name3 = get_string('date_3rd_name', 'block_autoattend');
    $nowtime = time();
    include('html/add_session.html');
}

echo $OUTPUT->footer($course);

