<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once("import.class.php");

global $USER_FIELD_MANAGER;
$pagen = intval($_REQUEST['page']);
$filePath = $_SERVER['DOCUMENT_ROOT'].'/crm/import/importfile/csv/dealactual.csv';
$importFile = new importFile;

$isExporting = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'import') ? true : false;

if($isExporting)
	$recperpage = 1000;

$arData = $importFile->load($filePath, $pagen, $recperpage);
echo $importFile->showhtml($arData['header'], $arData['rowdata'], $isExporting);

define('PROJECT', 0);
define('DEAL', 1);
define('ACQUISITIE_KOSTEN', 2);

// import deal actual
foreach($arData['rowdata'] as $key => $data) {

	if($isExporting) {
		$arFields = array(
			'DEAL_ID' => $data[DEAL],
			'TASK_ID' => 0,
			'ACQUISITION_COSTS' => $data[ACQUISITIE_KOSTEN],
			'COST_IF' => 0,
			'THIRD_PARTY_COST' => 0,
			'COST_LIFT' => 0,
			'TOTAL_COSTS' => 0,
			'REVENUE' => 0,
			'RESCUE' => 0,
			'BILLED' => 0,
			'PAID' => 0,
		);

		$mFields = $importFile->consQueryField($arFields);

		echo '<pre>';
			print_r($arFields);
		echo '</pre>';
		//$DB->Query("INSERT INTO `m_project_task_actual` SET ".implode(",", $mFields));
		//dealActualID = $DB->LastID();
		//$importFile->doSaveLog('deal-actual', intval($dealActualID)." - ".print_r($mFields, true));
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
