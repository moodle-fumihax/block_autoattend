<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block
 * @subpackage autoattend
 * @copyright  2016 Fumi.Iseki
 */

/**
 * Define all the restore steps that wll be used by the restore_autoattend_block_task
 */

/**
 * Define the complete autoattend  structure for restore
 */
class restore_autoattend_block_structure_step extends restore_structure_step {

    protected function define_structure() {

        $paths = array();

        $paths[] = new restore_path_element('autoattend_session',  '/block/autoattend/sessions/session', true);
        $paths[] = new restore_path_element('autoattend_student',  '/block/autoattend/sessions/session/students/student');
        $paths[] = new restore_path_element('autoattend_setting',  '/block/autoattend/settings/setting');
        $paths[] = new restore_path_element('autoattend_class',    '/block/autoattend/classes/class');
        $paths[] = new restore_path_element('autoattend_classify', '/block/autoattend/classifies/classify');

        return $paths;
    }

    //
    public function process_autoattend_session($data) {
        global $DB;

        $students = array();

        $data = (object)$data;
        $data->courseid = $this->get_courseid();
        //$data->timemodified = $this->apply_date_offset($data->timemodified);

        if (property_exists($data, 'students')) {
            $students = $data->students['student'];
            unset($data->students);
        }
        $attsid = $DB->insert_record('autoattend_sessions', $data);

        foreach($students as $student) {
            $stdntdat = new stdClass();
            $stdntdat->attsid     = $attsid;
            $stdntdat->studentid  = $student['studentid'];
            $stdntdat->status     = $student['status'];
            $stdntdat->called     = $student['called'];
            $stdntdat->calledby   = $student['calledby'];
            $stdntdat->calledtime = $student['calledtime'];
            $stdntdat->sentemail  = $student['sentemail'];
            $stdntdat->remarks    = $student['remarks'];
            $stdntdat->ipaddress  = $student['ipaddress'];
            $DB->insert_record('autoattend_students', $stdntdat);
            unset($stdntdat);
        }
    }

    //
    public function process_autoattend_setting($data) {
        global $DB;

        $data = (object)$data;
        $data->courseid = $this->get_courseid();

        $DB->insert_record('autoattend_settings', $data);
    }

    //
    public function process_autoattend_class($data) {
        global $DB;

        $data = (object)$data;
        $data->courseid = $this->get_courseid();
        //$data->timemodified = $this->apply_date_offset($data->timemodified);

        $DB->insert_record('autoattend_classes', $data);
    }

    //
    public function process_autoattend_classify($data) {
        global $DB;

        $data = (object)$data;
        $data->courseid = $this->get_courseid();

        $DB->insert_record('autoattend_classifies', $data);
    }
}
