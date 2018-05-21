<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(isset($_POST) && !empty($_POST))
{
	global $USER, $DB;
	CModule::IncludeModule("tasks");
	CModule::IncludeModule("socialnetwork");

	$arData = $_POST;
	$Project = new Project();

	$arData["DEAL_ID"] = 0;
	$arData["TASK_ID"] = 0;

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

					if($USER->isAdmin())
					{
						$arFilter = array(
							"XML_ID" => $dealTaskExplode2[0],
							CUF_T_LOCKED => "0"
						);
					}
					else {
						$arFilter = array(
							array(
								'::LOGIC' => 'OR',
								array('DOER'   => \Bitrix\Tasks\Util\User::getId()),
								array('GROUP_ID'   => CSocNetTools::GetMyGroups()),
								array('CREATED_BY'   => \Bitrix\Tasks\Util\User::getId()),
							),
							"XML_ID" => $dealTaskExplode2[0],
							CUF_T_LOCKED => "0"
						);
					}


					$dbRes = CTasks::GetList(array(), $arFilter, Array("ID"), array());
					while($arTask = $dbRes->Fetch()) {
						if(checkTaskActivity($arTask["ID"])) {
							$taskNumber = $arTask["ID"];
							break;
						}
					}
						
				}
				else
					$error .= "Project " . $projectID . " doesn't exists\n";
			}

			$isKosten = true;
		}
		else { // DealID

			if($USER->isAdmin())
				$getDeals = "select mdp.DEAL_ID, bcd.TITLE from m_deal_project mdp inner join m_project mp on mdp.PROJECT_ID = mp.ID inner join b_crm_deal bcd on bcd.ID = mdp.DEAL_ID where mdp.DEAL_ID = " . $DB->ForSQL($dealTaskExplode2[0]);
			else
				$getDeals = "select mdp.DEAL_ID, bcd.TITLE from m_deal_project mdp inner join m_project mp on mdp.PROJECT_ID = mp.ID inner join b_crm_deal bcd on bcd.ID = mdp.DEAL_ID where (bcd.ASSIGNED_BY_ID = " . $DB->ForSQL($USER->GetID()) . ") AND (mdp.DEAL_ID = " . $DB->ForSQL($dealTaskExplode2[0]) . ")";

			$dbDeals = $DB->Query($getDeals);
			if($arDeals = $dbDeals->Fetch()) 
			{
				$arProject = $Project->getProjectByDealId($dealTaskExplode2[0]);

				if(!empty($arProject))
				{
					$arData["PROJECT_ID"] = $arProject["ID"];
					$arData["DEAL_ID"] = $dealTaskExplode2[0];
					$arData["XML_ID"] = $dealTaskExplode2[0];
					$arData["TASK_ID"] = 0;
					$isAcquisite = true;
					$isAdded = true;
				}
				else
					$error .= "No associated project on this deal " . $dealTaskExplode2[0] . "\n";
			}
			else
				$error .= "No associated project on this deal " . $dealTaskExplode2[0] . "\n";
		}
		
		if(!empty($taskNumber))
		{
			if($USER->isAdmin())
			{
				$arFilter = array(
					"ID" => $taskNumber
				);
			}
			else
			{
				$arFilter = array(
					array(
						'::LOGIC' => 'OR',
						array('DOER'   => \Bitrix\Tasks\Util\User::getId()),
						array('GROUP_ID'   => CSocNetTools::GetMyGroups()),
						array('CREATED_BY'   => \Bitrix\Tasks\Util\User::getId()),
					),
					"ID" => $taskNumber
				);
			}			

			$dbRes = CTasks::GetList(array(), $arFilter, Array("*", "UF_*"), array());

			$arRes = array();
			while ($arTask = $dbRes->GetNext())
			{
				if(checkTaskActivity($arTask["ID"])) {
					$arRes = $arTask;
					$hasTask = true;
					break;
				}
			}

			
			if(!empty($arRes["UF_CRM_TASK"]) && $arRes[CUF_T_LOCKED] != "1" && $hasTask)
			{
				$getDealTask = 0;
				if(is_array($arRes["UF_CRM_TASK"]))
				{
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
				}
				else
				{
					if(stripos($arRes["UF_CRM_TASK"], "D_") !== false)
					{
						$explodeDeal = explode("_", $arRes["UF_CRM_TASK"]);
						$dealNumber = $explodeDeal[1];

						$dbSelect = "select ID from b_crm_deal where ID = " . $DB->ForSQL($dealNumber) . " AND STAGE_ID = 'WON'";
						$resDeal = $DB->Query($dbSelect);
						if($arDeal = $resDeal->Fetch()){
							$getDealTask = $dealNumber;
							$hasDeal = true;
						}
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

			if(!$hasDeal) $error .= "Deal doesn't exists\n";
			if(!$hasTask) $error .= "Task doesn't exists\n";
		}
		else if ($isAdded == false)
			$error .= "Task doesn't exists\n";
	}
	else
		$error .= "Project Task / Deal doesn't exists\n";

	$arData["USER_HOURLY_RATE"] = NULL;
	$arData["TOTAL_RATE"] = NULL;

	$getHourRate = "select UF_USER_HOURLY_RATE from b_uts_user where VALUE_ID = " . $DB->ForSQL($USER->GetID());
	$resHourRate = $DB->Query($getHourRate);
	if($arUserHourRate =  $resHourRate->Fetch()) {
		$arData["USER_HOURLY_RATE"] = $arUserHourRate["UF_USER_HOURLY_RATE"];
		$arData["USER_HOURLY_RATE"] = number_format(doubleval(str_replace(",", ".", $arData["USER_HOURLY_RATE"])), 2);
		
	}
	else {
		$isAdded = false;
		$error .= "Current user doesn't have hourly rates!\n";
	}
	
	if(doubleval($arData["USER_HOURLY_RATE"]) <= 0)
	{
		$isAdded = false;
		$error .= "Current user doesn't have hourly rates!\n";
	}


	if($isAdded)
	{
		$_POST["date_inserted"] = str_replace("/", "-", $_POST["date_inserted"]);
		$datetime = strtotime($_POST["date_inserted"]);

		$arData["DATE_CREATED"] = date('Y-m-d H:i:s');
		$arData["DATE_LOG"] = date('Y-m-d', $datetime) . " " . date("H:i:s");
		$arData["USER_ID"] = $USER->GetID();
		$arData["HOURS_LOG"] = number_format(doubleval(str_replace(",", ".", $_POST["hours_log"])), 2);
		$arData["TOTAL_RATE"] = doubleval($arData["HOURS_LOG"]) * doubleval($arData["USER_HOURLY_RATE"]);

		if(strtolower($_POST["comments"]) == "notitie")
			$_POST["comments"] = "";

		$arData["COMMENTS"] = $_POST["comments"];

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
	    $strTimeTableLogSql = "INSERT INTO m_timetable_log SET {$strTimeTableLogInsert}, `DATE_LOG` = '" . $DB->ForSQL($arData["DATE_LOG"]) . "', `DATE_CREATED` = '" . $DB->ForSQL($arData["DATE_CREATED"]) . "';";
	    $DB->Query($strTimeTableLogSql);

	    if(empty($arData["TASK_ID"])) $arData["TASK_ID"] = 0;

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

function checkTaskActivity($taskid)
{
	global $DB;

	$select = "select * from b_crm_act where OWNER_TYPE_ID = 2 and ASSOCIATED_ENTITY_ID = " . $taskid;
	$resTask = $DB->Query($select);

	if($resTask->SelectedRowsCount() > 0)
		return true;
	else
		return false;
}
?>