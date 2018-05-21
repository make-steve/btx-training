<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once("import.class.php");

global $USER_FIELD_MANAGER;
$pagen = intval($_REQUEST['page']);
$filePath = $_SERVER['DOCUMENT_ROOT'].'/crm/import/importfile/csv/company.csv';
$importFile = new importFile;

$isExporting = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'import') ? true : false;

if($isExporting)
	$recperpage = 1000;

$arData = $importFile->load($filePath, $pagen, $recperpage);
echo $importFile->showhtml($arData['header'], $arData['rowdata'], $isExporting);

// import company custom fields

define('ID', 0);
define('BEDRIJFSNAAM', 1);
define('VESTIGING', 2);
define('KLANTTYPE', 3);
define('KLANTVERANTWOORDELIJKE', 4);
define('KLANTWAARDERING', 5);
define('CBS_SECTOR', 6);
define('GEVONDEN', 7);
define('BEZOEKADRES', 8);
define('POSTCODE', 9);
define('PLAATS', 10);
define('FACTUURBEDRIJF', 11);
define('FACTUURADRES', 12);
define('TAV', 13);
define('CLIENT_ID_EXACT', 14);

define('COM_GEVONDEN', 'UF_CRM_1470063180');
define('COM_KLANTWAARDERING', 'UF_CRM_1474961849');
define('COM_VESTIGING', 'UF_CRM_1475157926');
define('COM_BEZOEKADRES', 'UF_CRM_1511437015');
define('COM_POSTCODE', 'UF_CRM_1511437033');
define('COM_PLAATS', 'UF_CRM_1511437053');
define('COM_FACTUURBEDRIJF', 'UF_CRM_1511437080');
define('COM_FACTUURADRES', 'UF_CRM_1511437095');
define('COM_TAV', 'UF_CRM_1511437111');
define('COM_CLIENT_ID_EXACT', 'UF_CRM_1511437130');

foreach($arData['rowdata'] as $key => $data) {

	if($isExporting) {
		$ID = intval($data[ID]);
		$arFields = array(
		    COM_GEVONDEN => $data[GEVONDEN],
		    COM_KLANTWAARDERING => $data[KLANTWAARDERING],
		    COM_VESTIGING => $data[VESTIGING],
		    COM_BEZOEKADRES => $data[BEZOEKADRES],
		    COM_POSTCODE => $data[POSTCODE],
		    COM_PLAATS => $data[PLAATS],
		    COM_FACTUURBEDRIJF => $data[FACTUURBEDRIJF],
		    COM_FACTUURADRES => $data[FACTUURADRES],
		    COM_TAV => $data[TAV],
		    COM_CLIENT_ID_EXACT => $data[CLIENT_ID_EXACT],
		);

		/*echo '<pre>';
			print_r($arFields);
		echo '</pre>';*/

		if(intval($ID) > 0) {
			//$GLOBALS['USER_FIELD_MANAGER']->Update(self::$sUFEntityID, $ID, $arFields);
			/*$GLOBALS['USER_FIELD_MANAGER']->Update('CRM_COMPANY', $ID, $arFields);
			$importFile->doSaveLog('company', intval($ID)." - ".print_r($arFields, true));*/
		}
	}
}

if($isExporting) {

	if(!empty($arData['rowdata'])) {
		++$pagen;
		$export_page = 'http://'.$_SERVER['HTTP_HOST'] . $APPLICATION->GetCurPageParam("page=".$pagen, array("page"));
		echo '<script> setTimeout(function(){ parent.window.location = "'.$export_page.'"; }, 5000);</script>';
	}
	else
		echo '<br/> Done';
}
?>
