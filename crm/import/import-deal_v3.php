<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once("import.class.php");

global $USER_FIELD_MANAGER;
$pagen = intval($_REQUEST['page']);
$filePath = $_SERVER['DOCUMENT_ROOT'].'/crm/import/importfile/csv/deal.csv';
$importFile = new importFile;
$CCrmDeal = new CCrmDeal(false);

$isExporting = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'import') ? true : false;

if($isExporting)
	$recperpage = 1000;

$arData = $importFile->load($filePath, $pagen, $recperpage);
echo $importFile->showhtml($arData['header'], $arData['rowdata'], $isExporting);


define('ID', 0);
define('DEALNAAM', 1);
define('FASE', 2);
define('INKOMSTEN', 3);
define('KOSTEN_DERDEN', 4);
define('KOSTEN_LIFT', 5);
define('KOSTEN_UREN_IF', 6);
define('AQUISITIE_KOSTEN', 7);
define('RENDEMENT', 8);
define('SLAGINGSKANS', 9);
define('ACQUISITEUR', 10);
define('OFFERTEDATUM', 11);
define('VERWACHTE_OPDRACHTDATUM', 12);
define('TECHNOLOGIE', 13);
define('PRODUCTGROEP', 14);
define('PROJECTCONNECTION', 15);
define('COMPANY', 16);
define('COMPANY_ID', 17);
define('CONTACT', 18);
define('CONTACT_ID', 19);

define('CRM_INKOMSTEN', 'UF_CRM_1511431560');
define('CRM_KOSTEN_DERDEN', 'UF_CRM_1412332756');
define('CRM_KOSTEN_LIFT', 'UF_CRM_1475155427');
define('CRM_KOSTEN_UREN_IF', 'UF_CRM_1511431510');
define('CRM_AQUISITIE_KOSTEN', 'UF_CRM_1511431537');
define('CRM_TECHNOLOGIE', 'UF_CRM_1493724413');

define('CRM_PROJECT_NAME', 'UF_CRM_1511431371');
define('CRM_PROJECT_ID', 'UF_CRM_1511431462');

// import b_crm_deal
$stages = $importFile->getStatus('DEAL_STAGE');
$types = $importFile->getStatus('DEAL_TYPE');

foreach($arData['rowdata'] as $key => $data) {

	if(intval($data[PROJECTCONNECTION]) > 0)
		$arProject = $importFile->getProjectByID(intval($data[PROJECTCONNECTION]));


	$dealId = (intval($data[ID]) > 0) ? intval($data[ID]) : 0;

	$action = "";

	if($isExporting) {
		if(intval($dealId) > 0 && $importFile->isDealExist(intval($dealId))) {

			$arFields = array(
				'OPPORTUNITY' => $importFile->cleanMoney($data[RENDEMENT]),
				'PROBABILITY' => $data[SLAGINGSKANS],
				CRM_INKOMSTEN => $importFile->cleanMoney($data[INKOMSTEN]),
				CRM_KOSTEN_DERDEN => $importFile->cleanMoney($data[KOSTEN_DERDEN]),
				CRM_KOSTEN_LIFT => $importFile->cleanMoney($data[KOSTEN_LIFT]),
				CRM_KOSTEN_UREN_IF => $importFile->cleanMoney($data[KOSTEN_UREN_IF]),
				CRM_AQUISITIE_KOSTEN => $importFile->cleanMoney($data[AQUISITIE_KOSTEN]),
				CRM_TECHNOLOGIE => $data[TECHNOLOGIE],
				CRM_PROJECT_NAME => $data[PROJECTCONNECTION].": ".$data[DEALNAAM],
				CRM_PROJECT_ID => $data[PROJECTCONNECTION],
				CRM_PROJECT_NAME => $arProject['TITLE'],
				CRM_PROJECT_ID => $arProject['ID'],
			);

			$action = "update";
		}
		else {
			$arFields = array(
				//'ID' => $dealId,
				'TITLE' => $data[DEALNAAM],
				'STAGE_ID' => $stages[$data[FASE]],
				'TYPE_ID' => $types[$data[PRODUCTGROEP]],
				'OPENED' => "Y",
				'ASSIGNED_BY_ID' => $data[ACQUISITEUR],
				'BEGINDATE' => ($data[OFFERTEDATUM] != "") ? date('d/m/Y', strtotime($data[OFFERTEDATUM])) : "",
				'CLOSEDATE' => ($data[VERWACHTE_OPDRACHTDATUM] != "") ? date('d/m/Y', strtotime($data[VERWACHTE_OPDRACHTDATUM])) : "",
				'OPPORTUNITY' => $importFile->cleanMoney($data[RENDEMENT]),
				'PROBABILITY' => $data[SLAGINGSKANS],
				'COMPANY_ID' => $data[COMPANY_ID],
				CRM_INKOMSTEN => $importFile->cleanMoney($data[INKOMSTEN]),
				CRM_KOSTEN_DERDEN => $importFile->cleanMoney($data[KOSTEN_DERDEN]),
				CRM_KOSTEN_LIFT => $importFile->cleanMoney($data[KOSTEN_LIFT]),
				CRM_KOSTEN_UREN_IF => $importFile->cleanMoney($data[KOSTEN_UREN_IF]),
				CRM_AQUISITIE_KOSTEN => $importFile->cleanMoney($data[AQUISITIE_KOSTEN]),
				CRM_TECHNOLOGIE => $data[TECHNOLOGIE],
				CRM_PROJECT_NAME => $data[PROJECTCONNECTION].": ".$data[DEALNAAM],
				CRM_PROJECT_ID => $data[PROJECTCONNECTION],
				CRM_PROJECT_NAME => $arProject['TITLE'],
				CRM_PROJECT_ID => $arProject['ID'],
			);

			if(intval($data[CONTACT_ID]) > 0) {

				$arFields['CONTACT_BINDINGS'] = array(
					array(
						'CONTACT_ID' => $data[CONTACT_ID],
						'SORT' => 10,
						'IS_PRIMARY' => 'Y'
					),
				);
			}

			$action = "add";
		}

		if(intval($dealId) > 0 && $action == "add") {
			$ID = $CCrmDeal->Add($arFields, true, array('REGISTER_SONET_EVENT' => false));

			$dbAddExactDeal = "INSERT INTO ".
							"`m_exact_deal` ".
							"SET ".
								"`EXACT_ID` = ".intval($dealId).", ".
								"`DEAL_ID` = ".intval($ID).";";

			$DB->Query($dbAddExactDeal, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		else if(intval($dealId) > 0 && $action == "update") {
			$ID = $CCrmDeal->Update(intval($dealId), $arFields, true, true, array('REGISTER_SONET_EVENT' => false));
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
