<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once("import.class.php");

global $USER_FIELD_MANAGER;
$pagen = intval($_REQUEST['page']);
$filePath = $_SERVER['DOCUMENT_ROOT'].'/crm/import/importfile/csv/taskdiff.csv';
$importFile = new importFile;

$isExporting = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'import') ? true : false;

if($isExporting)
	$recperpage = 1000;

$arData = $importFile->load($filePath, $pagen, $recperpage);
echo $importFile->showhtml($arData['header'], $arData['rowdata'], $isExporting);

define('PROJECT', 0);
define('DEAL', 1);
define('TASK_ID', 2);
define('KOSTEN_UREN_IF', 3);
define('KOSTEN_DERDEN', 4);
define('KOSTEN_LIFT', 5);
define('TOTALE_KOSTEN', 6);
define('INKOMSTEN', 7);
define('RENDEMENT', 8);

// import task diff
foreach($arData['rowdata'] as $key => $data) {

	if($isExporting) {
		/*$arFields = array(
			'DEAL_ID' => $data[DEAL],
			'TASK_ID' => $data[TASK_ID],
			'ACQUISITION_COSTS' => 0,
			'COST_IF' => $data[KOSTEN_UREN_IF],
			'THIRD_PARTY_COST' => 0,
			'COST_LIFT' => 0,
			'TOTAL_COSTS' >= $data[TOTALE_KOSTEN],
			'REVENUE' => 0,
			'RESCUE' => $data[RENDEMENT],
			'BILLED' => 0,
			'PAID' => 0,
		);*/

		$arFields = array(
			'DEAL_ID' => $data[DEAL],
			'TASK_ID' => $data[TASK_ID],
			'ACQUISITION_COSTS' => 0,
			'COST_IF' => $data[KOSTEN_UREN_IF],
			'THIRD_PARTY_COST' => $data[KOSTEN_DERDEN],
			'COST_LIFT' => $data[KOSTEN_LIFT],
			'TOTAL_COSTS' => $data[TOTALE_KOSTEN],
			'REVENUE' => $data[INKOMSTEN],
			'RESCUE' => $data[RENDEMENT],
			'BILLED' => 0,
			'PAID' => 0,
		);

		$mFields = $importFile->consQueryField($arFields);

		echo '<pre>';
			print_r($arFields);
		echo '</pre>';
		//$DB->Query("INSERT INTO `m_project_task_diff` SET ".implode(",", $mFields));
		//taskDiffID = $DB->LastID();
		//$importFile->doSaveLog('tasks-diff', intval($taskDiffID)." - ".print_r($mFields, true));
	}
}

if($isExporting) {

	/*if(!empty($arData['rowdata'])) {
		++$pagen;
		$export_page = 'http://'.$_SERVER['HTTP_HOST'] . $APPLICATION->GetCurPageParam("page=".$pagen, array("page"));
		echo '<script> setTimeout(function(){ parent.window.location = "'.$export_page.'"; }, 5000);</script>';
	}
	else
		echo 'done';*/
}
?>
