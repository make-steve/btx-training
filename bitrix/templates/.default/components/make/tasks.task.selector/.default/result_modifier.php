<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$Project = new Project();
global $USER, $DB;
CModule::IncludeModule("tasks");

$newLastTask = array();

foreach($arResult["LAST_TASKS"] as $key=>$task)
{
	$arFilter = array();
	$arFilter["ID"] = $task["ID"];
	$arFilter["DOER"] = \Bitrix\Tasks\Util\User::getId();

	$dbRes = CTasks::GetList(array(), $arFilter, Array("*", "UF_*"), array());
	if ($arRes = $dbRes->GetNext())
	{
		if(!empty($arRes["UF_CRM_TASK"]))
		{
			$hasDeal = false;
			foreach ($arRes["UF_CRM_TASK"] as $key => $value) 
			{
				if(stripos($value, "D_") !== false)
				{
					$explodeDeal = explode("_", $value);
					$dealNumber = $explodeDeal[1];

					$dbSelect = "select ID from b_crm_deal where ID = " . $DB->ForSQL($dealNumber) . " AND STAGE_ID = 'WON'";
					$resDeal = $DB->Query($dbSelect);
					if($arDeal = $resDeal->Fetch())
					{
						$hasDeal = true;
						$arProject = $Project->getProjectByDealId($dealNumber);
						$task["PROJECT_TASK_ID"] = $arRes["XML_ID"];
					}

					if($hasDeal) break;
				}
			}

			$newLastTask[$key] = $task;
		}
	}
}

$arResult["LAST_TASKS"] = $newLastTask;

$newCurrentTask = array();

foreach($arResult["CURRENT_TASKS"] as $key=>$task)
{
	$arFilter = array();
	$arFilter["ID"] = $task["ID"];
	$arFilter["DOER"] = \Bitrix\Tasks\Util\User::getId();

	$dbRes = CTasks::GetList(array(), $arFilter, Array("*", "UF_*"), array());
	if ($arRes = $dbRes->GetNext())
	{
		if(!empty($arRes["UF_CRM_TASK"]))
		{
			$hasDeal = false;
			foreach ($arRes["UF_CRM_TASK"] as $key => $value) 
			{
				if(stripos($value, "D_") !== false)
				{
					$explodeDeal = explode("_", $value);
					$dealNumber = $explodeDeal[1];

					$dbSelect = "select ID from b_crm_deal where ID = " . $DB->ForSQL($dealNumber) . " AND STAGE_ID = 'WON'";
					$resDeal = $DB->Query($dbSelect);
					if($arDeal = $resDeal->Fetch())
					{
						$hasDeal = true;
						$arProject = $Project->getProjectByDealId($dealNumber);
						$task["PROJECT_TASK_ID"] = $arRes["XML_ID"];
					}

					if($hasDeal) break;
				}
			}

			$newCurrentTask[$key] = $task;
		}
	}
}

$arResult["CURRENT_TASKS"] = $newCurrentTask;

?>