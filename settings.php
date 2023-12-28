<?php

$courseid = optional_param('course', '0', PARAM_INT);
$course = $DB->get_record('course', array('id'=>$courseid));

//
$settings->add(new admin_setting_configtext('ipresolv_url',
					get_string('ipresolv', 'block_autoattend'),
				   	get_string('ipresolv_desc', 'block_autoattend'), get_string('ipresolv_url', 'block_autoattend')));

$settings->add(new admin_setting_configcheckbox('use_timeoffset', 
					get_string('use_timeoffset', 'block_autoattend'),
				   	get_string('use_timeoffset_desc', 'block_autoattend'), 0));

$settings->add(new admin_setting_configcheckbox('output_idnumber', 
					get_string('output_idnumber', 'block_autoattend'),
				   	get_string('output_idnumber_desc', 'block_autoattend'), 1));

//
if (property_exists($CFG, 'page_row_size')) {
	if ($CFG->page_row_size<=0) $CFG->page_row_size = 15;
	if ($CFG->page_column_size<=0) $CFG->page_column_size = 15;
}

$settings->add(new admin_setting_configtext('page_row_size',
                    get_string('page_row_size', 'block_autoattend'),
                    get_string('page_row_size_desc', 'block_autoattend'), '15', PARAM_INT));

$settings->add(new admin_setting_configtext('page_column_size',
                    get_string('page_column_size', 'block_autoattend'),
                    get_string('page_column_size_desc', 'block_autoattend'), '15', PARAM_INT));

