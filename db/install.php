<?php

function xmldb_block_autoattend_install() 
{
    global $DB;

	$rec = new stdClass();

	$rec->id = 0;
	$rec->courseid = 0;

    $rec->status  = 'P';
	$rec->grade   =  2;
	$rec->display =  1;
	$rec->seqnum  =  1;
	$DB->insert_record('autoattend_settings', $rec);

    $rec->status  = 'X';
	$rec->grade   =  0;
	$rec->display =  1;
	$rec->seqnum  =  2;
	$DB->insert_record('autoattend_settings', $rec);

    $rec->status  = 'L';
	$rec->grade   =  1;
	$rec->display =  1;
	$rec->seqnum  =  3;
	$DB->insert_record('autoattend_settings', $rec);

    $rec->status  = 'E';
	$rec->grade   =  1;
	$rec->display =  1;
	$rec->seqnum  =  4;
	$DB->insert_record('autoattend_settings', $rec);

    $rec->status  = 'G';
	$rec->grade   =  0;
	$rec->display =  0;
	$rec->seqnum  =  5;
	$DB->insert_record('autoattend_settings', $rec);

    $rec->status  = 'S';
	$rec->grade   =  0;
	$rec->display =  0;
	$rec->seqnum  =  6;
	$DB->insert_record('autoattend_settings', $rec);

    $rec->status  = 'Y';
	$rec->grade   =  0;
	$rec->display =  1;
	$rec->seqnum  =  7;
	$DB->insert_record('autoattend_settings', $rec);

	//
	$DB->set_field('block', 'visible', 1, array('name'=>'autoattend'));
}

