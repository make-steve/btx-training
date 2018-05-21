<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once("import.class.php");

global $USER_FIELD_MANAGER;
$pagen = intval($_REQUEST['page']);
$filePath = $_SERVER['DOCUMENT_ROOT'].'/crm/import/importfile/csv/tasks.csv';
$importFile = new importFile;
$mTask = new CTasks();

$isExporting = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'import') ? true : false;

if($isExporting)
	$recperpage = 1000;

$arData = $importFile->load($filePath, $pagen, $recperpage);
echo $importFile->showhtml($arData['header'], $arData['rowdata'], $isExporting);

// import b_tasks

define('DEAL_ID', 0);
define('PROJECT_ID', 1);
define('LOCKED', 2);
define('STATUS', 3);
define('WBSO', 4);
define('TYPE_TAAK', 5);
define('ID', 6);
define('TITLE', 7);
define('DESCRIPTION', 8);
define('ACQUISITEUR', 9);
define('PROJECTLEIDER', 10);
define('PARTICIPANTS', 11);
define('START_DATE_TIME', 12);
define('TASK_DURATION', 13);
define('FINISH_DATE_TIME', 14);
define('INKOMSTEN', 15);
define('KOSTEN_DERDEN', 16);
define('KOSTEN_LIFT', 17);
define('KOSTEN_UREN_IF', 18);
define('TOTALE_KOSTEN', 19);
define('KOSTEN_UREN_BREAKDOWN', 20);
define('RENDEMENT', 21);
define('TECHNOLOGIE', 22);
define('PRODUCTGROEP', 23);
define('FACTUURINFORMATIE', 24);
define('REFERENTIE_KLANT', 25);
define('CRM_COMPANY', 26);

define('TASK_INKOMSTEN', 'UF_AUTO_276127427586');
define('TASK_KOSTEN_DERDEN', 'UF_AUTO_834260910111');
define('TASK_KOSTEN_LIFT', 'UF_AUTO_347562553598');
define('TASK_KOSTEN_UREN_IF', 'UF_AUTO_775103888970');
//define('TASK_KOSTEN_UREN_BREAKDOWN', 16);
define('TASK_RENDEMENT', 'UF_AUTO_197833829978');
define('TASK_TECHNOLOGIE', 'UF_AUTO_371316967807');
define('TASK_PRODUCTGROEP', 'UF_AUTO_371316967808');
define('TASK_FACTUURINFORMATIE', 'UF_AUTO_371316967809');
define('TASK_REFERENTIE_KLANT', 'UF_AUTO_192582141226');
define('TASK_LOCKED', 'UF_AUTO_713286985141');

define('TASK_WBSO', 'UF_AUTO_234502502541');
define('TASK_TYPE_TAAK', 'UF_AUTO_741430793433');
define('TASK_CRM_COMPANY', 'UF_AUTO_192582141227');

$taskTechnology = $importFile->getDropdownUF(TASK_TECHNOLOGIE, 'TASKS_TASK');
$tasProductGroup = $importFile->getDropdownUF(TASK_PRODUCTGROEP, 'TASKS_TASK');

foreach($arData['rowdata'] as $key => $data) {

	if($isExporting) {
		$task_arFields['TITLE'] = $data[TITLE];
		$task_arFields['XML_ID'] = $data[PROJECT_ID].".".$data[ID];
		$task_arFields['STATUS'] = (strtolower($data[STATUS]) == 'finished') ? 5 : 2;
		$task_arFields['SE_RESPONSIBLE'] = intval($data[ACQUISITEUR]);
		$task_arFields['SE_ORIGINATOR'] = explode(",", $data[PROJECTLEIDER]);
		$task_arFields['SE_ACCOMPLICE'] = explode(",", $data[PARTICIPANTS]);
		$task_arFields['TAGS'] = 'crm';
	    $task_arFields['PRIORITY'] = '1';
	    $task_arFields['GROUP_ID'] = '0';
	    $task_arFields['TASK_CONTROL'] = 'N';
	    $task_arFields['UF_TASK_WEBDAV_FILES'] = array();
	    $task_arFields['ALLOW_TIME_TRACKING'] = 'N';
	    $task_arFields['ALLOW_CHANGE_DEADLINE'] = 'Y';
	    $task_arFields['CREATED_BY'] = 1;

	    $owner_prefix = 'D_';
	    //$primaryDeal = $importFile->getProjectPrimDeal($data[PROJECT_ID]);
		$task_arFields['UF_CRM_TASK'] = array($owner_prefix.$data[DEAL_ID]);

		// to bbcode
		$messagetobbcode = CCrmMailTemplate::ConvertHtmlToBbCode($data[DESCRIPTION]);
		$task_arFields['DESCRIPTION'] = $messagetobbcode;
		$task_arFields['DESCRIPTION_IN_BBCODE'] = 'Y';

		// Dates
		$date1 = new DateTime($data[START_DATE_TIME]);
		$date2 = new DateTime($data[FINISH_DATE_TIME]);

		$diff = $date2->diff($date1);
		$hours = $diff->h;
		$hours = $hours + ($diff->days*24);

		$task_arFields['START_DATE_PLAN'] = $data[START_DATE_TIME];
		$task_arFields['DURATION'] = $hours;
		$task_arFields['DURATION_TYPE'] = 'hours';
		$task_arFields['END_DATE_PLAN'] = $data[FINISH_DATE_TIME];

		// custom fields
		$task_arFields[TASK_INKOMSTEN] = $data[INKOMSTEN];
		$task_arFields[TASK_KOSTEN_DERDEN] = $data[KOSTEN_DERDEN];
		$task_arFields[TASK_KOSTEN_LIFT] = $data[KOSTEN_LIFT];
		$task_arFields[TASK_KOSTEN_UREN_IF] = $data[KOSTEN_UREN_IF];
		$task_arFields[TASK_RENDEMENT] = $data[RENDEMENT];

		$task_arFields[TASK_TECHNOLOGIE] = $taskTechnology[$data[TECHNOLOGIE]];
		$task_arFields[TASK_PRODUCTGROEP] = $tasProductGroup[$data[PRODUCTGROEP]];
		$task_arFields[TASK_FACTUURINFORMATIE] = $data[FACTUURINFORMATIE];
		$task_arFields[TASK_REFERENTIE_KLANT] = $data[REFERENTIE_KLANT];
		$task_arFields[TASK_LOCKED] = (strtolower($data[LOCKED] == 'y')) ? 1 : 0;

		$task_arFields[TASK_WBSO] = $data[WBSO];
		$task_arFields[TASK_TYPE_TAAK] = $data[TYPE_TAAK];
		$task_arFields[TASK_CRM_COMPANY] = $data[CRM_COMPANY];

		echo '<pre>';
			print_r($task_arFields);
		echo '</pre>';
		//$rc = $mTask->Add($task_arFields, $arParams);
		//$importFile->doSaveLog('tasks', intval($rc)." - ".print_r($task_arFields, true));
	}
}

if($isExporting) {

	/*if(!empty($arData['rowdata'])) {
		++$pagen;
		$export_page = 'http://'.$_SERVER['HTTP_HOST'] . $APPLICATION->GetCurPageParam("page=".$pagen, array("page"));
		echo '<script> setTimeout(function(){ parent.window.location = "'.$export_page.'"; }, 5000);</script>';
	}
	else
		echo '<br/> Done';*/
}
?>
