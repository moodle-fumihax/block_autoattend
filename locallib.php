<?php // $Id: locallib.php,v 1.7 2006/02/25 18:07:32 dlnsk Exp $

// Modified by Fumi.Iseki    2007/03/19
//                           2012/04/20
//                           2013/04/17
//                           2014/05/11
//                           2014/11/28
//                           2016/01/04
//                           2019/08/20
//                           2023/08/04   add summary['pgrade']
//                           2023/12/28

/*
 function autoattend_to_localcode($message, $tocode) 

 function autoattend_get_current_session($courseid, $classid, $defer=600)
 function autoattend_get_sessions($courseid, $classid, $inall=false)
 function autoattend_count_sessions($courseid, $classid)
 function autoattend_get_attend_students($courseid, $classid=0, $context=null, $sort='', $order='')
 function autoattend_count_attend_students($courseid, $classid=0, $context=null)
 function autoattend_count_class_students($session, $courseid, $context, $select='')
 function autoattend_insert_session_class($courseid, $classname)
 function autoattend_update_session_classes(array $classids, array $classnames, array $classdels)
 function autoattend_get_user_class($userid, $courseid, $idonly=false) 
 function autoattend_get_session_classes($courseid) 
 function autoattend_get_user_classname($classid) 
 function autoattend_get_session_classname($classid) 
 function autoattend_select_session_class($classid, $classes, $url, $url_options)
 function autoattend_select_user_class($classid, $classes, $url, $url_options, $show_ex=false)
 function autoattend_select_attend($attend, $url, $url_options)
 function autoattend_choose_grouping($courseid, $url, $url_options)    // by Emilio Arjona
 function autoattend_get_grade_settings($courseid)
 function autoattend_update_grade_settings($courseid, array settings, $restore=0)
 function autoattend_get_status_num($userid, $courseid, $status)
 function autoattend_get_grade($userid, $courseid)
 function autoattend_get_user_summary($userid, $courseid) 
 function autoattend_get_session_summary($courseid) 
 function autoattend_print_user_row($left, $right) 
 function autoattend_print_user($user, $course, $printing=null) 
 function autoattend_get_nowopen_sessions($courseid, $stdntid, $method, $ntime)
 function autoattend_get_users_bystatus($sessid, $statuss='')
 //function autoattend_get_user_atsession($sessid, $userid)
 function autoattend_get_unclosed_sessions($courseid, $methods='', $ntime='', $incopen=false)
 function autoattend_add_user_insession($sessid, $userid)
 //function autoattend_reset_session_user($courseid, $sessid)
 //function autoattend_update_sessions_user($courseid, $stdntid, $ntime='')
 function autoattend_update_session_users($courseid, $sessid, $ntime='')
 //function autoattend_update_sessions_users($courseid, $ntime='')
 function autoattend_update_session_state($courseid, $sess, $ntime='', $regist=true)
 function autoattend_update_sessions_state($coueseid, $sesss, $ntime='', $regist=true)
 function autoattend_update_session($courseid, $sessid, $ntime='')
 function autoattend_update_sessions($courseid, $ntime='')
 function autoattend_update_auto_session($courseid, $sess, $logs, $ntime='')
 function autoattend_close_session($courseid, $sess, $ntime='')
 function autoattend_return_to_Y($sessid)
 function autoattend_check_valid_logip($userlogs, $ipfmts, $used_ips, $difipf)
 function autoattend_check_invalid_semiautoip($att)
 function autoattend_get_usedips($attsid)
 function autoattend_get_courselogs($courseid, $stime, $etime=0)
 //function autoattend_get_courselogs_pastdays($courseid, $day=0)
 function autoattend_is_localip($ip)
 function autoattend_get_ipresolv_url($ip)

 function autoattend_email_text($info, $mesg)
 function autoattend_email_html($info, $mesg)
 function autoattend_email_teachers_attend($sess, $courseid)
 function autoattend_email_teachers_key($sess, $courseid)
 function autoattend_email_user($sess, $user, $status, $courseid)

 function autoattend_get_course_module($courseid) 
 function autoattend_update_grades($courseid) 

 function autoattend_get_namepattern($courseid)
 function autoattend_get_disp_info($courseid)
 function autoattend_get_predisp_time($courseid)

 function autoattend_disp_feedback($courseid)
 function autoattend_is_email_enable($courseid)
 function autoattend_is_email_allreports($courseid)
 function autoattend_is_email_key($courseid)
 function autoattend_is_email_user($courseid)
 function autoattend_use_summertime($courseid)
 function autoattend_get_summertime($sessid, $use_summertime=true)
 function autoattend_is_old_excel($courseid)
 function autoattend_is_backup_block($courseid)
 function autoattend_disp_localhostname($courseid)

 function autoattend_get_event($context, $action, $params='', $info='')        // for log
*/


defined('ONE_MIN_TIME')  || define('ONE_MIN_TIME',  60);        // Seconds in one minut
defined('ONE_HOUR_TIME') || define('ONE_HOUR_TIME', 3600);      // Seconds in one hour
defined('ONE_DAY_TIME')  || define('ONE_DAY_TIME',  86400);     // Seconds in one day
defined('ONE_WEEK_TIME') || define('ONE_WEEK_TIME', 604800);    // Seconds in one week
defined('MIN_INTVL_TIME')|| define('MIN_INTVL_TIME',5);         // interval of minute

define('CALLED_BY_AUTO',     -1);   // 
define('CALLED_BY_SEMIAUTO', -2);   //

define('NON_CLASSID', -999);
define('VALID_CLASSID', -998);

//
define('AUTO_SUBMIT_LOG',     'SubmitAutoAttend');
define('SEMIAUTO_SUBMIT_LOG', 'SubmitSemiAutoAttend');
define('MANUAL_SUBMIT_LOG',   'SubmitManualAttend');


//
require_once(dirname(__FILE__).'/jbxl/jbxl_tools.php');
require_once(dirname(__FILE__).'/jbxl/jbxl_moodle_tools.php');
require_once(dirname(__FILE__).'/timezonedef.php');



/////////////////////////////////////////////////////////////////////////////////////////////
//
//

function autoattend_to_localcode($message, $tocode) 
{
    return mb_convert_encoding($message, $tocode, 'auto');
}



/////////////////////////////////////////////////////////////////////////////////////////////
//
// Sessions
//

//
// 現在または直近のセッションの情報を得る．
// @params defer 猶予時間
//
function autoattend_get_current_session($courseid, $classid, $defer=600) { 
    global $DB;

    $now_time = time();
    $start_time = $now_time + $defer;

    $select = "courseid='$courseid' and classid='$classid' and starttime<='$start_time' and endtime>'$now_time'";
    $session = $DB->get_record_select('autoattend_sessions', $select);

    if (!$session) return null;
    return $session;
}


//
// コースの講義情報を得る
//
//   $inall : 全学生用授業を含むか？ 
//
function autoattend_get_sessions($courseid, $classid, $inall=false)
{
    global $DB;

    $sort = 'sessdate, starttime, id ASC';
    if ($classid==0) {
        $sessions = $DB->get_records('autoattend_sessions', array('courseid'=>$courseid), $sort);
    }
    else {
        $params = array('courseid'=>$courseid, 'classid'=>$classid);
        if ($inall) {
            $select = 'courseid=:courseid AND (classid=:classid OR classid=0)';
            $sessions = $DB->get_records_select('autoattend_sessions', $select, $params, $sort);
        }
        else {
            $sessions = $DB->get_records('autoattend_sessions', $params, $sort);
        }
    }

    return $sessions;
}


//
//
//
function autoattend_count_sessions($courseid, $classid)
{
    global $DB;

    $select = "courseid=$courseid AND state<>'N' AND (classid=$classid OR classid=0)";
    $sescount = $DB->count_records_select('autoattend_sessions', $select);

    if (!$sescount) $sescount = 0;

      return $sescount;
}



/////////////////////////////////////////////////////////////////////////////////////////////
//
// Students
//

//
// クラスに属する学生を得る．
//     $classid==0 の場合は全クラスに属する学生を帰す．
//
function autoattend_get_attend_students($courseid, $classid=0, $context=null, $sort='', $order='')
{
    $students = array();
    if (!$courseid) return $students;

    if (!$context) $context = jbxl_get_course_context($courseid);

    if ($sort!='' and $order!='') $sort .= ' '.$order;
    $users = jbxl_get_course_students($context, $sort);
    if ($users) {
        foreach ($users as $user) {
            $classinfo = autoattend_get_user_class($user->id, $courseid);
            if ($classinfo->classid>=0 and (($classinfo->classid==$classid or $classid==0) or 
                                            ($classid==NON_CLASSID and $classinfo->classid==0))) {
                $students[$user->id]            = new stdClass();
                $students[$user->id]->id        = $user->id;
                $students[$user->id]->firstname = $user->firstname;
                $students[$user->id]->lastname  = $user->lastname;
                $students[$user->id]->idnumber  = $user->idnumber;
                $students[$user->id]->fullname  = fullname($user);
                $students[$user->id]->classid   = $classinfo->classid;
                $students[$user->id]->classname = $classinfo->name;
                $students[$user->id]->user      = $user;
            }
        }
    }

    return $students;
}


//
// クラスに属する学生の人数を得る．
//     $classid==0 の場合は全クラスに属する学生を帰す．
//
function autoattend_count_attend_students($courseid, $classid=0, $context=null)
{
    if (!$courseid) return 0;
    if (!$context) $context = jbxl_get_course_context($courseid);

    $count = 0;
    $users = jbxl_get_course_students($context, '');
    if ($users) {
        foreach ($users as $user) {
            $classinfo = autoattend_get_user_class($user->id, $courseid, true);
            if ($classinfo->classid>=0 and (($classinfo->classid==$classid or $classid==0) or
                                            ($classid==NON_CLASSID and $classinfo->classid==0))) {
                $count++;
            }
        }
    }

    return $count;
}


//
// 条件を指定して，出席レコードを用いて学生の数を数える．
//
//    ただし，出席レコードの存在する学生のみ (つまり，statusがYの学生はカウントされない可能性がある)
//    出席レコードの存在しない学生も正確にカウントしたい場合は autoattend_count_attend_students() を用いる．
//    また，欠席から除外された学生はカウントされない．
//
//  クラスを指定する場合は $session->classid にクラスIDを設定する．
//
//    出席（含む遅刻，早退）した学生の数: $select = "status<>'Y' AND status<>'X'"
//
function autoattend_count_class_students($session, $courseid, $context, $select='')
{
    global $DB;

    if (empty($select)) $select = 'attsid=?';
    else                $select = 'attsid=? AND '.$select;

    $count = 0;
    $users = $DB->get_records_select('autoattend_students', $select, array($session->id));
    if ($users) {
        foreach ($users as $user) {
            if (jbxl_is_student($user->studentid, $context)) {
                $classinfo = autoattend_get_user_class($user->studentid, $courseid, true);
                if ($classinfo->classid>=0) {
                    if ($classinfo->classid==$session->classid or $session->classid==0) $count++;
                }
            }
        }
    }

    return $count;
}



/////////////////////////////////////////////////////////////////////////////////////////////
//
// Class
//
//

//
// 講義のクラスをDB上に作成
// 
function autoattend_insert_session_class($courseid, $classname)
{
    global $DB, $USER;

    $rec = new stdClass();
    $rec->name = $classname;
    $rec->courseid = $courseid;
    $rec->creator  = $USER->id;
    $rec->timemodified = time();

    return $DB->insert_record('autoattend_classes', $rec);
}


//
// 講義のクラス情報（名前）を更新 または削除
//
function autoattend_update_session_classes(array $classids, array $classnames, array $classdels)
{
    global $DB, $USER;

    foreach ($classids as $key=>$classid) {
        if (isset($classdels[$key])) {
            $DB->delete_records('autoattend_classes', array('id'=>$classid));
        }
        else {
            $rec = $DB->get_record('autoattend_classes', array('id'=>$classid));
            if (strcmp($rec->name, $classnames[$key])) {
                $rec->name = $classnames[$key];
                $rec->creator = $USER->id;
                $rec->timemodified = time();
                $DB->update_record('autoattend_classes', $rec);
            }
        }
    }
}


//
// 学生の所属するクラスの情報を取得
//   idonly: class id のみを取得する．高速化用．
//
function autoattend_get_user_class($userid, $courseid, $idonly=false) 
{
    global $DB;

    $class = $DB->get_record('autoattend_classifies', array('courseid'=>$courseid, 'studentid'=>$userid));
    if (!$class) {
        $class = new stdClass();
        $class->id = 0;
        $class->courseid  = $courseid;
        $class->studentid = $userid;
        $class->classid   = 0;
    }
    if (!$idonly) $class->name = autoattend_get_user_classname($class->classid);

    return $class;
}


//
// 講義の全クラスの情報を取得
//
function autoattend_get_session_classes($courseid) 
{
    global $DB;

    $results = $DB->get_records('autoattend_classes', array('courseid'=>$courseid), 'id');

    $classes = array();
    /*
    $classes[0] = new stdClass();
    $classes[0]->id = 0;
    $classes[0]->courseid = $courseid;
    $classes[0]->creator  = 0;
    $classes[0]->name = get_string('allstudents', 'block_autoattend');
    $classes[0]->timemodified = time();
    */

    if ($results) {
        foreach($results as $result) {
            $classes[$result->id] = $result;
        }
    }

    return $classes;
}


//
// 学生の所属するクラスの名前を取得
//
function autoattend_get_user_classname($classid) 
{
    global $DB;

    if      ($classid==0)  return get_string('nonclass', 'block_autoattend');
    else if ($classid==-1) return get_string('exclusion', 'block_autoattend');

    $class = $DB->get_record('autoattend_classes', array('id'=>$classid));
    if (!$class) return get_string('unknownclass', 'block_autoattend');

    return $class->name;
}


//
// 講義のクラス名を取得
//
function autoattend_get_session_classname($classid) 
{
    global $DB;

    if ($classid==0) return get_string('allstudents', 'block_autoattend');

    $class = $DB->get_record('autoattend_classes', array('id'=>$classid));
    if (!$class) return get_string('unknownclass', 'block_autoattend');

    return $class->name;
}


//
// 講義のクラスの選択ボックスを表示する．
// 
function autoattend_select_session_class($classid, $classes, $url, $url_options)
{
    global $OUTPUT;

    if ($classes) {
        $popupurl = $url.$url_options;
        //
        $options = array();
        $options[0] = get_string('allclasses', 'block_autoattend');
        if ($classes) {
            foreach ($classes as $class) {
                $options[$class->id] = $class->name;
            }
        }
        //
        echo $OUTPUT->single_select($popupurl, 'class', $options, $classid);
    }
}


//
// by Emilio Arjona 2015
//
function autoattend_choose_grouping($courseid, $url, $url_options)
{
    global $OUTPUT;
    $options = array();

    $groupings = groups_get_all_groupings($courseid);
    if ($groupings){
        $popupurl = $url.$url_options;          
        foreach ($groupings as $grouping) {
            $options [$grouping->id] = $grouping->name;
        }
    }
    $options[0] = get_string('allgrouping', 'block_autoattend');

    return $options;
}


//
// ユーザクラスの選択ボックスを表示する．
// 
function autoattend_select_user_class($classid, $classes, $url, $url_options, $show_ex=false)
{
    global $OUTPUT;

    if ($classes or $show_ex) {
        $popupurl = $url.$url_options;
        //
        $options = array();
        $options[0] = get_string('allclasses', 'block_autoattend');
        if ($show_ex) $options[VALID_CLASSID] = get_string('validclasses', 'block_autoattend');
        if ($classes) {
            $options[NON_CLASSID] = get_string('nonclass', 'block_autoattend');
            foreach ($classes as $class) {
                $options[$class->id] = $class->name;
            }
        }
        if ($show_ex) $options[-1] = get_string('excludedstudents', 'block_autoattend');
        //
        echo $OUTPUT->single_select($popupurl, 'class', $options, $classid);
    }
}


//
function autoattend_select_attend($attend, array $settings, $url, $url_options)
{
    global $OUTPUT;

    $popupurl = $url.$url_options;
    //
    $options = array();
    $options['A'] = get_string('Adesc', 'block_autoattend');
    foreach($settings as $set) $options[$set->status] = $set->description;
    $options['Z'] = $options['X'].' or '.$options['Y'];
    //
    echo $OUTPUT->single_select($popupurl, 'attend', $options, $attend);
}



/////////////////////////////////////////////////////////////////////////////////////////////
//
// Grade 
//

//
// 評定の点数設定を得る
//
function autoattend_get_grade_settings($courseid)
{
    global $DB;

    $result= $DB->get_records('autoattend_settings', array('courseid'=>$courseid), 'seqnum'); 
    if (!$result) {
        $result = $DB->get_records('autoattend_settings', array('courseid'=>0), 'seqnum');    // use default
    }
    $settings = array();

    foreach ($result as $res) {
        if (empty($res->acronym))     $res->acronym = get_string($res->status.'acronym', 'block_autoattend');
        if (empty($res->title))       $res->title = get_string($res->status.'title', 'block_autoattend');
        if (empty($res->description)) $res->description = get_string($res->status.'desc', 'block_autoattend');
        $settings[$res->status] = $res;
    }
    return $settings;
}    


//
// 評定の点数設定を更新する
//
function autoattend_update_grade_settings($courseid, array $settings, $restore=0)
{
    global $DB;

    if (empty($courseid)) $courseid = 0;
   
    //restore defaults  
    if ($restore) {
        if ($courseid!=0) {
            $DB->delete_records('autoattend_settings', array('courseid'=>$courseid));
        }
        return;
    }
    if (!$settings) return;

    //
    $status = array('P','X','L','E','G','S','Y');

    for ($i=0; $i<count($status); $i++) {
        if ($rec = $DB->get_record('autoattend_settings', array('courseid'=>$courseid, 'status'=>$status[$i]))) {
            $update = true;
        }
        else {
            $rec = new stdClass();
            $rec->classid = 0;
            $update = false;
        }
        $rec->courseid    = $courseid;
        $rec->status      = $status[$i];
        $rec->grade       = $settings[$status[$i]]->grade;
        $rec->acronym     = $settings[$status[$i]]->acronym;
        $rec->title       = $settings[$status[$i]]->title;
        $rec->description = $settings[$status[$i]]->description;
        $rec->display     = $settings[$status[$i]]->display;
        $rec->seqnum      = $settings[$status[$i]]->seqnum;

        if ($update) {
            $result = $DB->update_record('autoattend_settings', $rec);
            if (!$result) break;
        }
        else {
            $result = $DB->insert_record('autoattend_settings', $rec);
            if (!$result) break;
        }
        unset($rec);
    }
}


//
// 出席や欠席の回数を返す
//         $status に 'P', 'L', 'E', 'X', 'G', 'S', 'Y' を指定する
//
function autoattend_get_status_num($userid, $courseid, $status)
{
    global $CFG, $DB;

    $classinfo = autoattend_get_user_class($userid, $courseid);

    $qry = "SELECT COUNT(*) AS cnt FROM {$CFG->prefix}autoattend_students std ,{$CFG->prefix}autoattend_sessions ses". 
                " WHERE std.attsid = ses.id AND ses.courseid = ".$courseid ." AND std.studentid = ".$userid.
                " AND std.status = '".$status."' AND (ses.classid = ".$classinfo->classid." OR ses.classid = 0)";

    //print "QUERY=> $qry <br />";
    $data = $DB->get_record_sql($qry);
    
    return $data->cnt;
}


//
// 出席点を返す
//
function autoattend_get_grade($userid, $courseid)
{
    $settings = autoattend_get_grade_settings($courseid);

      $grade = 0;
      foreach ($settings as $setting) {
          $count = autoattend_get_status_num($userid, $courseid, $setting->status);
          $grade = $grade + $count * $setting->grade;
      }
      
    return $grade;
}



/////////////////////////////////////////////////////////////////////////////////////////////
//
// Summary
//

//
// 学生のサマリーを返す
//
// $summary['userid']   : ユーザID
// $summary['courseid'] : コースID
// $summary['attitems'] : 学生の各授業のRawデータ（配列）
// $summary['complete'] : 出席コマ数（早退，遅刻を含む）
// $summary['settings'] : 出席点の配分Rawデータ（配列）
// $summary['grade']    : 出席点
// $summary['npercent'] : 出席率（出席数ベース）
// $summary['gpercent'] : 得点率（出席点ベース）
// $summary['P']        : 正常出席数 
// $summary['X']        : 欠席数．クローズしたセッションで Y の物を含む． 
// $summary['L']        : 遅刻数 
// $summary['E']        : 早退数 
// $summary['G']        : 汎用数 
// $summary['S']        : 特別数 
// $summary['Y']        : 未了数．ただしクローズしたセッションは X とする． 
// $summary['classid']  : クラスID
// $summary['classname']: クラス名
// $summary['pgrade']   : 出席点（皆勤の場合の出席点）
// $summary['maxgrade'] : 最高出席点（皆勤の場合の出席点 + 特別点）
// $summary['mingrade'] : 最低出席点（全欠の場合の出席点）
// $summary['leccount'] : 実施した授業のコマ数
//
function autoattend_get_user_summary($userid, $courseid) 
{
    global $CFG, $DB;

    require_once('jbxl/jbxl_moodle_tools.php');
 
    $ntime = time();
    $class = autoattend_get_user_class($userid, $courseid);

    $stqry = "SELECT * FROM {$CFG->prefix}autoattend_students std".
                  " RIGHT JOIN (SELECT * FROM {$CFG->prefix}autoattend_sessions WHERE courseid=$courseid) ses".
                  " ON ses.id=std.attsid AND std.studentid=$userid AND (ses.classid=".$class->classid." OR ses.classid=0)".
                  " ORDER BY ses.sessdate, ses.starttime ASC";

    $attitems = $DB->get_records_sql($stqry);
    if (!$attitems) return false;

    $summary = array();
    $summary['userid']   = $userid;
    $summary['courseid'] = $courseid;
    $summary['attitems'] = $attitems;

    $complete = 0;
    if ($attitems) {
        foreach($attitems as $att) {
            if (!empty($att->status) && $att->status!='Y') $complete++;
        }
    }
    $summary['complete'] = $complete;                                    // 出席コマ数（早退，遅刻を含む）
    $summary['settings'] = autoattend_get_grade_settings($courseid);

    $pgrade = $summary['settings']['P']->grade;
    $maxgrade = 0;
    $mingrade = 0;
    foreach($summary['settings'] as $set) {
        $summary[$set->status] = 0;
        foreach($summary['attitems'] as $att) {
            if ($set->status==$att->status) {
                // 出席の状態をカウント
                if ($att->status=='Y') {
                    if ($ntime>$att->endtime) $summary['X']++;
                    //else                    $summary['Y']++;
                }
                else {
                    $summary[$set->status]++;
                }
            }
        }
        if ($set->grade>$maxgrade) $maxgrade = $set->grade;
        if ($set->grade<$mingrade) $mingrade = $set->grade;
    }
    $sessnum = autoattend_count_sessions($courseid, $class->classid);
    $summary['Y'] = $sessnum - $summary['P'] - $summary['L'] - $summary['E'] - $summary['X'] - $summary['G'] - $summary['S'];

    //
    $summary['grade']    = autoattend_get_grade($userid, $courseid);
    $summary['pgrade']   = $pgrade   * $sessnum;
    $summary['maxgrade'] = $maxgrade * $sessnum;
    $summary['mingrade'] = $mingrade * $sessnum;
    $summary['leccount'] = $sessnum;
    //
    //$gradelevel = $summary['maxgrade'] - $summary['mingrade'];
    $gradelevel = $summary['pgrade'];
    if ($gradelevel!=0) {
        $npercent = 100*($sessnum - $summary['X'] - $summary['Y'] - $summary['L']/2.0 - $summary['E']/2.0)/$sessnum;
        $gpercent = 100*($summary['grade']-$summary['mingrade'])/$gradelevel;
        $summary['npercent'] = sprintf('%0.1f', $npercent);
        $summary['gpercent'] = sprintf('%0.1f', $gpercent);
    }
    else {
        $summary['npercent'] = ' - ';
        $summary['gpercent'] = ' - ';
    }

    $summary['classid']   = $class->classid;
    $summary['classname'] = $class->name;

    return $summary;
}


//
// 授業の全クラスのサマリーを返す
//
// $summary['courseid'] : コースID
// $summary['attitems'] : 各授業のRawデータ（配列）
// $summary['classes']  : クラスデータ（配列）
// $summary['settings'] : 出席点の配分Rawデータ（配列）
// $summary['pgrade']   : 実施された授業での出席点（皆勤の場合の出席点）
// $summary['maxgrade'] : 実施された授業での最高出席点（皆勤の場合の出席点 + 特別点）
// $summary['mingrade'] : 実施された授業での最低出席点（全欠場合の出席点）
// $summary['leccount'] : 実施された授業のコマ数
//
function autoattend_get_session_summary($courseid) 
{
    global $DB;

    require_once('jbxl/jbxl_moodle_tools.php');

    $attitems = $DB->get_records('autoattend_sessions', array('courseid'=>$courseid), 'starttime');
    if (!$attitems) return false;

    $summary = array();
    $summary['courseid'] = $courseid;
    $summary['attitems'] = $attitems;
    $summary['settings'] = autoattend_get_grade_settings($courseid);
    $summary['classes']  = autoattend_get_session_classes($courseid);

    //
    $attpoint = 0;
    $abspoint = 0;
    foreach($summary['settings'] as $set) {
        if ($set->grade>$attpoint) $attpoint = $set->grade;
        if ($set->grade<$abspoint) $abspoint = $set->grade;
    }

    $leccount = 0;
    if (!$summary['classes']) {
        $summary['classes'][0] = new stdClass();
        $summary['classes'][0]->id = 0;
        $summary['classes'][0]->courseid = $courseid;
        $summary['classes'][0]->creator  = 0;
        $summary['classes'][0]->name = get_string('allstudents', 'block_autoattend');
        $summary['classes'][0]->timemodified = time();
    }

    // クラスによってコマ数が違う場合
    foreach ($summary['classes'] as $class) {
        $count = 0;
        foreach ($attitems as $att) {
            if ($att->state<>'N' and ($att->classid==$class->id or $att->classid==0)) $count++;
        }
        if ($count>$leccount) $leccount = $count;
    }

    $summary['pgrade']   = $summary['settings']['P']->grade * $leccount;
    $summary['maxgrade'] = $attpoint * $leccount;
    $summary['mingrade'] = $abspoint * $leccount;
    $summary['leccount'] = $leccount;

    return $summary;
}



/////////////////////////////////////////////////////////////////////////////////////////////
//
// View and Print
//

function autoattend_print_user_row($left, $right) 
{
    echo "\n<tr><td nowrap=\"nowrap\" align=\"right\" valign=\"top\" class=\"label c0\">$left</td>
                <td align=\"left\" valign=\"top\" class=\"info c1\">$right</td></tr>\n";
}


//
// １ユーザの出欠レポートを表示する
//
function autoattend_print_user($user, $course, $printing=null) 
{
    global $DB, $CFG, $USER, $OUTPUT, $TIME_OFFSET;

    $wwwBlock  = $CFG->wwwroot.'/blocks/autoattend';
    $wwwGrade  = $CFG->wwwroot.'/grade/report/user';
    $wwwReport = $CFG->wwwroot.'/report/log';
    $wwwMesg   = $CFG->wwwroot.'/message';
    $wwwUser   = $CFG->wwwroot.'/user';

    if (!is_object($course)) {
        $course = $DB->get_record('course', array('id'=>$course));
    }
    $courseid  = $course->id;
    $userid    = $user->id;
    $context   = jbxl_get_course_context($courseid);
    $isteacher = jbxl_is_teacher($USER->id, $context);
    $summary   = autoattend_get_user_summary($user->id, $courseid);
    $use_summertime = autoattend_use_summertime($courseid);

    if(!$summary) {
        notice(get_string('attendnotstarted','block_autoattend'), $CFG->wwwroot.'/course/view.php?id='.$courseid);
    } 
    else {
        $complete  = $summary['complete'];
        $npercent  = $summary['npercent'].' %';
        $gpercent  = $summary['gpercent'].' %';
        $grade     = $summary['grade'];
        $pgrade    = $summary['pgrade'];
        $maxgrade  = $summary['maxgrade'];
        $settings  = $summary['settings'];
        $classid   = $summary['classid']; 
        $classname = $summary['classname'];

        //
        if ($CFG->output_idnumber) {
            if (empty($user->idnumber)) $user_idnum = ' - ';
            else                        $user_idnum = $user->idnumber;
            $disp_idnum = '['.$user_idnum.']';
        }
        else {
            $user_idnum = '';
            $disp_idnum = '';
        }
        //
        $name_pattern = autoattend_get_namepattern($courseid);
        $username = jbxl_get_user_name($user->id, $name_pattern);
        include('html/print_user_header.html');
        //
        if ($classid>=0) {        // !出欠から除外
            //
            $table = new html_table();

            // Header
            $table->head [] = '#';
            $table->align[] = 'right';
            $table->size [] = '20px';
            $table->wrap [] = 'nowrap';

            $table->head [] = get_string('date');
            $table->align[] = 'center';
            $table->size [] = '40px';
            $table->wrap [] = 'nowrap';

            $table->head [] = get_string('starttime', 'block_autoattend');
            $table->align[] = 'center';
            $table->size [] = '60px';
            $table->wrap [] = 'nowrap';

            $table->head [] = get_string('endtime', 'block_autoattend');
            $table->align[] = 'center';
            $table->size [] = '60px';
            $table->wrap [] = 'nowrap';

            $table->head [] = get_string('classname', 'block_autoattend');
            $table->align[] = 'center';
            $table->size [] = '40px';
            $table->wrap [] = 'nowrap';

            $table->head [] = get_string('description','block_autoattend');
            $table->align[] = 'left';
            $table->size [] = '40px';
            $table->wrap [] = 'nowrap';

            $table->head [] = get_string('status', 'block_autoattend');
            $table->align[] = 'center';
            $table->size [] = '40px';
            $table->wrap [] = 'nowrap';

            $table->head [] = get_string('callmethod', 'block_autoattend');
            $table->align[] = 'center';
            $table->size [] = '60px';
            $table->wrap [] = 'nowrap';

            $table->head [] = get_string('calledtime', 'block_autoattend');
            $table->align[] = 'center';
            $table->size [] = '60px';
            $table->wrap [] = 'nowrap';

            //$table->head [] = get_string('ip', 'block_autoattend');
            $table->head [] = get_string('client', 'block_autoattend');
            $table->align[] = 'center';
            $table->size [] = '80px';
            $table->wrap [] = 'nowrap';

            $table->head [] = get_string('remarks', 'block_autoattend');
            $table->align[] = 'left';
            $table->size [] = '120px';
            $table->wrap [] = 'nowrap';

            $i = 0;
            foreach($summary['attitems'] as $att) { 
                //
                $summertime_mark = '&nbsp;';
                $summertime = autoattend_get_summertime($att->id, $use_summertime)*ONE_HOUR_TIME;
                if ($summertime!=0) $summertime_mark = '*';

                if ($att->classid==$classid or $att->classid==0) {
                    $num = $i + 1;
                    $table->data[$i][] = $num.$summertime_mark;
                    //$table->data[$i][] = strftime(get_string('strftimedmyw',   'block_autoattend'), $att->sessdate  + $summertime + $TIME_OFFSET);
                    $table->data[$i][] = strftime(get_string('strftimedmyw',   'block_autoattend'), $att->starttime + $summertime + $TIME_OFFSET);
                    $table->data[$i][] = strftime(get_string('strftimehourmin','block_autoattend'), $att->starttime + $summertime + $TIME_OFFSET);
                    $table->data[$i][] = strftime(get_string('strftimehourmin','block_autoattend'), $att->endtime   + $summertime + $TIME_OFFSET);
                    $table->data[$i][] = autoattend_get_user_classname($att->classid);
                    $table->data[$i][] = $att->description ? $att->description: get_string('nodescription', 'block_autoattend');

                    if ($att->studentid) {
                        if ($att->status=='Y') {
                            if (time()>$att->endtime) {
                                $table->data[$i][] = $settings['X']->acronym;
                            }
                            else {
                                $table->data[$i][] = get_string('novalue', 'block_autoattend');
                            }
                        }
                        else {
                            $table->data[$i][] = $settings[$att->status]->acronym;
                        }
                        $table->data[$i][] = get_string($att->called.'methodfull', 'block_autoattend');
                    }
                    else {
                        $table->data[$i][] = get_string('novalue', 'block_autoattend');
                        $table->data[$i][] = get_string('novalue', 'block_autoattend');
                    }

                    //
                    if (!$att->studentid OR $att->status==='X' OR $att->status==='Y') {
                        $table->data[$i][] = get_string('novalue', 'block_autoattend');;
                    }
                    else {
                        //$sessndate  = strftime(get_string('strftimedmshort', 'block_autoattend'), $att->sessdate   + $summertime + $TIME_OFFSET);
                        $sessndate  = strftime(get_string('strftimedmshort', 'block_autoattend'), $att->calledtime + $summertime + $TIME_OFFSET);
                        $calleddate = strftime(get_string('strftimedmshort', 'block_autoattend'), $att->calledtime + $summertime + $TIME_OFFSET);
                        $calledtime = strftime(get_string('strftimehmshort', 'block_autoattend'), $att->calledtime + $summertime + $TIME_OFFSET);
                        if ($sessndate===$calleddate) {
                            $table->data[$i][] = $calledtime;
                        }
                        else {
                            $table->data[$i][] = $calledtime.'&nbsp;('.$calleddate.')';
                        }
                    }

                    //
                    $ipaddr = $att->ipaddress ? $att->ipaddress : get_string('novalue', 'block_autoattend');
                    if ($ipaddr) {
                        //$ipurl  = jbxl_get_ipresolv_url($ipaddr);
                        $ipurl  = autoattend_get_ipresolv_url($ipaddr);
                        //
                        if ($ipurl) $table->data[$i][] = "<a href=$ipurl target=_blank>$ipaddr</a>";
                        else {
                            if ($att->ipaddress && autoattend_disp_localhostname($course->id)) {
                                $table->data[$i][] = gethostbyaddr($ipaddr);
                            }
                            else {
                                $table->data[$i][] = $ipaddr;
                            }
                        }
                    }
                    else {
                         $table->data[$i][] = get_string('novalue', 'block_autoattend');
                    }

                    $table->data[$i][] = $att->remarks;
                    $i++;
                }
            }
            echo '<div align="left">';
            echo html_writer::table($table);
            echo '</div>';
        }

        //
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';
    }

    return;
}


//
// ダウンロード用のデータを作る
//
// $courseid: ダウンロードするコースのID
// $classes:  通常はクラス名（ID）の配列．中身があれば（内容は問わない），クラス名も表示する．
// $classid:  ダウンロードするクラスのID．0なら全クラス．
// $viewmode: ダウンロードする期間．'all', 'weeks', 'months'
// $starttm:  viewmode で，'weeks', 'months' を指定した場合の始まりの時刻指定．0なら現在．
// $attsid:   ダウンロードするセッションのID．0 または 'all' なら全てのセッション．
// $students: 外部で表示する学生を定義する場合に指定する．データがある場合，つづく $sort,$order指定は無効になる．
// $sort:     'lastname', 'firstname', 'idnumber'
// $order:    'ASC', 'DESC'

function autoattend_make_download_data($courseid, $classes=null, $classid=0, $viewmode='all', $starttm=0, $attsid='all', $students=null, $sort='', $order='')
{
    global $CFG, $DB, $TIME_OFFSET;

    $settings = autoattend_get_grade_settings($courseid);
    $use_summertime = autoattend_use_summertime($courseid);

    //
    $indclass  = '';
    $indsess   = '';
    $indperiod = '';
    if ($classid!=0) $indclass = ' AND (classid='.$classid.' OR classid=0)';
    if ($viewmode!='all') {
        if ($starttm==0) $starttm = time();
        if ($viewmode==='weeks') {
            $indperiod = " AND sessdate >= $starttm AND sessdate < ".($starttm + ONE_WEEK_TIME);
        }
        elseif ($viewmode==='months') {
            $nxtmon = mktime(0, 0, 0, date('m', $starttm+$TIME_OFFSET)+1, 1, date('Y', $starttm+$TIME_OFFSET)) - $TIME_OFFSET;
            $indperiod = " AND sessdate >= $starttm AND sessdate < ".$nxtmon;
        }
    }
    if ($attsid!='all' and $attsid!=0) {
        $indsess  = ' AND id='.$attsid;
        $viewmode = 'session';
    }

    // Only Closed Session
    $qry = "SELECT * FROM {$CFG->prefix}autoattend_sessions where courseid=".$courseid.$indsess.$indclass.$indperiod.
                            " AND state='C' ORDER BY sessdate, starttime ASC";    

    $name_pattern = autoattend_get_namepattern($courseid);
    //
    $datas = new stdClass();
    $datas->attr = array();    // 属性 'string', 'number'. デフォルトは 'string' 
    $datas->data = array();

    $j = 0;
    $k = 0;
    $datas->attr[0] = array();
    $datas->data[0] = array();

    if ($CFG->fullnamedisplay=='lastname firstname') {
        if ($name_pattern=='fullname' or $name_pattern=='lastname') {
            $datas->attr[0][$k++] = '';
            $datas->data[0][$j++] = get_string('lastname');
        }
        if ($name_pattern=='fullname' or $name_pattern=='firstname') {
            $datas->attr[0][$k++] = '';
            $datas->data[0][$j++] = get_string('firstname');
        }
        if ($sort=='') $sort = 'lastname';
    }
    else {
        if ($name_pattern=='fullname' or $name_pattern=='firstname') {
            $datas->attr[0][$k++] = '';
            $datas->data[0][$j++] = get_string('firstname');
        }
        if ($name_pattern=='fullname' or $name_pattern=='lastname') {
            $datas->attr[0][$k++] = '';
            $datas->data[0][$j++] = get_string('lastname');
        }
        if ($sort=='') $sort = 'firstname';
    }
    //
    if ($CFG->output_idnumber) {
        $datas->attr[0][$k++] = '';
        $datas->data[0][$j++] = 'ID';
    }
    if ($classes) {
        $datas->attr[0][$k++] = '';
        $datas->data[0][$j++] = get_string('classname', 'block_autoattend');
    }

    if ($viewmode=='session') {
        $courseses = array();
        if ($sess = $DB->get_records_sql($qry)) {
            foreach($sess as $id=>$dsess) {
                $summertime = autoattend_get_summertime($dsess->id, $use_summertime)*ONE_HOUR_TIME;
                //$date = strftime(get_string('strftimedmshort','block_autoattend'), $dsess->sessdate + $summertime + $TIME_OFFSET);
                $date = strftime(get_string('strftimedmshort','block_autoattend'), $dsess->starttime + $summertime + $TIME_OFFSET);
                $datas->attr[0][$k++] = '';
                $datas->data[0][$j++] = $date;
                $courseses[] = $dsess->id;
            }
        }
    }

    //
    $datas->attr[0][$k++] = '';
    $datas->data[0][$j++] = get_string('Cstatefull',   'block_autoattend');
    //
    foreach($settings as $set) {
        if ($set->status!='Y' and $set->display) {
            $datas->attr[0][$k++] = '';
            $datas->data[0][$j++] = $set->title;
        }
    }
    //
    $datas->attr[0][$k++] = '';
    $datas->attr[0][$k++] = '';
    $datas->attr[0][$k++] = '';
    $datas->data[0][$j++] = get_string('attendnpercent','block_autoattend');
    $datas->data[0][$j++] = get_string('attendgrade',   'block_autoattend');
    $datas->data[0][$j++] = get_string('attendgpercent','block_autoattend');

    if ($viewmode!='session') {
        $courseses = array();
        if ($sess = $DB->get_records_sql($qry)) {
            foreach($sess as $id=>$dsess) {
                $summertime = autoattend_get_summertime($dsess->id, $use_summertime)*ONE_HOUR_TIME;
                //$date = strftime(get_string('strftimedmshort','block_autoattend'), $dsess->sessdate + $summertime + $TIME_OFFSET);
                $date = strftime(get_string('strftimedmshort','block_autoattend'), $dsess->starttime + $summertime + $TIME_OFFSET);
                $datas->attr[0][$k++] = '';
                $datas->data[0][$j++] = $date;
                $courseses[] = $dsess->id;
            }
        }
    }
        
    //
    if (!$students) {
        $context  = jbxl_get_course_context($courseid);
        $students = autoattend_get_attend_students($courseid, $classid, $context, $sort, $order);
    }

    $i = 1;
    foreach ($students as $student) {
        $j = 0;
        $k = 0;
        $summary = autoattend_get_user_summary($student->id, $courseid);

        $datas->data[$i] = array();
        $datas->attr[$i] = array();
        //
        if ($CFG->fullnamedisplay == 'lastname firstname') {
            if ($name_pattern=='fullname' or $name_pattern=='lastname') {
                $datas->attr[$i][$k++] = '';
                $datas->data[$i][$j++] = $student->lastname;
            }
            if ($name_pattern=='fullname' or $name_pattern=='firstname') {
                $datas->attr[$i][$k++] = '';
                $datas->data[$i][$j++] = $student->firstname;
            }
        }
        else {
            if ($name_pattern=='fullname' or $name_pattern=='firstname') {
                $datas->attr[$i][$k++] = '';
                $datas->data[$i][$j++] = $student->firstname;
            }
            if ($name_pattern=='fullname' or $name_pattern=='lastname') {
                $datas->attr[$i][$k++] = '';
                $datas->data[$i][$j++] = $student->lastname;
            }
        }
        if ($CFG->output_idnumber) {
            if (empty($student->idnumber)) $idnumber = '-';
            else                           $idnumber = $student->idnumber;
            $datas->attr[$i][$k++] = '';
            $datas->data[$i][$j++] = $idnumber;
        }
        if ($classes) {
            $datas->attr[$i][$k++] = '';
            $datas->data[$i][$j++] = isset($summary['classname']) ? $summary['classname'] : '';
        }

        if ($viewmode=='session') {
            foreach ($courseses as $sid) {
                if ($rec = $DB->get_record('autoattend_students', array('attsid'=>$sid, 'studentid'=>$student->id))) {
                    $datas->attr[$i][$k++] = '';
                    $datas->data[$i][$j++] = $settings[$rec->status]->acronym; 
                } 
                else {
                    $datas->attr[$i][$k++] = '';
                    $datas->data[$i][$j++] = '-';
                }
            }
        }

        //
        $datas->attr[$i][$k++] = 'number';
        $datas->data[$i][$j++] = isset($summary['complete']) ? $summary['complete'] : '';
        //
        foreach($settings as $set) {
            if ($set->status!='Y' and $set->display) {
                $datas->attr[$i][$k++] = 'number';
                $datas->data[$i][$j++] = isset($summary[$set->status]) ? $summary[$set->status] : '';
            }
        }

        $datas->attr[$i][$k++] = 'number';
        $datas->attr[$i][$k++] = 'number';
        $datas->attr[$i][$k++] = 'number';
        $datas->data[$i][$j++] = isset($summary['npercent']) ? $summary['npercent'] : '';
        $datas->data[$i][$j++] = isset($summary['grade'])    ? $summary['grade']   : '';
        $datas->data[$i][$j++] = isset($summary['gpercent']) ? $summary['gpercent'] : '';

        if ($viewmode!='session') {
            foreach ($courseses as $sid) {
                if ($rec = $DB->get_record('autoattend_students', array('attsid'=>$sid, 'studentid'=>$student->id))) {
                    $datas->attr[$i][$k++] = '';
                    $datas->data[$i][$j++] = $settings[$rec->status]->acronym; 
                } 
                else {
                    $datas->attr[$i][$k++] = '';
                    $datas->data[$i][$j++] = '-';
                }
            }
        }

        $i++;
    }

    return $datas;
}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// for Automatic Attendance
//

//
// 指定した条件で，現在出席をとっている授業の情報を返す．
//
//        ex.) $sessions = autoattend_get_nowopen_sessions($courseid, $stdntid, 'S', $ntime);
//
function autoattend_get_nowopen_sessions($courseid, $stdntid, $method, $ntime)
{
    $sesss = autoattend_get_unclosed_sessions($courseid, $method, $ntime, true);
    $sessions = array();

    foreach($sesss as $sess) {
        if ($ntime>=$sess->starttime and $ntime<=$sess->endtime and $method==$sess->method) {
            $sessions[] = $sess;
        }
    }
    return $sessions;
}


//
// 指定した授業で，指定した評価を得たユーザ（複数）の情報を返す．
// $statuss は評価の配列（例：array('Y','P','X')）または評価の文字（例：'Y'）
// または指定しない 指定しない場合は全ての評価を指定したことになる．
//
function autoattend_get_users_bystatus($sessid, $statuss='')
{
    global $CFG, $DB;

    //
    $status = '';
    if (is_array($statuss)) {
        $substs = implode("' OR status='", $statuss);
        if (!empty($substs))  $status = " AND ( status='".$substs."' )";
    }
    else {
        if (!empty($statuss)) $status = " AND status='".$statuss."' ";
    }

    $qry = "SELECT * FROM {$CFG->prefix}autoattend_students WHERE attsid=".$sessid." ".$status." ORDER BY studentid ASC";
    //print "QUERY = $qry<br/>";

    $return = array();
    $users  = $DB->get_records_sql($qry);
    if ($users) {
        foreach($users as $user) {
            $return[$user->studentid]             = new stdClass();
            $return[$user->studentid]->id         = $user->id;
            $return[$user->studentid]->attsid     = $user->attsid;
            $return[$user->studentid]->studentid  = $user->studentid;
            $return[$user->studentid]->status     = $user->status;
            $return[$user->studentid]->called     = $user->called;
            $return[$user->studentid]->calledby   = $user->calledby;
            $return[$user->studentid]->calledtime = $user->calledtime;
            $return[$user->studentid]->remarks    = $user->remarks;
            $return[$user->studentid]->ipaddress  = $user->ipaddress;
        }
    }

    return $return;
}


function autoattend_get_summertime($sessid, $use_summertime=true)
{
    global $DB;
  
    if (!$use_summertime) return 0;

    $summertime = $DB->get_field('autoattend_sessions', 'summertime', array('id'=>$sessid));
    return $summertime;
}


//
// まだクローズ状態にない授業の情報を返す．．
//
// 指定した授業での，指定したユーザの情報を返す．
//
/*
function autoattend_get_user_atsession($sessid, $userid)
{
    global $CFG, $DB;

    $qry = "SELECT *  FROM {$CFG->prefix}autoattend_students WHERE attsid=".$sessid." AND studentid=".$userid;
    //print "QUERY = $qry<br/>";

    $return = null;
    $users  = $DB->get_records_sql($qry);
    if ($users) {
        foreach ($users as $user) {
            $return = new stdClass();
            $return->id         = $user->id;
            $return->attsid     = $user->attsid;
            $return->studentid  = $user->studentid;
            $return->status     = $user->status;
            $return->called     = $user->called;
            $return->calledby   = $user->calledby;
            $return->calledtime = $user->calledtime;
            $return->remarks    = $user->remarks;
            $return->ipaddress  = $user->ipaddress;
            break;
        }
    }

    return $return;
}
*/


//
// まだクローズ状態にない授業の情報を返す．．
// $methods はデフォルトの点呼方法の配列（例：array('A','S'）または点呼方法の文字（例：'S'）
// または指定なし．指定しない場合は全ての方法を指定したことになる．
// また，現在授業中ものも含めて返す場合は $incopen を true にする．
//
function autoattend_get_unclosed_sessions($courseid, $methods='', $ntime='', $incopen=false)
{
    global $CFG, $DB;
    
     $method = '';
    if (is_array($methods)) {
        $submth = implode("' OR method='", $methods);
        if (!empty($submth))  $method = " AND ( method='".$submth."' )";
    }
    else {
        if (!empty($methods)) $method = " AND method='".$methods."' ";
    }
    if (empty($ntime)) $ntime = time();

    if ($incopen) $ctime = 'starttime';
    else          $ctime = 'endtime';

    $qry = "SELECT * FROM {$CFG->prefix}autoattend_sessions ".
                " WHERE courseid=".$courseid." AND state<>'C'".$method." AND (".$ctime.")<='".$ntime."' ".
                " ORDER BY sessdate, starttime ASC";
    //print "QUERY = $qry<br/>";

    $return = array();
    $sesss  = $DB->get_records_sql($qry);
    if ($sesss) {
        foreach($sesss as $sess) {
            $return[$sess->id]              = new stdClass();
            $return[$sess->id]->id          = $sess->id;
            $return[$sess->id]->courseid    = $sess->courseid;
            $return[$sess->id]->classid     = $sess->classid;
            $return[$sess->id]->sessdate    = $sess->sessdate;
            $return[$sess->id]->starttime   = $sess->starttime;
            $return[$sess->id]->endtime     = $sess->endtime;
            $return[$sess->id]->latetime    = $sess->latetime;
            $return[$sess->id]->method      = $sess->method;
            $return[$sess->id]->attendkey   = $sess->attendkey;
            $return[$sess->id]->denysameip  = $sess->denysameip;
            $return[$sess->id]->allowip     = $sess->allowip;
            $return[$sess->id]->description = $sess->description;
            $return[$sess->id]->state       = $sess->state;
        }
    }

    return $return;
}


//
// 学生用の授業用データレコードの追加
//
function autoattend_add_user_insession($sessid, $userid)
{
    global $DB;

    $rec             = new stdClass();
    $rec->attsid     = $sessid;
    $rec->studentid  = $userid;
    $rec->status     = 'Y';
    $rec->called     = 'D';
    $rec->calledby   = 0;
    $rec->calledtime = 0;
    $rec->sentemail  = 0;
    $rec->remarks    = '';
    $rec->ipaddress  = '';

    $student = $DB->get_record('autoattend_students', array('attsid'=>$sessid, 'studentid'=>$userid));
    if ($student) return $student;
    //
    $rec->id = $DB->insert_record('autoattend_students', $rec);
    if ($rec->id) return $rec;
    else return null;
}
 

//
// 指定されたセッションに対して，学生(複数)の登録状態を最新の状態に更新する．
// セッションデータを返す．
//
function autoattend_update_session_users($courseid, $sessid, $ntime='')
{
    global $DB;

    if ($sessid<=0) return null;
    if (empty($ntime)) $ntime = time();

    $sess = $DB->get_record('autoattend_sessions', array('id'=>$sessid));
    if (!$sess) return null;

    $context = jbxl_get_course_context($courseid);
    $stdnts  = jbxl_get_course_students($context);

    if ($sess->state!='C' and $ntime>$sess->starttime) {
        foreach($stdnts as $stdnt) {
            $user = $DB->get_record('autoattend_students', array('attsid'=>$sess->id, 'studentid'=>$stdnt->id));
            if (empty($user)) {
                autoattend_add_user_insession($sess->id, $stdnt->id);
            }
        }
    }
    return $sess;
}


//
// 指定された授業（セッション）の点呼状態を最新の状態に更新する．
//
function autoattend_update_session_state($courseid, $sess, $ntime='', $regist=true)
{
    global $DB;

    if (!empty($sess)) {
        $sess->prv_state = $sess->state;
        //
        if ($sess->state!='C') {
            //
            if (empty($ntime)) $ntime = time();

            if ($ntime>$sess->endtime) {
                $state = 'C';
            }
            else if ($ntime>=$sess->starttime and $ntime<=$sess->endtime) {
                $state = 'O';
            }
            else {
                $state = 'N';
            }
            
            if ($sess->state!=$state) {
                $rec        = new stdClass();
                $rec->id    = $sess->id;
                $rec->state = $state;
                $DB->update_record('autoattend_sessions', $rec);
                unset($rec);
            }

            if ($regist) {
                if ($sess->state!='O' and $state=='O') {
                    autoattend_update_session_users($courseid, $sess->id, $ntime);  // 学生(複数)の授業レコードを登録
                }
            }
            $sess->state = $state;
        }
    }

    return $sess;
}


//
// コースの全授業（セッション）の点呼状態を最新の状態に更新する．
//
function autoattend_update_sessions_state($courseid, $sesss, $ntime='', $regist=true)
{
    if (!empty($sesss) and is_array($sesss)) {
        if (empty($ntime)) $ntime = time();
        //
        foreach($sesss as $key=>$sess) {
            if ($sess->state!='C') {
                $sesss[$key] = autoattend_update_session_state($courseid, $sesss[$key], $ntime, $regist);
            }
            else {
                $sesss[$key]->prv_state = $sesss[$key]->state;
            }
        }
    }

    return $sesss;
}
/*
            $sesss[$key]->prv_state = $sesss[$key]->state;
            if ($ntime>$sess->endtime) {
                if ($close) $state = 'C';
                else        $state = 'O';        // Close 処理しない
            }
            else if ($ntime>=$sess->starttime and $ntime<=$sess->endtime) {
                $state = 'O';
            }
            else {
                $state = 'N';
            }
            
            $rec        = new stdClass();
            $rec->id    = $sess->id;
            $rec->state = $state;
            $DB->update_record('autoattend_sessions', $rec);
            unset($rec);

            $sesss[$key]->state = $state;
*/


//
// 指定されたセッションのデータを最新の状態に更新する
//
function autoattend_update_session($courseid, $sessid, $ntime='')
{
    global $DB;

    if ($sessid<=0) return null;

    $sess = $DB->get_record('autoattend_sessions', array('id'=>$sessid));

    if ($sess and $sess->state!='C') {
        if (empty($ntime)) $ntime = time();
        //
        $sess = autoattend_update_session_state($courseid, $sess, $ntime);    // 授業の状態を更新
        if ($sess) {
            if ($sess->method=='A') {     // 自動処理
                $logs = autoattend_get_courselogs($courseid, $sess->starttime, $sess->endtime);
                autoattend_update_auto_session($courseid, $sess, $logs, $ntime);
            }
            if ($sess->state=='C') {    // 授業で終了したものをクローズ
                autoattend_close_session($courseid, $sess, $ntime);
                // mail
                if (autoattend_is_email_enable($courseid)) {
                    autoattend_email_teachers_attend($sess, $courseid);
                }
            }
            else if ($sess->prv_state!='O' and $sess->state=='O' and $sess->method=='S') {
                if (autoattend_is_email_key($courseid)) {
                    autoattend_email_teachers_key($sess, $courseid);
                }
            }
            //
            autoattend_update_grades($courseid);
            return $sess;
        }
    }

    return null;
}


//
// 指定されたコースの全てのデータを最新の状態に更新する
//
function autoattend_update_sessions($courseid, $ntime='')
{
    if (empty($ntime)) $ntime = time();

    $sesss = autoattend_get_unclosed_sessions($courseid, '', $ntime, true);  // Open中を含む
    $sesss = autoattend_update_sessions_state($courseid, $sesss, $ntime);    // 授業の状態を更新

    if ($sesss) {
        //
        $getlog = false;
        $etime  = 0;
        $stime  = $ntime;
        //
        foreach($sesss as $sess) {
            if ($ntime>$sess->starttime and $sess->prv_state!='C') {
                if ($stime>$sess->starttime) $stime = $sess->starttime;
                if ($etime<$sess->endtime)               $etime = $sess->endtime;
                if ($sess->method=='A') $getlog = true;
            }
        }
        if ($getlog) $logs = autoattend_get_courselogs($courseid, $stime, $etime);
        else         $logs = '';

        //
        foreach($sesss as $sess) {
            if ($ntime>$sess->starttime and $sess->prv_state!='C') {
                if ($sess->method=='A') {   // 自動処理
                    autoattend_update_auto_session($courseid, $sess, $logs, $ntime);
                }
                if ($sess->state=='C') {    // 授業で終了したものをクローズ
                    autoattend_close_session($courseid, $sess, $ntime);
                    // mail
                    if (autoattend_is_email_enable($courseid)) {
                        autoattend_email_teachers_attend($sess, $courseid);
                    }
                }
                else if ($sess->prv_state!='O' and  $sess->state=='O' and $sess->method=='S') {
                    if (autoattend_is_email_key($courseid)) {
                        autoattend_email_teachers_key($sess, $courseid);
                    }
                }
            }
        }
        //
        autoattend_update_grades($courseid);
        return true;
    }

    return false;
}


//
// 自動モードの処理
//
function autoattend_update_auto_session($courseid, $sess, $logs, $ntime='')
{
    global $DB;

    $ver = jbxl_get_moodle_version();

    $context = jbxl_get_course_context($courseid);
    $users = jbxl_get_course_students($context);

    if ($users) {
        if (empty($ntime)) $ntime = time();

        $attsid   = $sess->id;
        $stime    = $sess->starttime;
        $etime    = $sess->endtime;
        $ltime    = $sess->latetime;
        $allowip  = $sess->allowip;
        $difipf   = $sess->denysameip;

        $ipfmts   = jbxl_to_subnetformats($allowip);
        $used_ips = autoattend_get_usedips($attsid);
        $sesslogs = array();
        $is_email_user = autoattend_is_email_user($courseid);

        if (!empty($logs)) {
            foreach($logs as $log) {
                if (floatval($ver)>=2.7) $logtime = $log->timecreated;
                else                     $logtime = $log->time;
                if ($logtime>=$stime and $logtime<=$etime) $sesslogs[] = $log;
            }
        }

        foreach($users as $user) {
            //
            $status   = 'X';
            $match_ip = '';
            $match_tm = 0;
            $err_mesg = '';
            $userlogs = array();

            if (!empty($sesslogs)) {
                foreach($sesslogs as $log) {
                    if ($log->userid==$user->id) $userlogs[] = $log;
                }
            }

            $rec             = new stdClass();
            $rec->attsid     = $sess->id;
            $rec->studentid  = $user->id;
            $rec->status     = 'Y';
            $rec->called     = 'A';
            $rec->calledby   = CALLED_BY_AUTO;
            $rec->calledtime = $ntime;
            $rec->sentemail  = 0;
            $rec->remarks    = '';
            $rec->ipaddress  = '';
            //
            if (!empty($userlogs)) {
                $valid_log = autoattend_check_valid_logip($userlogs, $ipfmts, $used_ips, $difipf); 
                if (!empty($valid_log)) {
                    if (isset($valid_log['ip']))    $match_ip = $valid_log['ip'];
                    if (isset($valid_log['time']))  $match_tm = $valid_log['time'];
                    if (isset($valid_log['error'])) $err_mesg = $valid_log['error'];
                }

                if (!empty($match_ip)) {
                    $status = 'P';
                    if ($difipf) $used_ips[] = $match_ip;
                    if ($ltime!=0) {
                        if ($match_tm > $stime + $ltime) $status = 'L';    
                    }
                    $rec->ipaddress = $match_ip;
                }
            }
            // ここで，$status は X, P, L のいずれか．
        
            if ($status!='X') {
                // $status は P か L
                if ($match_tm!=0) $calledtime = $match_tm;
                else              $calledtime = $ntime;
                $rec->status = $status;
                $rec->sentemail = 0;
                $sentemail = false;
                $result = '';

                $student = $DB->get_record('autoattend_students', array('attsid'=>$sess->id, 'studentid'=>$user->id));
                if ($student and $student->status=='Y') {
                    $rec->id         = $student->id;
                    $rec->calledtime = $calledtime;
                    $rec->sentemail  = $student->sentemail;
                    $rec->remarks    = $student->remarks;
                    if ($is_email_user and !$student->sentemail) {
                        $rec->sentemail = 1;
                        $sentemail = true;
                    }
                    $result = $DB->update_record('autoattend_students', $rec);
                }
                else if (empty($student)) {
                    if ($is_email_user) {
                        $rec->sentemail = 1;
                        $sentemail = true;
                    }
                    $result = $DB->insert_record('autoattend_students', $rec); 
                }
                if ($result and $sentemail) autoattend_email_user($sess, $user, $status, $courseid);
            }
            //
            if ($err_mesg) {
                $mdluser = $DB->get_record('user', array('id'=>$user->id));
                //$loginfo = AUTO_SUBMIT_LOG.',id='.$sess->id.',userid='.$user->id.',user='.fullname($mdluser).',Error('.$err_mesg.')';
            }
            unset($rec);
        }
    }
}


//
// 授業のクローズ処理
//
function autoattend_close_session($courseid, $sess, $ntime='')
{
    global $DB;

    if ($sess->state=='C') {
        //
        $context = jbxl_get_course_context($courseid);
        $users = jbxl_get_course_students($context);
        //$users = autoattend_get_users_bystatus($sess->id, 'Y');

        if ($users) {
            if (empty($ntime)) $ntime = time();
            //
            $rec             = new stdClass();
            $rec->attsid     = $sess->id;
            $rec->status     = 'X';
            $rec->called     = $sess->method;
            $rec->calledby   = 0;
            $rec->calledtime = $ntime;
            $rec->remarks    = '';
            $rec->ipaddress  = '';

            $is_email_user = autoattend_is_email_user($courseid);

            foreach($users as $user) {
                $rec->studentid = $user->id;
                $rec->sentemail = 0;
                $sentemail = false;
                $result = '';
                //
                $student = $DB->get_record('autoattend_students', array('attsid'=>$sess->id, 'studentid'=>$user->id));
                if (empty($student)) {
                    if ($is_email_user) {
                        $rec->sentemail = 1;
                        $sentemail = true;
                    }
                    $result = $DB->insert_record('autoattend_students', $rec); 
                }
                else {
                    if ($student->status=='Y') {
                        $rec->id = $student->id;
                        $rec->sentemail = $student->sentemail;
                        if ($is_email_user and !$student->sentemail) {
                            $rec->sentemail = 1;
                            $sentemail = true;
                        }
                        $result = $DB->update_record('autoattend_students', $rec);
                    }
                }
                if ($result and $sentemail) autoattend_email_user($sess, $user, 'X', $courseid);
            }
            //
            unset($rec);
        }
    }
}


//
// 自動出欠モードでの学生の出欠を未了状態に戻す．
//
// 学生への通知メールのフラグはリセットしない
//
function autoattend_return_to_Y($sessid)
{
    global $DB;

    $students = $DB->get_records('autoattend_students', array('attsid'=>$sessid));
    if ($students) {
        foreach ($students as $student) {
            if ($student->called=='A') {
                $student->status = 'Y';
                $student->ipaddress = '';
                $student->calledbya = 0;
                $DB->update_record('autoattend_students', $student);
            }
        }
    }

    return;
}


//
// logの中から許可されたフォーマット ipfmts に一致するIPを探す
// ただしdifipf が trueの場合，配列 userd_ipsに含まれるIPは一致から除外する．
//
function autoattend_check_valid_logip($userlogs, $ipfmts, $used_ips, $difipf)
{
    $return = array();
    if (empty($userlogs)) return $return;

    $ver = jbxl_get_moodle_version();
    $err_mesg = '';

    foreach($userlogs as $log) {
        $chkip = $log->ip;
        if (empty($ipfmts) or jbxl_match_ipaddr($chkip, $ipfmts)) {
            $chkip_f = true;
            if ($difipf and !empty($used_ips)) {
                foreach($used_ips as $used_ip) {    // 同一IPチェック
                    if ($chkip==$used_ip) {
                        $chkip_f = false;
                        if (!empty($err_mesg)) $err_mesg.= ',';
                        $err_mesg.= 'already:'.$chkip;
                        break;
                    }
                }
            }
            if ($chkip_f) {
                $return['ip'] = $chkip;
                if (floatval($ver)>=2.7) $return['time'] = $log->timecreated;
                else                     $return['time'] = $log->time;
                $return['userid'] = $log->userid;
                return $return;
            }    
        }
        else {
            if (!empty($err_mesg)) $err_mesg.= ',';
            $err_mesg.= 'notmatch:'.$chkip;
        }
    }
    if ($err_mesg) $return['error'] = $err_mesg;

    return $return;
}


//
// 半自動モードに於いて接続IPの検査を行う．
//
function autoattend_check_invalid_semiautoip($att)
{
    $ipaddr  = getremoteaddr();
    $allowip = $att->allowip;
    $difipf  = $att->denysameip;

    $iperrmesg = '';
    $ipfmts = jbxl_to_subnetformats($allowip);

    if (empty($ipfmts) or jbxl_match_ipaddr($ipaddr, $ipfmts)) {
        if ($difipf) {
            $used_ips = autoattend_get_usedips($att->id);
            if ($used_ips) {
                foreach($used_ips as $used_ip) {
                    if ($ipaddr==$used_ip) {
                        $iperrmesg = get_string('sameusedip', 'block_autoattend').' '.$ipaddr;
                        break;
                    }
                }
            }
        }
    }
    else {
        $iperrmesg = get_string('mismatchip', 'block_autoattend').' '.$ipaddr;
    }

    return $iperrmesg;
}


//
// 既に取られた出席からIPを取り出す
//
function autoattend_get_usedips($attsid)
{
    global $DB;
    
    $results = $DB->get_records('autoattend_students', array('attsid'=>$attsid));

    $ips = array();
    foreach ($results as $result) {
        $ips[] = $result->ipaddress;
    }

    return $ips;
}


//
// 指定したコースの $stime から $etime までのログを得る
//
function autoattend_get_courselogs($courseid, $stime, $etime=0)
{
    global $CFG, $DB;

    $return = array();

    if ($etime<=0) {
        $etime = time();
    }

    $ver = jbxl_get_moodle_version();
    if (floatval($ver)>=2.7) {
        $where = 'WHERE courseid='.$courseid;
        $where.= ' AND timecreated>='.$stime.' AND timecreated<='.$etime;
        $where.= " AND (action='viewed' OR action='review') ORDER BY timecreated ASC";
        $qry   = "SELECT * FROM {$CFG->prefix}logstore_standard_log ".$where;
    }
    else {
        $where = 'WHERE course='.$courseid;
        $where.= ' AND time>='.$stime.' AND time<='.$etime;
        $where.= " AND (action='view' OR action='review') ORDER BY time ASC";
        $qry   = "SELECT * FROM {$CFG->prefix}log ".$where;
    }

    //print "QUERY = $qry<br/>";
    $logs = $DB->get_records_sql($qry);

    return $logs;
}


/*
//
// 指定したコースの過去 $day日のログを得る
//
function autoattend_get_courselogs_pastdays($courseid, $day=0)
{
    global $CFG, $DB;

    $return = array();

    $where = 'WHERE course='.$courseid;
    if ($day>0) {
        $lmtdy = time() - $day*ONE_DAY_TIME;
        $where.= ' AND time>'.$lmtdy;
    }
    $where.= " AND (action='view' OR action='review') ORDER BY time ASC";
    $qry = "SELECT * FROM {$CFG->prefix}log ".$where;

    //print "QUERY = $qry<br/>";
    $logs = $DB->get_records_sql($qry);

    return $logs;
}
*/



/////////////////////////////////////////////////////////////////////////////////////////////
//
//


/*
    return 0 ... local ip address
    return 1 ... global ip address
    return 2 ... not ip address
*/
function  autoattend_is_localip($ip)
{
    if (!preg_match('/(^\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip, $match)) return 2;
    if ($match[1]>255 or $match[2]>255 or $match[3]>255 or $match[4]>255) return 2;

    if ($match[1]=='127' or  $match[1]=='10') return 0;
    if ($match[1]=='172' and $match[2]>='16' and $match[2]<='31') return 0;
    if ($match[1]=='192' and $match[2]=='168') return 0;

    return 1;
}



function  autoattend_get_ipresolv_url($ip)
{
    global $CFG;

    $ret = autoattend_is_localip($ip);
    if ($ret!=1) return ''; // global ip address

    if (!empty($CFG->ipresolv_url)) $url = sprintf($CFG->ipresolv_url, $ip);
    else $url = sprintf(get_string('ipresolv_url','block_autoattend'), $ip);

    return $url;
}



/////////////////////////////////////////////////////////////////////////////////////////////
//
// autoattendmod 連携
//         autoattendmod が必要
// 

//
// E-Mail
//

function autoattend_email_text($info, $mesg, $ttle)
{
    $posttext  = $info->shortname.' ['.$info->date.' '.$info->starttm.'-'.$info->endtm.']';
    $posttext .= "\n--------------------------------------------------------------------------\n";
    $posttext .= get_string($ttle, 'block_autoattend')."\n\n";
    $posttext .= get_string($mesg, 'block_autoattend', $info)."\n";
    $posttext .= "\n--------------------------------------------------------------------------\n";

    return $posttext;
}


function autoattend_email_html($info, $mesg, $ttle)
{
    $posthtml  = '<h3>'.$info->shortname.'&nbsp;['.$info->date.'&nbsp;'.$info->starttm.'-'.$info->endtm.']</h3><hr />'."\n";
    $posthtml .= '<div style="font-weight:bold;">'.get_string($ttle, 'block_autoattend').'</div><br />'."\n";
    $posthtml .= '<font face="sans-serif"><p>'."\n";
    $posthtml .= get_string($mesg, 'block_autoattend', $info)."\n";
    $posthtml .= '</p></font><hr />';

    return $posthtml;
}


function autoattend_email_teachers_attend($sess, $couseid)
{
    global $CFG, $DB, $TIME_OFFSET;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');

        $use_summertime = autoattend_use_summertime($couseid);
        $summertime = autoattend_get_summertime($sess->id, $use_summertime)*ONE_HOUR_TIME;
        //
        if (!file_exists($CFG->tempdir.'/autoattend')) {
            mkdir($CFG->tempdir.'/autoattend');
        }
        //
        $courseid = $sess->courseid;
        $course   = $DB->get_record('course', array('id'=>$courseid));
        $classes  = autoattend_get_session_classes($courseid);

        //$daytime  = strftime(get_string('strftimedmyw', 'block_autoattend'),    $sess->sessdate  + $summertime + $TIME_OFFSET);
        $daytime  = strftime(get_string('strftimedmyw', 'block_autoattend'),    $sess->starttime + $summertime + $TIME_OFFSET);
        $starttm  = strftime(get_string('strftimehourmin', 'block_autoattend'), $sess->starttime + $summertime + $TIME_OFFSET);
        $endtm    = strftime(get_string('strftimehourmin', 'block_autoattend'), $sess->endtime   + $summertime + $TIME_OFFSET);
        $pathname = $CFG->tempdir.'/autoattend/attendance_'.$sess->id.'_'.date('YmdHis').'.$$$';
        $filename = get_string('attendance', 'block_autoattend').'_'.$course->fullname.'_'.$daytime.'.csv';

        $datas = autoattend_make_download_data($courseid, $classes, 0, 'all', 0, $sess->id);
        jbxl_save_csv_file($datas, $pathname);

        $info = new stdClass();
        $info->shortname = $course->shortname;
        $info->fullname  = $course->fullname;
        $info->date      = $daytime;
        $info->starttm   = $starttm;
        $info->endtm     = $endtm;

        $posttext = autoattend_email_text($info, 'email_teacher_attend', 'attenddata');
        $posthtml = autoattend_email_html($info, 'email_teacher_attend', 'attenddata');
        $subject  = get_string('attenddata', 'block_autoattend').': '.$course->fullname.': '.$daytime.' '.$starttm.'-'.$endtm;
        autoattendmod_send_email_teachers($courseid, $subject, $posttext, $posthtml, $pathname, $filename);

        unlink($pathname);
    }
    return;
}


function autoattend_email_teachers_key($sess, $courseid)
{
    global $CFG, $DB, $TIME_OFFSET;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        //
        $use_summertime = autoattend_use_summertime($couseid);
        $summertime = autoattend_get_summertime($sess->id, $use_summertime)*ONE_HOUR_TIME;
        //
        $courseid = $sess->courseid;
        $course   = $DB->get_record('course', array('id'=>$courseid));
        //$daytime  = strftime(get_string('strftimedmyw', 'block_autoattend'),    $sess->sessdate  + $summertime + $TIME_OFFSET);
        $daytime  = strftime(get_string('strftimedmyw', 'block_autoattend'),    $sess->starttime + $summertime + $TIME_OFFSET);
        $starttm  = strftime(get_string('strftimehourmin', 'block_autoattend'), $sess->starttime + $summertime + $TIME_OFFSET);
        $endtm    = strftime(get_string('strftimehourmin', 'block_autoattend'), $sess->endtime   + $summertime + $TIME_OFFSET);

        $info = new stdClass();
        $info->shortname = $course->shortname;
        $info->fullname  = $course->fullname;
        $info->key       = $sess->attendkey;
        $info->date      = $daytime;
        $info->starttm   = $starttm;
        $info->endtm     = $endtm;
    
        $posttext = autoattend_email_text($info, 'email_teacher_key', 'attendkey');
        $posthtml = autoattend_email_html($info, 'email_teacher_key_html', 'attendkey');
        $subject  = get_string('attendkey', 'block_autoattend').': '.$course->fullname.': '.$daytime.' '.$starttm.'-'.$endtm;
        autoattendmod_send_email_teachers($courseid, $subject, $posttext, $posthtml, null, null);
    }
    return;
}


function autoattend_email_user($sess, $user, $status, $couseid)
{
    global $CFG, $DB, $TIME_OFFSET;

    if ($status=='Y') return;    // 通知なし

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        //
        $use_summertime = autoattend_use_summertime($couseid);
        $summertime = autoattend_get_summertime($sess->id, $use_summertime)*ONE_HOUR_TIME;
        //
        $courseid = $sess->courseid;
        $course   = $DB->get_record('course', array('id'=>$courseid));
        //$daytime  = strftime(get_string('strftimedmyw', 'block_autoattend'),    $sess->sessdate  + $summertime + $TIME_OFFSET);
        $daytime  = strftime(get_string('strftimedmyw', 'block_autoattend'),    $sess->starttime + $summertime + $TIME_OFFSET);
        $starttm  = strftime(get_string('strftimehourmin', 'block_autoattend'), $sess->starttime + $summertime + $TIME_OFFSET);
        $endtm    = strftime(get_string('strftimehourmin', 'block_autoattend'), $sess->endtime   + $summertime + $TIME_OFFSET);

        $info = new stdClass();
        $info->shortname = $course->shortname;
        $info->fullname  = $course->fullname;
        $info->date      = $daytime;
        $info->starttm   = $starttm;
        $info->endtm     = $endtm;

        if ($status=='P') {
            $posttext = autoattend_email_text($info, 'email_user_attend_P', 'attendanceconfrm');
            $posthtml = autoattend_email_html($info, 'email_user_attend_P', 'attendanceconfrm');
        }
        else if ($status=='X') {
            $posttext = autoattend_email_text($info, 'email_user_attend_X', 'attendanceconfrm');
            $posthtml = autoattend_email_html($info, 'email_user_attend_X', 'attendanceconfrm');
        }
        else if ($status=='L') {
            $posttext = autoattend_email_text($info, 'email_user_attend_L', 'attendanceconfrm');
            $posthtml = autoattend_email_html($info, 'email_user_attend_L', 'attendanceconfrm');
        }
        else {
            $posttext = autoattend_email_text($info, 'email_user_attend_C', 'attendanceconfrm');
            $posthtml = autoattend_email_html($info, 'email_user_attend_C', 'attendanceconfrm');
        }
        
        $subject = get_string('attendanceconfrm', 'block_autoattend').': '.$course->fullname.': '.$daytime.' '.$starttm.'-'.$endtm;
        autoattendmod_send_email_user($courseid, $user, $subject, $posttext, $posthtml, null, null);
    }
    return;
}
 


//
// course_modules
//
function  autoattend_get_course_module($courseid) 
{
    global $CFG;

    $mod = null;
    if ($courseid==0) return $mod;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $mod = autoattendmod_get_course_module($courseid);
    }
    return $mod;
}


//
// 評定を更新する
//
function  autoattend_update_grades($courseid) 
{
    global $CFG, $DB;

    if ($courseid==0) return;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        //
        $mod = autoattend_get_course_module($courseid);    // course_modules;
        if ($mod) {
            $autoattendmod = $DB->get_record('autoattendmod', array('id'=>$mod->instance));
            if ($autoattendmod) {
                $autoattendmod->idnumber = $mod->idnumber;
                autoattendmod_update_grades($autoattendmod);
            }
        }
    }
}



///////////////////////////////////////////////////////////
//
//
function  autoattend_get_namepattern($courseid)
{
    global $CFG;

    $ret = 'fullname';
    if ($courseid==0) return $ret;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $ret = autoattendmod_get_namepattern($courseid);
    }
    return $ret;
}


function  autoattend_get_disp_info($courseid)
{
    global $CFG;

    $ret = 1;
    if ($courseid==0) return $ret;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $ret = autoattendmod_get_disp_info($courseid);
    }
    return $ret;
}


function  autoattend_get_predisp_time($courseid)
{
    global $CFG;

    $dtm = 100;    // default +5m    
    if ($courseid==0) return $dtm;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $dtm = autoattendmod_get_predisp_time($courseid);
    }
    return $dtm;
}


function  autoattend_disp_feedback($courseid)
{
    global $CFG;

    $ret = 1;
    if ($courseid==0) return $ret;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $ret = autoattendmod_disp_feedback($courseid);
    }
    return $ret;
}


function  autoattend_is_email_enable($courseid)
{
    global $CFG;

    $ret = 0;
    if ($courseid==0) return $ret;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $ret = autoattendmod_is_email_enable($courseid);
    }
    return $ret;
}


function  autoattend_is_email_allreports($courseid)
{
    global $CFG;

    $ret = 0;
    if ($courseid==0) return $ret;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $ret = autoattendmod_is_email_allreports($courseid);
    }
    return $ret;
}


function  autoattend_is_email_key($courseid)
{
    global $CFG;

    $ret = 0;
    if ($courseid==0) return $ret;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $ret = autoattendmod_is_email_key($courseid);
    }
    return $ret;
}


function  autoattend_is_email_user($courseid)
{
    global $CFG;

    $ret = 0;
    if ($courseid==0) return $ret;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $ret = autoattendmod_is_email_user($courseid);
    }
    return $ret;
}


function  autoattend_use_summertime($courseid)
{
    global $CFG;

    $ret = 0;
    if ($courseid==0) return $ret;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $ret = autoattendmod_use_summertime($courseid);
    }
    return $ret;
}


function  autoattend_is_old_excel($courseid)
{
    global $CFG;

    $ret = 0;
    if ($courseid==0) return $ret;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $ret = autoattendmod_is_old_excel($courseid);
    }
    return $ret;
}


function  autoattend_is_backup_block($courseid)
{
    global $CFG;

    $ret = 0;
    if ($courseid==0) return $ret;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $ret = autoattendmod_is_backup_block($courseid);
    }
    return $ret;
}



function  autoattend_disp_localhostname($courseid)
{
    global $CFG;

    $ret = 0;
    if ($courseid==0) return $ret;

    if  (file_exists($CFG->dirroot.'/mod/autoattendmod/locallib.php')) {
        require_once($CFG->dirroot.'/mod/autoattendmod/locallib.php');
        $ret = autoattendmod_disp_localhostname($courseid);
    }
    return $ret;
}





////////////////////////////////////////////////////////////////////////////////////
//
// for event log
//

function  autoattend_get_event($context, $action, $params='', $info='')
{
    global $CFG;

    $ver = jbxl_get_moodle_version();

    $event = null;
    if (!is_array($params)) $params = array();

    if (floatval($ver)>=2.7) {
        $params = array(
            'contextid' => $context->id,
            'other' => array('params' => $params, 'info'=> $info),
        );
        //
        if ($action=='view') {
            $event = \block_autoattend\event\index_view::create($params);
        }
        else if ($action=='update') {
            $event = \block_autoattend\event\attendaction_update::create($params);
        }
        else if ($action=='delete') {
            $event = \block_autoattend\event\delete_del::create($params);
        }
        else if ($action=='submit') {
            $event = \block_autoattend\event\semiautoattend_submit::create($params);
        }
    }

    // for Legacy add_to_log()        
    else {
        if ($action=='view') {
            $file = 'index.php';
        }
        else if ($action=='update') {
            $file = 'attendaction.php';
        }
        else if ($action=='delete') {
            $file = 'delete.php';
        }
        else if ($action=='submit') {
            $file = 'semiautoattend.php';
        }
        else {
            $file = 'view.php';
        }
        $param_str = jbxl_get_url_params_str($params);

        $event = new stdClass();
        $event->courseid= $context->instanceid;
        $event->name    = 'autoattend'; 
        $event->action  = $action;
        $event->url     = $file.$param_str;
        $event->info    = $info;
    }
    
    return $event;
}

