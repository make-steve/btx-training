<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once("import.class.php");

global $USER_FIELD_MANAGER;
$pagen = intval($_REQUEST['page']);
$filePath = $_SERVER['DOCUMENT_ROOT'].'/crm/import/importfile/csv/project.csv';
$importFile = new importFile;

$isExporting = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'import') ? true : false;

if($isExporting)
	$recperpage = 1000;

$arData = $importFile->load($filePath, $pagen, $recperpage);
echo $importFile->showhtml($arData['header'], $arData['rowdata'], $isExporting);

// import m_project
define('ID', 0);
define('COMPANY', 1);
define('CONTACT', 2);
define('TITLE', 3);
define('CLIENT_NAME', 4);
define('TECHNOLOGY', 5);
define('PRODUCT_GROUP', 6);
define('COMMENTS', 7);

define('POST_ADDRESS', 'UF_CRM_1511437982');
define('POST_CODE', 'UF_CRM_1511437995');
define('CITY', 'UF_CRM_1511438007');

define('POST_ADDRESS', 'UF_CRM_1511437982');
define('POST_CODE', 'UF_CRM_1511437995');
define('CITY', 'UF_CRM_1511438007');

define('COM_ADDRESS', 'UF_CRM_1511437015');
define('COM_POSTCODE', 'UF_CRM_1511437033');
define('COM_CITY', 'UF_CRM_1511437053');
define('COM_FACTUUR_BEDRIJF', 'UF_CRM_1511437080');
define('COM_FACTUUR_ADDRES', 'UF_CRM_1511437095');
define('COM_TAV', 'UF_CRM_1511437111');

$technology = $importFile->getDropdownUF('UF_CRM_1493724413', 'CRM_DEAL');
$productgroup = $importFile->getStatus('DEAL_TYPE');

foreach($arData['rowdata'] as $key => $data) {

	//if($key == 0) continue; // skip title

	if(strpos($data[ID],".") === false){

		$arMProject[] = array(
			'ID' => intval($data[ID]),
			'ACTIVE' => "Y",
			'DATE_CREATE' => date('Y-m-d H:i:s'),
			'CREATED_BY_ID' => 1,
			'COMPANY_ID' => $data[COMPANY],
			'CONTACT_ID' => $data[CONTACT],
			'TITLE' => $data[TITLE],
			'CLIENT_NAME' => $data[CLIENT_NAME],
			'TECHNOLOGY' => $technology[$data[TECHNOLOGY]],
			'PRODUCT_GROUP' => $productgroup[$data[PRODUCT_GROUP]],
			'COMMENTS' => $data[COMMENTS],
		);
	}
}

foreach($arMProject as $project) {

	if($isExporting) {
		$arCompany = array();
		$arContact = array();
		$mProjectField = $importFile->consQueryField($project);

		// contact
		$mProjectContactField = array();
		$_arContact = $importFile->getContactByID(intval($project['CONTACT_ID']));
		if(!empty($_arContact))	{

			$arContact = array(
				'PROJECT_ID' => intval($project['ID']),
	            'CONTACT_ID' => intval($_arContact['CONTACT_ID']),
	            'SALUTATION' => $_arContact['SALUTATION'],
	            'LAST_NAME' => $_arContact['LAST_NAME'],
	            'MIDDLE_NAME' => $_arContact['MIDDLE_NAME'],
	            'NAME' =>  $_arContact['NAME'],
	            'POST_ADD' => $_arContact['UF'][POST_ADDRESS]["VALUES"][0]["VALUE_NAME"],
	            'POSTCODE' => $_arContact['UF'][POST_CODE]["VALUES"][0]["VALUE_NAME"],
	            'CITY' => $_arContact['UF'][CITY]["VALUES"][0]["VALUE_NAME"],
	            'TELEPHONE' => $_arContact['TELEPHONE'],
	            'EMAIL' => $_arContact['EMAIL'],
			);

			$mProjectContactField = $importFile->consQueryField($arContact);
		}

		// Company
		$mProjectCompanyField = array();
		$_arCompany = $importFile->getCompanyByID(intval($project['COMPANY_ID']));
		if(!empty($_arCompany))	{

			$arCompany = array(
				'PROJECT_ID' => intval($project['ID']),
	            'COMPANY_ID' => intval($_arCompany['COMPANY_ID']),
	            'COMPANY_NAME' => $_arCompany['COMPANY_NAME'],
	            'ADDRESS' => $_arCompany['UF'][COM_ADDRESS]["VALUES"][0]["VALUE_NAME"],
	            'POSTCODE' => $_arCompany['UF'][COM_POSTCODE]["VALUES"][0]["VALUE_NAME"],
	            'CITY' => $_arCompany['UF'][COM_CITY]["VALUES"][0]["VALUE_NAME"],
	            'ADDRESS_INVOICE' => $_arCompany['UF'][COM_FACTUUR_BEDRIJF]["VALUES"][0]["VALUE_NAME"],
	            'ADDRESS_BILLING' => $_arCompany['UF'][COM_FACTUUR_ADDRES]["VALUES"][0]["VALUE_NAME"],
	            'TAV' => $_arCompany['UF'][COM_TAV]["VALUES"][0]["VALUE_NAME"],
			);

			$mProjectCompanyField = $importFile->consQueryField($arCompany);
		}

		/*echo '<pre>';
			print_r($mProjectField);
			print_r($mProjectCompanyField);
			print_r($mProjectContactField);
		echo '</pre>';*/
		if(!empty($mProjectField))
			$DB->Query("INSERT INTO `m_project` SET ".implode(",", $mProjectField));

		if(!empty($mProjectContactField))
			$DB->Query("INSERT INTO `m_project_contact` SET ".implode(",", $mProjectContactField));

		if(!empty($mProjectCompanyField))
			$DB->Query("INSERT INTO `m_project_company` SET ".implode(",", $mProjectCompanyField));

		$importFile->doSaveLog('project', intval($project['ID'])." - ".print_r($mProjectField, true));
	}
}
if($isExporting) {

	if(!empty($arData['rowdata'])) {
		++$pagen;
		$export_page = 'http://'.$_SERVER['HTTP_HOST'] . $APPLICATION->GetCurPageParam("page=".$pagen, array("page"));
		echo '<script> setTimeout(function(){ parent.window.location = "'.$export_page.'"; }, 5000);</script>';
	}
	else
		echo 'done';
}
?>
