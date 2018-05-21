<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

global $USER, $DB;

$query = "";
$arProjectDealSettings = array();

$arProjectData = $arParams['DATA'];
$userID = $USER->GetID();

$checkExists = "select * from m_projectdeal_detail_settings where PROJECT_ID = '" . $DB->ForSQL($arProjectData["ID"]) . "' and USER_ID = '" . $DB->ForSQL($userID) . "';";
$resProjectDeal = $DB->Query($checkExists);

if($resProjectDeal->SelectedRowsCount() > 0)
{
	while($arProjectDeal = $resProjectDeal->Fetch())
	{
		if($arProjectDeal["SHOW_TASKS"] == "Y")
			$arProjectDealSettings[$arProjectDeal["DEAL_ID"]] = " crm-lead-header-contact-btn-open";
		else
			$arProjectDealSettings[$arProjectDeal["DEAL_ID"]] = " crm-lead-header-contact-btn-close";
	}
}
else
{
	$arProjectDealSettings[$arProjectData["ID"]] = " crm-lead-header-contact-btn-open";
}


$arParams["DATA_SETTINGS"] = $arProjectDealSettings;
?>