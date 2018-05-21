<?php
ini_set('display_errors', 1);
define('CURRENT_LANG', 'en');

if (stripos($_SERVER['SERVER_NAME'], 'inthemake.bz')!==false)
    include 'config.make.php';
else
    include 'config.if.php';

include 'make/event_handlers.php';
include 'make/classes.php';
include 'make/functions.php';

include 'make/mod_crmpanel.php';
include 'make/mod_crmtask.php';
include 'make/mod_crmtask_info.php';
include 'make/mod_project.php';
include 'make/mod_project_task.php';
include 'make/mod_deal.php';

// test for meetings, this is not included on the project
include 'make/mod_meeting.php';

//$path = "..";
$phpFolder = dirname(__FILE__);
// autoload vendors
if (file_exists($phpFolder.'/../../vendor/autoload.php')) {
    require_once($phpFolder.'/../../vendor/autoload.php');
}