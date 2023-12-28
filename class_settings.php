<?php

require_once('../../config.php');
require_once(dirname(__FILE__).'/locallib.php');


$courseid = required_param('course', PARAM_INTEGER);	// Course id
$confirm  = optional_param('confirm','', PARAM_INTEGER);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
	print_error('invalidsesskey');
}

$urlparams['course'] = $courseid;
if ($confirm) $urlparams['confirm'] = $confirm;
$PAGE->set_url('/blocks/autoattend/class_settings.php', $urlparams);

$wwwBlock = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL = $wwwBlock.'/class_settings.php';


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



//////////////////////////////////////////////////////////////////////////////////////////
//
function class_settings_show_table($classes)
{
	$table = new html_table();
	//
	$table->head [] = '#';
	$table->align[] = 'center';
	$table->size [] = '20px';
	$table->wrap [] = 'nowrap';

	$table->head [] = get_string('classname','block_autoattend');
	$table->align[] = 'center';
	$table->size [] = '60px';
	$table->wrap [] = 'nowrap';

	$table->head [] = get_string('delete');
	$table->align[] = 'center';
	$table->size [] = '60px';
	$table->wrap [] = 'nowrap';

	//
	$i = 0;
	foreach($classes as $class) {
		$classid_input = '<input type="hidden" name="classids['.$i.']" value="'.$class->id.'" />';
		$table->data[$i][] = $i + 1;
		$table->data[$i][] = '<input type="text" name="classnames['.$i.']" size="24" maxlength="32" value="'.$class->name.'" />';
		$table->data[$i][] = '<input type="checkbox" name="classdels['.$i.']" value="1" />'.$classid_input;
		$i++;
	}

	echo '<div align="center">';
	echo html_writer::table($table);
	echo '</div>';

	return $i;
}



//////////////////////////////////////////////////////////////////////////////////////////
// Print header
$title = $course->shortname.': '.get_string('autoattend','block_autoattend');
if ($course->category) {
	$title.= ' '.get_string('class_settings','block_autoattend');
} 

$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_button('&nbsp;');
//$PAGE->set_headingmenu();

echo $OUTPUT->header();

$currenttab = 'class_settings';	
include('tabs.php');


if($isteacher) {
	//
	if (isset($formdata->addclass)) {
		$classname = required_param('classname', PARAM_TEXT);
		if ($classname) {
			autoattend_insert_session_class($course->id, $classname);
		}
	} 
	//
	// by Emilio Arjona
	else if (isset($formdata->groupingtoclass)) {
		$groupingid = required_param('groupingid', PARAM_TEXT);

		if(isset($groupingid) and $groupingid!=''){
			$groups = groups_get_all_groups($course->id, 0, $groupingid);
			$existingclasses = autoattend_get_session_classes($courseid);
			
			if (isset($groups)){
				foreach ($groups as $group){
					$createclass = true;
					// Check groups and class names. 
					if (!empty($existingclasses)){
						foreach ($existingclasses as $existingclass){
							if ($group->name==autoattend_get_session_classname($existingclass->id)){
								// Class exists
								$newclassid= $existingclass->id;
								$createclass = false;
							}
						}
					}
					// Create class				
					if ($createclass){
						$newclassid = autoattend_insert_session_class($course->id, $group->name);
					}
					// $newclassid, id of the class to join users (created or existing)
					// Join group members to class					
					$students = groups_get_members ($group->id);
					foreach ($students as $student){
						$userclass = autoattend_get_user_class($student->id, $course->id, true);
						if ($userclass->classid!=$newclassid) {
							$userclass->classid = $newclassid;
							if ($userclass->id!=0) {
								$DB->update_record('autoattend_classifies', $userclass);
							}
							else {
								$userclass->id = $DB->insert_record('autoattend_classifies', $userclass);
							}							
						}
					}					
				}
			}
		}
	} 
	//
	else if (isset($formdata->updateclass)) {
		$exist_flag = false;
		$classids	= array();
		$classnames = array();
		$classdels	= array();
		//
		if (!$confirm) {
			$classids  = required_param_array('classids',   PARAM_INTEGER);
			$classdels = optional_param_array('classdels', array(), PARAM_INTEGER);
			if ($classdels) {
				foreach ($classdels as $key=>$classdel) {
					$sessions = $DB->get_records('autoattend_sessions', array('classid'=>$classids[$key]));
					if ($sessions) {
						foreach ($sessions as $session) {
							$students = $DB->get_records('autoattend_students', array('attsid'=>$session->id));
							if ($students) {
								$exist_flag = true;
								break;
							}
						}
						if ($exist_flag) break;
					}
				}
			}
			if ($exist_flag) {	// クラスの削除：既に出欠を取った講義が存在する．
				$classnames = required_param_array('classnames', PARAM_TEXT);
				include('html/class_delete.html');
				echo $OUTPUT->footer($course);
				exit();
			}
		}

		if (!$exist_flag) {
			if (empty($classids)) 	$classids	= required_param_array('classids',   PARAM_INTEGER);
			if (empty($classnames)) $classnames = required_param_array('classnames', PARAM_TEXT);
			if (empty($classdels))	$classdels	= optional_param_array('classdels',  array(), PARAM_INTEGER);
			autoattend_update_session_classes($classids, $classnames, $classdels);
		}
	}
	//
	else if (isset($formdata->submit_delete) and $confirm) {
		$classids	= required_param_array('classids',   PARAM_INTEGER);
		$classnames = required_param_array('classnames', PARAM_TEXT);
		$classdels	= optional_param_array('classdels',  array(), PARAM_INTEGER);
		autoattend_update_session_classes($classids, $classnames, $classdels);
	}

	//
	$classes = autoattend_get_session_classes($course->id);
	include('html/class_settings.html');
}

echo $OUTPUT->footer($course);

