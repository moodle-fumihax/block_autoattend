<?php

require_once('../../config.php');	
require_once($CFG->libdir.'/blocklib.php');
require_once(dirname(__FILE__).'/locallib.php');

//
define('PAGE_ROW_SIZE', $CFG->page_row_size);


$courseid = required_param('course', PARAM_INTEGER);	 // Course id
$classid  = optional_param('class', 0, PARAM_INTEGER);
$sort 	  = optional_param('sort',  'firstname', PARAM_ALPHA);
$order 	  = optional_param('order', 'ASC', PARAM_ALPHA);

$newclassid = 0;
$newclassids = optional_param_array('newclass', array(), PARAM_INTEGER);

if (($formdata = data_submitted()) and !confirm_sesskey()) {
	print_error('invalidsesskey');
}

//
$wwwBlock  = $CFG->wwwroot.'/blocks/autoattend';
$wwwMyURL  = $wwwBlock.'/class_division.php';
$wwwParams = jbxl_get_url_params_array($_SERVER["REQUEST_URI"]);

if (!$sort) {
    if (array_key_exists('sort', $wwwParams)) {
        $sort = $wwwParams['sort'];
    }
    else {
        if ($CFG->output_idnumber) $sort = 'idnumber';
        else                       $sort = 'lastname';
    }
}

$urlparams['course'] = $courseid;
if ($classid) $urlparams['class'] = $classid;
if ($sort)	  $urlparams['sort']  = $sort;
if ($order)	  $urlparams['order'] = $order;
$PAGE->set_url('/blocks/autoattend/class_division.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$courseid));
if (!$course) {
	print_error('courseidwrong', 'block_autoattend');
}

require_login($course->id);

$context   = jbxl_get_course_context($course->id);
$isassist  = false;
$isteacher = jbxl_is_teacher($USER->id, $context);
if (!$isteacher) {
	$isassist = jbxl_is_assistant($USER->id, $context);
 	if (!$isassist) {
   		print_error('notaccessstudent', 'block_autoattend');
	}
}

$user = $DB->get_record('user', array('id'=>$USER->id));
if (!$user) {
	print_error('nosuchuser', 'block_autoattend');
}



//////////////////////////////////////////////////////////////////////////////////////////
//
function class_division_make_header(&$table, $url_options, $start, $end, $name_pattern, $order)
{
	global $CFG, $wwwMyURL;

	$firstname = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=firstname&amp;order='.$order.'">'.get_string('firstname').'</a>';
	$lastname  = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=lastname&amp;order='.$order.'">' .get_string('lastname').'</a>';
	$fullnamehead = jbxl_get_fullnamehead($name_pattern, $firstname, $lastname, '/');

	//
	unset($table->head);
	unset($table->align);
	unset($table->size);
	unset($table->wrap);

	$table->head [] = '#';
	$table->align[] = 'center';
	$table->size [] = '20px';
	
	$table->head [] = '';
	$table->align[] = '';
	$table->size [] = '20px';
		
	$table->head [] = $fullnamehead;
	$table->align[] = 'left';
	$table->size [] = '140px';
	$table->wrap [2]= 'nowrap';

	$cellnum = 4;
	if ($CFG->output_idnumber) {
		$table->head [] = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=idnumber&amp;order='.$order.'">ID</a>';
		$table->align[] = 'center';
		$table->size [] = '60px';
		$table->wrap [] = 'nowrap';
		$cellnum++;
	}

	$table->head [] = get_string('classname', 'block_autoattend');
	$table->align[] = 'center';
	$table->size [] = '80px';
	$table->wrap [] = 'nowrap';

	$table->head [] = "<a href=\"javascript:select_all_check_in('TD','cell c".$cellnum." lastcol',null,$start,$end);\">".get_string('changeclass','block_autoattend')."</a>";
	$table->align[] = 'center';
	$table->size [] = '80px';
	$table->wrap [] = 'nowrap';
	
	return;
}



function class_division_show_table($students, $courseid, $classid, $name_pattern, $order, $classes, $newclassid)
{
	global $CFG, $wwwBlock, $OUTPUT;

	$url_options = '?course='.$courseid.'&amp;class='.$classid;
	//
	if (empty($order) or $order=='ASC') {
		$order = 'DESC';
	}
	else $order = 'ASC';

	//
	$table = new html_table();

	$i = 0;
	$n = 0;
	foreach($students as $student) {
		$classinfo = autoattend_get_user_class($student->id, $courseid);
		if ($classinfo->classid==$classid or $classid==0 or ($classid==NON_CLASSID and $classinfo->classid==0) 
														 or ($classid==VALID_CLASSID and $classinfo->classid>=0)) {
			$i++;
			$table->data[$student->id][] = $i; 
			$username = jbxl_get_user_name($student->id, $name_pattern);
			$pic_options = array('size'=>20, 'link'=>true, 'alttext'=>true, 'courseid'=>$courseid, 'popup'=>true);
			$table->data[$student->id][] = $OUTPUT->user_picture($student, $pic_options);
			$table->data[$student->id][] = '<a href="'.$wwwBlock.'/index.php?course='.$courseid.'&amp;student='.$student->id.'" target="_blank">'.$username.'</a>';
			if ($CFG->output_idnumber) {
				if (empty($student->idnumber)) $idnum = ' - ';
				else						   $idnum = $student->idnumber;
				$table->data[$student->id][] = $idnum;
			}
			$table->data[$student->id][] = $classinfo->name;
			$table->data[$student->id][] = '<input type="checkbox" name="changeclass'.$student->id.'" value="1" />';
			//
 			if ($i%PAGE_ROW_SIZE==0) {
				echo '<div align="center">';
 				class_division_make_header($table, $url_options, $i-PAGE_ROW_SIZE, $i, $name_pattern, $order);
				echo html_writer::table($table);
				class_division_submit_button($classes, $newclassid, $n);
				$n++;
				echo '</div><br />';
				//echo '</div>';
				unset($table->data);
			}
		}
	}
	if ($i%PAGE_ROW_SIZE!=0 or $i==0) {
		echo '<div align="center">';
		class_division_make_header($table, $url_options, $i-$i%PAGE_ROW_SIZE, $i, $name_pattern, $order);
		echo html_writer::table($table);
		class_division_submit_button($classes, $newclassid, $n);
		echo '</div>';
	}

	return;
}



function class_division_submit_button($classes, $newclassid, $n)
{
	echo '<select name="newclass['.$n.']">';
	{
		if ($newclassid==0) $selected = 'selected="selected"'; 
		else                $selected = '';
		echo '<option value="0" '.$selected.'>'.get_string('nonclass','block_autoattend').'</option >';

		foreach ($classes as $class) { 
			if ($newclassid==$class->id) $selected = 'selected="selected"'; 
        	else                         $selected = '';
			echo '<option value="'.$class->id.'" '.$selected.'>'.$class->name.'</option>';
		}
		echo '<option value="-1" >'.get_string('exclusion','block_autoattend').'</option>';
	}
	echo '</select>&nbsp;&nbsp;';
	echo '<input type="submit" name="change_class['.$n.']" value="'.get_string('changeclass','block_autoattend').'" />';
}





//////////////////////////////////////////////////////////////////////////////////////////
// Print Header
$title = $course->shortname.': '.get_string('autoattend','block_autoattend');
if ($course->category) {
	//$title.= ' '.get_string('class_division','block_autoattend');
	$title.= ' '.get_string('students_list','block_autoattend');
} 

$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_button('&nbsp;');
//$PAGE->set_headingmenu();

echo $OUTPUT->header();

echo "<script type=\"text/javascript\">
<!--
function select_all_check_in(elTagName, elClass, elId, start, end) {
	var inputs = document.getElementsByTagName('INPUT');
	inputs = filterByParent(inputs, function(el) {return findParentNode(el, elTagName, elClass, elId);});
	for(var i=start; i<end; i++) {
		if(inputs[i].type=='checkbox') {
			inputs[i].checked = !inputs[i].checked;
		}
	}
}
//-->
</script>";


//
$currenttab = 'class_division';
include('tabs.php');

//
//$sort = ($sort=='firstname' ? 'firstname' : 'lastname');
if ($sort!='firstname' and $sort!='idnumber') $sort = 'lastname';
$sort .= ' '.$order;
$students = jbxl_get_course_students($context, $sort);


// Display Attendance Table
if ($students) {
	//
	if (isset($formdata->change_class) and is_array($formdata->change_class)) {
		//
		foreach ($formdata->change_class as $key=>$value) {
			$newclassid = $newclassids[$key];
			//
			$cnt = 0;
			foreach($_POST as $key=>$value) {
				if (substr($key, 0, 11)=='changeclass') {
					$studentid = substr($key, 11, strlen($key)-11);
					if (is_numeric($studentid)) {
						$class = autoattend_get_user_class($studentid, $course->id);
						if ($class->classid!=$newclassid) {
							$class->classid = $newclassid;
							if ($class->id!=0) {
								$DB->update_record('autoattend_classifies', $class);
							}
							else {
								$class->id = $DB->insert_record('autoattend_classifies', $class);
							}
							$cnt++;
						}
					}
				}
			}
			break;		// only use first element
		}
		if ($cnt>0) {
			autoattend_update_grades($course->id);
		}
	}
	//
	$name_pattern = autoattend_get_namepattern($course->id);
	$classes = autoattend_get_session_classes($course->id);
	$url_options = '?course='.$courseid.'&amp;sort='.$sort;
	include('html/class_division.html');
} 
//
else {
	echo $OUTPUT->heading(get_string('nothingtodisplay'));
}
 
echo $OUTPUT->footer($course);

