<?php 
//
// Automatic Attendance Block 
//
// Modified Attendance Block by Fumi.Iseki	2010/03/28
//											2013/04/17
//


require_once($CFG->dirroot.'/blocks/autoattend/locallib.php');


class block_autoattend extends block_base 
{
	function init() 
	{
		global $CFG;

		if (empty($plugin)) $plugin = new stdClass();
		include($CFG->dirroot.'/blocks/autoattend/version.php');
		//
		$this->title   = get_string('pluginname', 'block_autoattend');
		$this->version = $plugin->version;
		$this->release = $plugin->release;
	}


	function get_content() 
	{
		global $CFG, $USER, $DB;

		if ($this->content != NULL) {
			return $this->content;
		}
		
		$courseid = optional_param('course', 0, PARAM_INTEGER);
		$classid  = optional_param('class',  0, PARAM_INTEGER);
		if ($courseid==0) $courseid = $this->page->course->id;
		if ($courseid!=0) {
			$course = $DB->get_record('course', array('id'=>$courseid));
			if (!$course) {
				print_error('courseidwrong', 'block_autoattend');
			}
		} 
		else {
			return '';
		}

		$this->content = new stdClass(); 
		$this->content->items  = array();
		$this->content->icons  = array(); 
		$this->content->footer = '';
		$this->content->text   = '';

		//
		$context   = jbxl_get_course_context($course->id);
		$isstudent = false;
		$isassist  = false;
		$isteacher = jbxl_is_teacher($USER->id, $context);

		if (!$isteacher) {
			$isassist = jbxl_is_assistant($USER->id, $context);
			if (!$isassist) $isstudent = jbxl_is_student($USER->id, $context);
		}

		if ($isteacher or $isassist) {	// Teacher
			//
			$this->content->text = '<a href="'.$CFG->wwwroot.'/blocks/autoattend/index.php?course='.$course->id.'&amp;class='.$classid.
										'&amp;from=home">'.get_string('takemanualattend','block_autoattend').'</a><br />';
			$this->content->text.= '<a href="'.$CFG->wwwroot.'/blocks/autoattend/class_division.php?course='.$course->id.'&amp;class='.$classid.
										'&amp;from=home">'.get_string('students_list','block_autoattend').'</a><br />';
			$this->content->text.= '<a href="'.$CFG->wwwroot.'/blocks/autoattend/report.php?course='.$course->id.'&amp;class='.$classid.
										'&amp;from=home&amp;view=months&amp;refresh=1">'.get_string('report','block_autoattend').'</a><br />';
			if ($isteacher) {
				//$this->content->text.= '<a href="'.$CFG->wwwroot.'/blocks/autoattend/class_division.php?course='.$course->id.'">'.
				//								get_string('class_division','block_autoattend').'</a><br />';
				//
				if ($this->version < 2014051100) {
					$this->content->text.= '<a href="'.$CFG->wwwroot.'/blocks/autoattend/repairDB.php?course='.$course->id.'">'.
												get_string('repairdb','block_autoattend').'</a><br />';
				}
			}
		}	
		//
		elseif ($isstudent) {
			//
			$user_summary = autoattend_get_user_summary($USER->id, $course->id);

			if(!$user_summary) {		//autoattend not generated yet
				$this->content->text.= get_string('attendnotstarted','block_autoattend');
			}
			else {						//autoattend taken
				$classinfo = autoattend_get_user_class($USER->id, $course->id);
				$this->content->text.= get_string('classname','block_autoattend').': '.$classinfo->name.'<br />';
				//
				if ($classinfo->classid>=0) {	// !出欠から除外
					$absence  = 0;
					$settings = autoattend_get_grade_settings($course->id);
					foreach($settings as $set) {
						//if ($set->status=='X' or $set->status=='Y') {
						if ($set->status=='X') {
							$absence+= $user_summary[$set->status];
						}
						else if ($set->status!='Y' and $set->display) {
							$this->content->text.= $settings[$set->status]->description.': '.$user_summary[$set->status].'<br />';
						}
					}
					$this->content->text .= $settings['X']->description.': '.$absence.'<br />';

					$npercent = $user_summary['npercent'];
					$gpercent = $user_summary['gpercent'];
					$grade    = $user_summary['grade'];
					$pgrade   = $user_summary['pgrade'];
					$mxgrade  = $user_summary['maxgrade'];
					$this->content->text.= get_string('attendnpercent','block_autoattend').': '.$npercent.' %<br />';
					//$this->content->text.= get_string('attendgrade','block_autoattend').": $grade / $mxgrade<br />";
					$this->content->text.= get_string('attendgrade','block_autoattend').": $grade / $pgrade<br />";
					$this->content->text.= get_string('attendgpercent','block_autoattend').': '.$gpercent.' %<br />';
					$this->content->text.= '<a href="'.$CFG->wwwroot.'/blocks/autoattend/index.php?course='.$course->id.'&amp;class='.$classid.'">';
					$this->content->text.= get_string('indetail','block_autoattend').'</a>';
				}
			}
		}
		//
		else {
			$this->content->text = '';	// Guest
		} 

		$this->content->footer = '<hr />';
		if (autoattend_disp_feedback($course->id)) {
			if ($isteacher or $isassist) {
				$this->content->footer.= '<a href="https://el.mml.tuis.ac.jp/moodle/mod/feedback/view.php?id=528" target="_blank">';
			}
			elseif ($isstudent) {
				$this->content->footer.= '<a href="https://el.mml.tuis.ac.jp/moodle/mod/feedback/view.php?id=529" target="_blank">';
			}
			$this->content->footer.= get_string('feedback','block_autoattend').'</a><br />';
		}
		$this->content->footer.= '<a href="'.get_string('wiki_url','block_autoattend').'" target="_blank"><i>Autoattend '.$this->release.'</i></a>';
		return $this->content;
	}



	// setting of instance block. need config_instance.html
	function instance_allow_config()
	{
		return false;
	}



	// setting block. need settings.php
	function has_config()
	{
		return true;
	}


	function cron()
	{	
		global $CFG, $DB;

		$qry = "SELECT DISTINCT courseid FROM {$CFG->prefix}autoattend_sessions";
		$courseids = $DB->get_records_sql($qry);

		if ($courseids) {
			foreach ($courseids as $id) {
				$ret = autoattend_update_sessions($id->courseid);
			}
		}

		return true;
	}
}
