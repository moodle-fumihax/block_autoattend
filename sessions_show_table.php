<?php

//////////////////////////////////////////////////////////////////////////////////////////
//
// function
//
function autoattend_sessions_show_table($sessions, $classes, $courseid, $classid)
{
	global $context, $wwwBlock, $isteacher, $DB, $TIME_OFFSET;
    
    $use_summertime = autoattend_use_summertime($courseid);

	$table = new html_table();
	//
	$table->head [] = '#';
	$table->align[] = 'center';
	$table->size [] = '20px';
	$table->wrap [] = 'nowrap';

	$table->head [] = get_string('date');
	$table->align[] = 'center';
	$table->size [] = '80px';
	$table->wrap [] = 'nowrap';

	$table->head [] = get_string('starttime','block_autoattend');
	$table->align[] = 'center';
	$table->size [] = '60px';
	$table->wrap [] = 'nowrap';

	$table->head [] = get_string('endtime','block_autoattend');
	$table->align[] = 'center';
	$table->size [] = '60px';
	$table->wrap [] = 'nowrap';

	$table->head [] = get_string('classname','block_autoattend');
	$table->align[] = 'center';
	$table->size [] = '60px';
	$table->wrap [] = 'nowrap';

	$table->head [] = get_string('description','block_autoattend');
	$table->align[] = 'left';
	$table->size [] = '80px';
	$table->wrap [] = 'nowrap';

	$table->head [] = get_string('keyword','block_autoattend');
	$table->align[] = 'center';
	$table->size [] = '70px';
	$table->wrap [] = 'nowrap';

	$table->head [] = get_string('callmethod','block_autoattend');
	$table->align[] = 'center';
	$table->size [] = '40px';
	$table->wrap [] = 'nowrap';

	$table->head [] = get_string('callstate','block_autoattend');
	$table->align[] = 'center';
	$table->size [] = '80px';
	$table->wrap [] = 'nowrap';

	$table->head [] = get_string('action');
	$table->align[] = 'center';
	$table->size [] = '60px';
	$table->wrap [] = 'nowrap';

	//
	$totalmember[0] = autoattend_count_attend_students($courseid, 0, $context);
	foreach($classes as $class) { 
		$totalmember[$class->id] = autoattend_count_attend_students($courseid, $class->id, $context);
	}

	//
	$i = 0;
	foreach($sessions as $sessdata) { 
        //  
        $summertime_mark = '&nbsp;';
        $summertime = autoattend_get_summertime($sessdata->id, $use_summertime)*ONE_HOUR_TIME;
        if ($summertime!=0) $summertime_mark = '*';

		$ttlcount = $totalmember[$sessdata->classid];
		$attcount = autoattend_count_class_students($sessdata, $courseid, $context, "status<>'Y' AND status<>'X'");

        $number = $i + 1;
        $number.= $summertime_mark;
		$table->data[$i][] = $number;
		//$table->data[$i][] = strftime(get_string('strftimedmyw',   'block_autoattend'), $sessdata->sessdate  + $summertime + $TIME_OFFSET);
		$table->data[$i][] = strftime(get_string('strftimedmyw',   'block_autoattend'), $sessdata->starttime + $summertime + $TIME_OFFSET);
		$table->data[$i][] = strftime(get_string('strftimehourmin','block_autoattend'), $sessdata->starttime + $summertime + $TIME_OFFSET);
		$table->data[$i][] = strftime(get_string('strftimehourmin','block_autoattend'), $sessdata->endtime   + $summertime + $TIME_OFFSET);
		$table->data[$i][] = autoattend_get_session_classname($sessdata->classid);
		$table->data[$i][] = $sessdata->description ? $sessdata->description: get_string('nodescription', 'block_autoattend');
		$table->data[$i][] = $sessdata->attendkey ? $sessdata->attendkey: get_string('novalue', 'block_autoattend');
		$table->data[$i][] = get_string($sessdata->method.'methodfull', 'block_autoattend');
		$table->data[$i][] = get_string($sessdata->state.'statefull', 'block_autoattend').'&nbsp;('.$attcount.'/'.$ttlcount.')';

		$title  = get_string('takeattendance','block_autoattend');
		$actbtn = '<a title="'.$title.'" href="'.$wwwBlock.'/updateAttendance.php?course='.$courseid.'&amp;attsid='.$sessdata->id.'&amp;class='.$classid.'">';

		if($sessdata->state=='C') {
			$actbtn.= '<img src="pix/t/go.gif" alt="'.$title.'" /></a>&nbsp;';
		}
		else if($sessdata->state=='O') {
			$actbtn.= '<img src="pix/b.gif" alt="'.$title.'" /></a>&nbsp;';
		}
		else {
			$actbtn.= '<img src="pix/t/stop.gif" alt="'.$title.'" /></a>&nbsp;';
		}
		if ($isteacher) {
			$title = get_string('editsession','block_autoattend');
			$actbtn.= '<a title="'.$title.'" href="'.$wwwBlock.'/updateSession.php?course='.$courseid.'&amp;attsid='.$sessdata->id.'">';
			$actbtn.= '<img src="pix/t/edit.gif" alt="'.$title.'" /></a>&nbsp;';
			$actbtn.= '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
			$actbtn.= '<input type="checkbox" name="delete'.$sessdata->id.'" value="1" />';
		}
		$table->data[$i][] = $actbtn;

		$i++; 
	}

	echo html_writer::table($table);

	return;
}

