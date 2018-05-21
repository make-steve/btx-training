<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once("import.class.php");

global $USER_FIELD_MANAGER;
$pagen = intval($_REQUEST['page']);
$filePath = $_SERVER['DOCUMENT_ROOT'].'/crm/import/importfile/csv/hours.csv';
$importFile = new importFile;

$isExporting = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'import') ? true : false;

if($isExporting)
	$recperpage = 1000;

$arData = $importFile->load($filePath, $pagen, $recperpage);
echo $importFile->showhtml($arData['header'], $arData['rowdata'], $isExporting);

// import hours
define('USER_ID', 0);
define('PROJECT_ID', 1);
define('DEAL_ID', 2);
define('TASK_ID', 3);
define('HOURS_LOG', 4);
define('COMMENTS', 5);
define('USER_HOURLY_RATE', 6);
define('DATE', 7);

foreach($arData['rowdata'] as $key => $data) {

	if($isExporting) {
		$_totalRate = doubleVal($data[HOURS_LOG]) * doubleVal($data[USER_HOURLY_RATE]);
		$totalRate = number_format($_totalRate, 2, '.', '');
		$arFields = array(
			'DATE_CREATED' => $data[DATE],
			'DATE_LOG' => $data[DATE],
			'DATE_MODIFIED' => '',
			'USER_ID' => $data[USER_ID],
			'MODIFIED_BY' => '',
			'PROJECT_ID' => $data[PROJECT_ID],
			'DEAL_ID' => $data[DEAL_ID],
			'TASK_ID' => $data[TASK_ID],
			'XML_ID' => $data[PROJECT_ID].".".$data[TASK_ID],
			'HOURS_LOG' => $data[HOURS_LOG],
			'COMMENTS' => $data[COMMENTS],
			'USER_HOURLY_RATE' => $data[USER_HOURLY_RATE],
			'TOTAL_RATE' => $totalRate,
			'TYPE' => ($data[TASK_ID] == 0 || $data[TASK_ID] = '') ? 'ACQUISITION_COSTS' : 'COST_IF',
		);

		$mHoursLog = $importFile->consQueryField($arFields);

		echo '<pre>';
			print_r($arFields);
		echo '</pre>';
		//$DB->Query("INSERT INTO `m_timetable_log` SET ".implode(",", $mHoursLog));
		//$hoursId = $DB->->LastID();
		//$importFile->doSaveLog('hours', intval($hoursId)." - ".print_r($mHoursLog, true));
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
