<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("tasks");	
$arOrder = array("STATUS" => "ASC", "DEADLINE" => "DESC", "PRIORITY" => "DESC", "ID" => "DESC");
$arFilter = array(
	'DOER'   => \Bitrix\Tasks\Util\User::getId(),
);

$arTaskNames = array();
$arDealCodes = array();
$Project = new Project();
$arResults = array();

global $DB, $USER;


$dbRes = CTasks::GetList($arOrder, $arFilter, Array("*", "UF_*"), $arGetListParams);
while ($arRes = $dbRes->GetNext())
{
	if(!empty($arRes["UF_CRM_TASK"]))
	{
		$hasDeal = false;
		$arDealInfo = array();
		foreach ($arRes["UF_CRM_TASK"] as $key => $value) 
		{
			if(stripos($value, "D_") !== false)
			{
				$explodeDeal = explode("_", $value);
				$dealNumber = $explodeDeal[1];

				$dbSelect = "select ID, TITLE, CREATED_BY_ID, ASSIGNED_BY_ID from b_crm_deal where ID = " . $DB->ForSQL($dealNumber) . " AND STAGE_ID = 'WON'";
				$resDeal = $DB->Query($dbSelect);
				if($arDeal = $resDeal->Fetch()){
					$arDealInfo = $arDeal;
					$hasDeal = true;
				}

				if($hasDeal) break;
			}
		}
		
		if($hasDeal && !empty($arDealInfo))
		{
			$arProject = $Project->getProjectByDealId($arDealInfo["ID"]);

			#verify project
			$explodeProj = explode(".", $arRes["XML_ID"]);
			$projectID = $explodeProj[0];

			$rsProject = $Project->getProjectSimpleData($projectID);
			if($rsProject->SelectedRowsCount() > 0) 
			{
				$value = "[" . $arRes["XML_ID"] . "] " . $arRes["TITLE"];
				if(!empty($arProject["ID"]) && $arRes["ID"] && !empty($arRes["XML_ID"]))
					$arResults[$arRes["XML_ID"]] = $value;
			}

			$value = "[" . $arDealInfo["ID"] . "] " . $arDealInfo["TITLE"];
			if(!empty($arDealInfo["ID"]) && ($arDealInfo["CREATED_BY_ID"] == $USER->GetID() || $arDealInfo["ASSIGNED_BY_ID"] == $USER->GetID()) )
				$arResults[$arDealInfo["ID"]] = $value;
		}		
	}
}

$getDeals = "select mdp.DEAL_ID, bcd.TITLE from m_deal_project mdp inner join m_project mp on mdp.PROJECT_ID = mp.ID inner join b_crm_deal bcd on bcd.ID = mdp.DEAL_ID where (bcd.CREATED_BY_ID = " . $USER->GetID() . " OR bcd.ASSIGNED_BY_ID = " . $USER->GetID() . ")";
$dbDeals = $DB->Query($getDeals);
while($arDeals = $dbDeals->Fetch())
	$arResults[$arDeals["DEAL_ID"]] = "[" . $arDeals["DEAL_ID"] . "] " . $arDeals["TITLE"];

ksort($arResults);
$arResults = array_values($arResults);

if(!empty($arResults))
	echo json_encode($arResults);
else
	echo null;
?>