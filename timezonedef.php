<?php

defined('MOODLE_INTERNAL') || die();


global $USER, $CFG;




$TIME_OFFSET = 0;
if (property_exists($CFG, 'use_timeoffset')) {
    if ($CFG->use_timeoffset) {
        //
        $ver = jbxl_get_moodle_version();
        if ($ver>=2.7) {
            if (is_numeric($CFG->timezone)) {
                $TIME_OFFSET = $CFG->timezone*ONE_HOUR_TIME;
            }
            else {
                $dtz = new DateTimeZone($CFG->timezone);
                $now = new DateTime("now", $dtz);
                $TIME_OFFSET = $dtz->getOffset($now);
            }
        }
        else {
            if (jbxl_is_admin($USER->id)) {
                if (is_numeric($USER->timezone) && $USER->timezone!=99) {
                    $TIME_OFFSET = $USER->timezone*ONE_HOUR_TIME;
                }
                else if (is_numeric($CFG->timezone) && $CFG->timezone!=99) {
                    $TIME_OFFSET = $CFG->timezone*ONE_HOUR_TIME;
                }
            }
        }
    }
}


//
$OMITTED_DAYS = array('0'=>'Sun','1'=>'Mon','2'=>'Tue','3'=>'Wed','4'=>'Thu','5'=>'Fri','6'=>'Sat');
