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
 * Define all the backup steps that wll be used by the backup_autoattend_block_task
 */

/**
 * Define the complete forum structure for backup, with file and id annotations
 */
class backup_autoattend_block_structure_step extends backup_block_structure_step
{
    protected function define_structure() {
        //
        require_once(dirname(__FILE__).'/../../locallib.php');
        //
        $courseid = $this->get_courseid();
        $backup_block = autoattend_is_backup_block($courseid);

        // Define each element separated
        $autoattend = new backup_nested_element('autoattend', array('id'), null);
        if (!$backup_block) return $this->prepare_block_structure($autoattend);

        $sessions   = new backup_nested_element('sessions');
        $students   = new backup_nested_element('students');
        $settings   = new backup_nested_element('settings');
        $classes    = new backup_nested_element('classes');
        $classifies = new backup_nested_element('classifies');
 
        $session  = new backup_nested_element('session', array('id'), array(
            'classid', 'creator', 'sessdate', 'starttime', 'endtime', 'latetime', 'takenby', 'timetaken', 'method', 'attendkey', 'denysameip', 'allowip', 
            'description', 'state', 'timemodified')); 
        $student  = new backup_nested_element('student', array('id'), array(
            'studentid', 'status', 'called', 'calledby', 'calledtime', 'sentemail', 'remarks', 'ipaddress'));
        $setting  = new backup_nested_element('setting', array('id'), array('classid', 'status', 'acronym', 'title', 'description', 'grade')); 
        $class    = new backup_nested_element('class', array('id'), array('creator', 'name', 'timemodified'));
        $classify = new backup_nested_element('classify', array('id'), array('studentid', 'classid'));

        //
        // Build the tree
        $autoattend->add_child($sessions);
        $autoattend->add_child($settings);
        $autoattend->add_child($classes);
        $autoattend->add_child($classifies);

        $sessions->add_child($session);
        $settings->add_child($setting);
        $classes->add_child($class);
        $classifies->add_child($classify);

        $session->add_child($students);
        $students->add_child($student);

        //
        // Define sources
        $autoattend->set_source_array(array((object)array('id' => $this->task->get_blockid())));

        $session->set_source_table ('autoattend_sessions',   array('courseid' => backup::VAR_COURSEID));
        $setting->set_source_table ('autoattend_settings',   array('courseid' => backup::VAR_COURSEID));
        $class->set_source_table   ('autoattend_classes',    array('courseid' => backup::VAR_COURSEID));
        $classify->set_source_table('autoattend_classifies', array('courseid' => backup::VAR_COURSEID));
        $student->set_source_table ('autoattend_students',   array('attsid' => '../../id'));

        //
        // Annotations (none)

        //
        // Return the root element (autoattend), wrapped into standard block structure
        return $this->prepare_block_structure($autoattend);
    }
}
