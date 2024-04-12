<?php

defined('MOODLE_INTERNAL') || die();


$plugin->requires  = 2011120500;          // Moodle 2.2
$plugin->component = 'block_autoattend';  // Full name of the plugin (used for diagnostics)
$plugin->cron      = 0;
$plugin->maturity  = MATURITY_STABLE;

$plugin->release   = '2.7.3';

$plugin->version   = 2024032301;    // v2.7.3 get_string('grade') -> get_string('attendgrade', 'block_autoattend');
//$plugin->version = 2024011102;    // v2.7.2 contorol show attendance info. in the block
//$plugin->version = 2023122801;    // v2.7.0 add info to block
//$plugin->version = 2023112800;    // v2.6.5 strong to div
//$plugin->version = 2023103100;    // v2.6.4 correct rate of attendance
//$plugin->version = 2023032300;    // v2.6.3 add local host name
//$plugin->version = 2022091900;    // v2.6.2 fix timezonedef.php bug
//$plugin->version = 2022051300;    // v2.6.1 fix bugs
//$plugin->version = 2019082202;    // v2.6.0 for 3.7.1
//$plugin->version = 2019081802;    // v2.5.5 minor change
//$plugin->version = 2018101200;    // v2.5.4 centering of tabs.php
//$plugin->version = 2016071800;    // v2.5.3 db/access.php myaddinstance
//$plugin->version = 2016051900;    // v2.5.2 Bug Fix of e-mail for students
//$plugin->version = 2016031500;    // v2.5.1
