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
 * Defines backup_autoattend_block_task class
 *
 * @package     block_autoattend
 * @category    backup
 * @copyright   2016 Fumi.Iseki
 */

require_once($CFG->dirroot . '/blocks/autoattend/backup/moodle2/backup_autoattend_stepslib.php'); // We have structure steps

/**
 * Specialised backup task for the autoattend block
 * (has own DB structures to backup)
 *
 * TODO: Finish phpdocs
 */
class backup_autoattend_block_task extends backup_block_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        // autoattend has one structure step
        $this->add_step(new backup_autoattend_block_structure_step('autoattend_structure', 'autoattend.xml'));
    }

    public function get_fileareas() {
        return array(); // No associated fileareas
    }

    public function get_configdata_encoded_attributes() {
        return array(); // No special handling of configdata
    }

    static public function encode_content_links($content) {
        return $content; // No special encoding of links
    }
}

