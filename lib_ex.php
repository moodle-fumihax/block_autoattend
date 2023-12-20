<?php 

function autoattend_repairDB()
{
    global $DB;

    $students = $DB->get_records('autoattend_students');

    $num = 0;
    foreach($students as $student) {
        $users = $DB->get_records('autoattend_students', array('attsid'=>$student->attsid, 'studentid'=>$student->studentid));
        //
        if (count($users)>1) {
            $countp = 0;
            foreach($users as $user) {
                if ($user->status!='X' and $user->status!='Y') $countp++;
            }
            //
            if ($countp>0) {    // X,Y 以外の出席がある．
                $counts = 0;
                foreach($users as $user) {
                    if ($user->status!='X' and $user->status!='Y') $counts++;
                    if ($user->status=='X' or $user->status=='Y' or $counts>1) {
                        $DB->delete_records('autoattend_students', array('id'=>$user->id));
                        $num++;
                    }
                }
            }
            else {              // 全て X か Y
                $counts = 0;
                foreach($users as $user) {
                    $counts++;
                    if ($counts>1) {
                        $DB->delete_records('autoattend_students', array('id'=>$user->id));
                        $num++;
                    }
                }
            }
        }
    }

    return $num;
}


//
function autoattend_cleanup_sessionsDB()
{
    global $DB;

    //
    $courses  = $DB->get_records('course');
    $sessions = $DB->get_records('autoattend_sessions');

    $num = 0;
    foreach($sessions as $session) {
        $exist_flag = false;
        foreach($courses as $course) {
            if ($course->id==$session->courseid) {
                $exist_flag = true;
                break;
            }
        }
        if (!$exist_flag) {
            //echo "delete session data: ".$session->id."<br />";
            $DB->delete_records('autoattend_sessions', array('id'=>$session->id));
            $num++;
        }
    }

    return $num;
}


//
function autoattend_cleanup_studentsDB()
{
    global $DB;

    //
    $sessions = $DB->get_records('autoattend_sessions');
    $students = $DB->get_records('autoattend_students');

    $num = 0;
    foreach($students as $student) {
        $exist_flag = false;
        foreach($sessions as $session) {
            if ($session->id==$student->attsid) {
                $exist_flag = true;
                break;
            }
        }
        if (!$exist_flag) {
            //echo "delete student data: ".$student->id."<br />";
            $DB->delete_records('autoattend_students', array('id'=>$student->id));
            $num++;
        }
    }

    return $num;
}


//
function autoattend_delete_sessionsDB($otime=0)
{
    global $DB;

    $pcount = 0;
    $ncount = 0;
    if ($otime!='' and $otime>0) {
        $select = 'endtime<'.$otime;
        $pcount = $DB->count_records('autoattend_sessions');
        $DB->delete_records_select  ('autoattend_sessions', $select);
        $ncount = $DB->count_records('autoattend_sessions');
    }
    $num = $pcount - $ncount;

    return $num;
}

