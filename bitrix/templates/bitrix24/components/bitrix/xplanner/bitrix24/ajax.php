<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(isset($_POST) && !empty($_POST))
{
	global $USER, $DB;
	CModule::IncludeModule("tasks");

	$arData = $_POST;
	$Project = new Project();

	$arData["DEAL_ID"] = NULL;
	$arData["TASK_ID"] = NULL;

	$isAdded = false;
	$error = "";

	$hasDeal = false;
	$hasTask = false;
	$hasProject = false;

	$isKosten = false;
	$isAcquisite = false;

	if(stripos($arData["deal_task"], "[") !== false && stripos($arData["deal_task"], "]") !== false)
	{
		$dealTaskExplode1 = explode("[", $arData["deal_task"]);
		$dealTaskExplode2 = explode("]", $dealTaskExplode1[1]);

		$taskNumber = 0;

		if(stripos($dealTaskExplode2[0], ".") !== false) // ProjectID.taskID
		{
			$dataIDEx = explode(".", $dealTaskExplode2[0]);
			$projectID = $dataIDEx[0];
			$taskID = $dataIDEx[1];

			if(!empty($projectID)) 
			{
				$rsProject = $Project->getProjectSimpleData($projectID);
				if($rsProject->SelectedRowsCount() > 0) 
				{
					$arData["PROJECT_ID"] = $projectID;
					$hasProject = true;
					
					$arFilter = array();
					$arFilter["XML_ID"] = $dealTaskExplode2[0];
					$arFilter["DOER"] = \Bitrix\Tasks\Util\User::getId();

					$dbRes = CTasks::GetList(array(), $arFilter, Array("ID"), array());
					if($arTask = $dbRes->Fetch())
						$taskNumber = $arTask["ID"];
				}
				else
					$error .= "Project " . $projectID . " doesn't exists\n";
			}

			$isKosten = true;
		}
		else { // DealID

			$arProject = $Project->getProjectByDealId($dealTaskExplode2[0]);

			if(!empty($arProject))
			{
				$arData["PROJECT_ID"] = $arProject["ID"];
				$arData["DEAL_ID"] = $dealTaskExplode2[0];
				$arData["XML_ID"] = $dealTaskExplode2[0];
				$arData["TASK_ID"] = NULL;
				$isAcquisite = true;
				$isAdded = true;
			}
			else
				$error .= "No associated project on this deal " . $dealTaskExplode2[0] . "\n";
		}
		
		if(!empty($taskNumber))
		{
			$arFilter = array();
			$arFilter["ID"] = $taskNumber;
			$arFilter["DOER"] = \Bitrix\Tasks\Util\User::getId();

			$dbRes = CTasks::GetList(array(), $arFilter, Array("*", "UF_*"), array());
			if ($arRes = $dbRes->GetNext())
			{
				$hasTask = true;
				if(!empty($arRes["UF_CRM_TASK"]))
				{
					$getDealTask = 0;
					foreach ($arRes["UF_CRM_TASK"] as $key => $value) 
					{
						if(stripos($value, "D_") !== false)
						{
							$explodeDeal = explode("_", $value);
							$dealNumber = $explodeDeal[1];

							$dbSelect = "select ID from b_crm_deal where ID = " . $DB->ForSQL($dealNumber) . " AND STAGE_ID = 'WON'";
							$resDeal = $DB->Query($dbSelect);
							if($arDeal = $resDeal->Fetch()){
								$getDealTask = $dealNumber;
								$hasDeal = true;
							}

							if($hasDeal) break;
						}
					}
					
					if($hasDeal && $getDealTask != 0)
					{
						$arData["DEAL_ID"] = $getDealTask;
						$arData["TASK_ID"] = $taskNumber;
						$arData["XML_ID"] = $arRes["XML_ID"];
						$isAdded = true;
					}		
				}
			}
			if(!$hasDeal) $error .= "Deal doesn't exists\n";
			if(!$hasTask) $error .= "Task doesn't exists\n";
		}
		else
			$error .= "Task doesn't exists\n";
	}
	else
		$error .= "Project Task / Deal doesn't exists";

	if($isAdded)
	{
		$_POST["date_inserted"] = str_replace("/", "-", $_POST["date_inserted"]);
		$datetime = strtotime($_POST["date_inserted"]);

		$arData["DATE_INSERT"] = date('Y-m-d', $datetime) . " " . date("H:i:s");
		$arData["USER_ID"] = $USER->GetID();
		$arData["HOURS_LOG"] = number_format(floatval(str_replace(",", ".", $_POST["hours_log"])), 2);

		if(strtolower($_POST["comments"]) == "notitie")
			$_POST["comments"] = "";

		$arData["COMMENTS"] = $_POST["comments"];
		$arData["USER_HOURLY_RATE"] = NULL;
		$arData["TOTAL_RATE"] = NULL;

		$getHourRate = "select UF_USER_HOURLY_RATE from b_uts_user where VALUE_ID = " . $DB->ForSQL($USER->GetID());
		$resHourRate = $DB->Query($getHourRate);
		if($arUserHourRate = $resHourRate->Fetch()) {
			$arData["USER_HOURLY_RATE"] = $arUserHourRate["UF_USER_HOURLY_RATE"];
			$arData["TOTAL_RATE"] = floatval($arData["HOURS_LOG"]) * floatval($arData["USER_HOURLY_RATE"]);
		}
		

		$arTimeTableLog = array(
	        'USER_ID' => $DB->ForSQL($arData["USER_ID"]),
	        'HOURS_LOG' => $DB->ForSQL($arData["HOURS_LOG"]),
	        'COMMENTS' => $DB->ForSQL($arData["COMMENTS"]),
	        'USER_HOURLY_RATE' => $DB->ForSQL($arData["USER_HOURLY_RATE"]),
	        'DEAL_ID' =>  $DB->ForSQL($arData["DEAL_ID"]),
	        'TASK_ID' => $DB->ForSQL($arData["TASK_ID"]),
	        'TOTAL_RATE' => $DB->ForSQL($arData["TOTAL_RATE"]),
	        'XML_ID' => $DB->ForSQL($arData["XML_ID"]),
	        'PROJECT_ID' => $DB->ForSQL($arData["PROJECT_ID"]),
	    );

	    if($isKosten)
	    	$arTimeTableLog["TYPE"] = "COST_IF";
	    else
	    	$arTimeTableLog["TYPE"] = "ACQUISITION_COSTS";

	    $strTimeTableLogInsert = $DB->PrepareUpdate("m_timetable_log", $arTimeTableLog);
	    $strTimeTableLogSql = "INSERT INTO m_timetable_log SET {$strTimeTableLogInsert}, `DATE_INSERT` = '" . $DB->ForSQL($arData["DATE_INSERT"]) . "'";
	    $DB->Query($strTimeTableLogSql);

	    // get log hours for task
	    $dbGetUserLog = 'SELECT SUM(TOTAL_RATE) AS TOTAL_SUM FROM `m_timetable_log` WHERE `DEAL_ID` = '.intval($arData["DEAL_ID"]).' AND `TASK_ID` = '.intval($arData["TASK_ID"]).'; ';
        $rsGetUserLog = $DB->Query($dbGetUserLog, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        if($arGetUserLog = $rsGetUserLog->fetch())  
        {
        	if($isKosten)
        	{
        		$totalHrs = doubleVal($arGetUserLog['TOTAL_SUM']);
	        	$mod_project_task = new mod_project_task(intval($arData["DEAL_ID"]), intval($arData["TASK_ID"]));
				$mod_project_task->setTaskData('COST_IF', $totalHrs, 'ACTUAL');
        	}
        	else if($isAcquisite)
        	{
        		$totalHrs = doubleVal($arGetUserLog['TOTAL_SUM']);
	        	$mod_project_task = new mod_project_task(intval($arData["DEAL_ID"]), 0, false);
				$mod_project_task->setTaskData('ACQUISITION_COSTS', $totalHrs, 'ACTUAL', true);
        	}
        }

	    echo "success";
	}
	else
		echo $error;
}
?>