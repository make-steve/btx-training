<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$response = array();

if(isset($_POST) && !empty($_POST))
{
	global $USER, $DB;


	$dealid = 0;
	$taskid = 0;
	$totalHrRate = 0;
	$typeLog = "";

	if(!$USER->isAdmin())
	{
		$dbSelect = "select ID,DEAL_ID,TASK_ID,USER_HOURLY_RATE,TYPE from m_timetable_log where ID = " . $DB->ForSQL(intval($_POST["dealtaskid"])) . " AND USER_ID = " . $USER->GetID();
		$rsTimeLog = $DB->Query($dbSelect);
		if($rsTimeLog->SelectedRowsCount() <= 0) 
			$response["error"] = 'Invalid update!';
	}


	if(intval($_POST["dealtaskid"]) > 0) {
		$dbSelect = "select ID,DEAL_ID,TASK_ID,USER_HOURLY_RATE,TYPE from m_timetable_log where ID = " . $DB->ForSQL(intval($_POST["dealtaskid"]));
		$rsTimeLog = $DB->Query($dbSelect);

		if($arTimeLog = $rsTimeLog->Fetch()) {

			$dealid = $arTimeLog['DEAL_ID'];
			$taskid = $arTimeLog['TASK_ID'];
			$totalHrRate = $arTimeLog['USER_HOURLY_RATE'];
			$typeLog = $arTimeLog['TYPE'];
		}
	}

	

	// get deal id and task id
	if($_POST["hourslog"] == "0,00" || $_POST["hourslog"] == "0" || $_POST["hourslog"] == "0.00" || empty($_POST["hourslog"]))
	{
		$resCheck = $DB->Query("delete from m_timetable_log where ID = " . $DB->ForSQL($_POST["dealtaskid"]));
	}
	else {
		
		$hoursLog = number_format(floatval(str_replace(",", ".", $_POST["hourslog"])), 2);
		$commentLog = $_POST["commentlog"];
		$datemodified = date('Y-m-d H:i:s');
		$usermodified = $USER->GetID();

		if($totalHrRate > 0) {
			$total_rate = $hoursLog * $totalHrRate;
			$resCheck = $DB->Query("update m_timetable_log set DATE_MODIFIED = '" . $DB->ForSQL($datemodified) . "', MODIFIED_BY = '" . $DB->ForSQL($usermodified) . "', HOURS_LOG = '" . $DB->ForSQL($hoursLog) . "', COMMENTS = '" . $DB->ForSQL($commentLog) . "', TOTAL_RATE = '".$total_rate."' where ID = " . $DB->ForSQL($_POST["dealtaskid"]));
		}
		else
			$response["error"] = 'Please input user hourly rate!';
	}

	if($resCheck)
	{
		// get log hours for task
	    $dbGetUserLog = 'SELECT SUM(TOTAL_RATE) AS TOTAL_SUM FROM `m_timetable_log` WHERE `DEAL_ID` = '.intval($dealid).' AND `TASK_ID` = '.intval($taskid).'; ';
	    $rsGetUserLog = $DB->Query($dbGetUserLog, false, "File: ".__FILE__."<br>Line: ".__LINE__);

	    if($arGetUserLog = $rsGetUserLog->fetch())  {

	    	if($typeLog == "COST_IF")
	    	{
	    		$totalHrs = doubleVal($arGetUserLog['TOTAL_SUM']);
		    	$mod_project_task = new mod_project_task(intval($dealid), intval($taskid));
				$mod_project_task->setTaskData('COST_IF', $totalHrs, 'ACTUAL');
	    	}
	    	else
	    	{
	    		$totalHrs = doubleVal($arGetUserLog['TOTAL_SUM']);
	        	$mod_project_task = new mod_project_task(intval($dealid), 0, false);
				$mod_project_task->setTaskData('ACQUISITION_COSTS', $totalHrs, 'ACTUAL', true);
	    	}

	        
	    }
	}
	
}

echo $response["error"];
?>