<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$spoutAutoload = $_SERVER["DOCUMENT_ROOT"] . "/assets/include/spout-master/Spout/Autoloader/autoload.php";
require_once $spoutAutoload;	
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;

if(isset($_POST["export"]))
{

	$query = "select DISTINCT(mtl.ID), mtl.*, bt.TITLE as TASK_TITLE, mp.TITLE as PROJECT_TITLE, bcd.TITLE as DEAL_TITLE 
				from m_timetable_log mtl
				left join b_tasks bt
				on mtl.TASK_ID = bt.ID 
				left join m_project mp
				on mp.ID = mtl.PROJECT_ID
				left join b_crm_deal bcd
				on bcd.ID = mtl.DEAL_ID
				where ";
	
	#dateFilter
	$where = "";
	if(!empty($_POST["DATE_INSERT_from"]) && empty($_POST["DATE_INSERT_to"]))
	{
		$dateFrom = $_POST["DATE_INSERT_from"] . " 00:00:00";
		$dateTo = date('Y-m-d') . " 23:59:59";
		$where .= "(mtl.DATE_LOG BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "') ";
	}
	else if(empty($_POST["DATE_INSERT_from"]) && !empty($_POST["DATE_INSERT_to"]))
	{
		$dateFrom = "0000-00-00 00:00:00";
		$dateTo = $_POST["DATE_INSERT_to"] . " 23:59:59";
		$where .= "(mtl.DATE_LOG BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "') ";
	}
	else if(!empty($_POST["DATE_INSERT_from"]) && !empty($_POST["DATE_INSERT_to"]))
	{
		$dateFrom = $_POST["DATE_INSERT_from"] . " 00:00:00";
		$dateTo = $_POST["DATE_INSERT_to"] . " 23:59:59";
		$where .= "(mtl.DATE_LOG BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "') ";
	}
	else
	{
		$dateFrom = "0000-00-00 00:00:00";
		$dateTo = date('Y-m-d') . " 23:59:59";
		$where .= "(mtl.DATE_LOG BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "') ";
	}

	$arPMS = array();
	$arMembers = array();

	$_POST["USER_ID"] = array_filter($_POST["USER_ID"]);
	$_POST["ORIGINATOR_USER_ID"] = array_filter($_POST["ORIGINATOR_USER_ID"]);

	if(is_array($_POST["ORIGINATOR_USER_ID"]))
		$arPMS = $_POST["ORIGINATOR_USER_ID"];
	else if(isset($_POST["ORIGINATOR_USER_ID"]))
		$arPMS[] = $_POST["ORIGINATOR_USER_ID"];
	else
		$arPMS = null;

	if(is_array($_POST["USER_ID"]))
		$arMembers = $_POST["USER_ID"];
	else if(isset($_POST["USER_ID"]))
		$arMembers[] = $_POST["USER_ID"];
	else
		$arMembers = null;

	#userFilter
	$userWhere = '';
	$unionSQL = '';

	
	#include ProjectLeider = Originator
	if(!empty($arPMS))
	{
		#get Connected Deal of a PM
		$arConnectedPMDealUsers = array();
		$arConnectedPMTask = array();

		$getTempquery = $query . $where;
		$rsTemp = $DB->Query($getTempquery);
		while($arTemp = $rsTemp->Fetch()) {

			$queryDealTask = "select bca.OWNER_ID, btm.TASK_ID from b_crm_act bca
							inner join b_tasks_member btm 
							on btm.TASK_ID = bca.ASSOCIATED_ENTITY_ID
							where bca.TYPE_ID = 3 AND 
								  btm.TYPE = 'O' AND 
								  bca.OWNER_ID = '" . $arTemp["DEAL_ID"] . "' AND 
								  btm.TASK_ID = '" . $arTemp["TASK_ID"] . "' AND 
								  btm.USER_ID IN (" . implode(", ", $arPMS) . ")";


			$rsDealTask = $DB->Query($queryDealTask);

			if($rsDealTask->SelectedRowsCount() > 0)
			{
				$arConnectedPMTask[$arTemp["TASK_ID"]] = $arTemp["TASK_ID"];

				$queryGetAcquisitor = "select USER_ID from b_tasks_member where TASK_ID = " . $arTemp["TASK_ID"] . " AND TYPE = 'R'";
				$rsTaskUsers = $DB->Query($queryGetAcquisitor);
				while ($arAcquisitors = $rsTaskUsers->Fetch()) 
					$arConnectedPMDealUsers[$arTemp["DEAL_ID"]][$arTemp["TASK_ID"]] = $arAcquisitors["USER_ID"];

			}
		}

		
		if(!empty($arConnectedPMDealUsers) || !empty($arConnectedPMTask))
		{

			if(!empty($arConnectedPMDealUsers)) 
			{
				$appendDealUserWhere = " OR (" . $where . " AND (";

				$arDealUsers = array();
				foreach ($arConnectedPMDealUsers as $dealkey => $arAcquisitors) 
					$arDealUsers[] = " (mtl.DEAL_ID = '" . $dealkey . "' AND mtl.TASK_ID = 0 AND mtl.USER_ID IN (" . implode(", ", $arAcquisitors) . "))";
				
						
				$appendDealUserWhere .= implode(" OR ", $arDealUsers); 
				$appendDealUserWhere .= ")) "; 
			}

			if(!empty($arConnectedPMTask)) {

				if(!empty($arMembers))
					$appendDealTaskWhere = " OR (" . $where . " AND mtl.TASK_ID IN (" . implode(", ", $arConnectedPMTask) . ") AND mtl.USER_ID IN (" . implode(", ", $arMembers) . "))";
				else
					$appendDealTaskWhere = " OR (" . $where . " AND mtl.TASK_ID IN (" . implode(", ", $arConnectedPMTask) . "))";
			}
			

			$addOrCondition = $appendDealUserWhere . $appendDealTaskWhere;

		}
		

		$userWhere = "AND (mtl.USER_ID IN (" . implode(", ", $arPMS) . ") ) ";
	}
	else if(!empty($arMembers))
		$userWhere = "AND (mtl.USER_ID IN (" . implode(", ", $arMembers) . ")) ";


	$arTimeLogs = array();
	$query .= " (" . $where . $userWhere . ") " . $addOrCondition . "ORDER BY mtl.PROJECT_ID ASC, mtl.DATE_LOG DESC";

	/*if(!empty($arPMS) && !empty($unionSQL))
		$query = "(" . $query . ") UNION " . $unionSQL;*/

	/*echo $query;
	exit();*/


	$arUsers = array();
	$resTimeLog = $DB->Query($query);
	while($arTimeLog = $resTimeLog->Fetch())
	{
		$arTimeLogs[$arTimeLog["PROJECT_ID"]]["ID"] = $arTimeLog["ID"]; 
		$arTimeLogs[$arTimeLog["PROJECT_ID"]]["TITLE"] = $arTimeLog["PROJECT_TITLE"]; 


		$timeLogs = array();

		if($arTimeLog["TASK_ID"] > 0)
		{
			$getXMLID = "select XML_ID from b_tasks where ID = " . $arTimeLog["TASK_ID"];
			$rsXML = $DB->Query($getXMLID);
			if($arTaskXML = $rsXML->Fetch())
				$arTimeLog["XML_ID"] = $arTaskXML["XML_ID"];
		}

		$timeLogs["TASK_NUMBER"] = $arTimeLog["XML_ID"];
		$timeLogs["TASK_TITLE"] = $arTimeLog["TASK_TITLE"];
		$timeLogs["DEAL_ID"] = $arTimeLog["DEAL_ID"];
		$timeLogs["DEAL_TITLE"] = $arTimeLog["DEAL_TITLE"];

		if(!array_key_exists($arTimeLog["USER_ID"], $arUsers))
		{
			$rsUser = CUser::GetByID($arTimeLog["USER_ID"]);

			$userName = "";

			if($arUser = $rsUser->Fetch())
			{
				if(!empty($arUser["LAST_NAME"]))
					$userName = $arUser["LAST_NAME"] . ", ";
				if(!empty($arUser["NAME"]))
					$userName .= $arUser["NAME"] . " ";
				if(!empty($arUser["SECOND_NAME"]))
					$userName .= $arUser["SECOND_NAME"];

				//$userName .= implode(" ", Array($arUser["NAME"], $arUser["SECOND_NAME"]));

				$arUsers[$arTimeLog["USER_ID"]]["ID"] = $arTimeLog["USER_ID"];
				$arUsers[$arTimeLog["USER_ID"]]["NAME"] = $userName;
			}

		}

		$dateInsert = explode(" ", $arTimeLog["DATE_LOG"]);

		$timeLogs["USER_ID"] = $arTimeLog["USER_ID"];
		$timeLogs["USER_NAME"] = $arUsers[$arTimeLog["USER_ID"]]["NAME"];
		$timeLogs["DATE"] = $dateInsert[0];
		$timeLogs["COMMENTS"] = $arTimeLog["COMMENTS"];
		$timeLogs["HOURS_LOG"] = $arTimeLog["HOURS_LOG"];

		$arTimeLogs[$arTimeLog["PROJECT_ID"]]["LOGS"][$arTimeLog["XML_ID"]][] = $timeLogs; 
		$arTimeLogs[$arTimeLog["PROJECT_ID"]]["LOGS_TOTAL_HOURS"][$arTimeLog["XML_ID"]] += $arTimeLog["HOURS_LOG"]; 
	}
	
	$dateName = date('Y_m_d_H_i_s');
	$fileName = $_SERVER["DOCUMENT_ROOT"] . "/crm/project/project-overview-export/exportfiles/project_overview_" . $dateName . ".xlsx";
	$fname = "Project Overview (" . str_replace("_", "-", $dateName) . ").xlsx";

	$dateFromEx = array();
	if($dateFrom == "0000-00-00 00:00:00")
		$dateFromEx[0] = "From the start";
	else
		$dateFromEx = explode(" ", $dateFrom);

	$dateToEx = explode(" ", $dateTo);

	$dateString = '';
	if(!empty($_POST["DATE_INSERT_from"]) || !empty($_POST["DATE_INSERT_to"]))
		$dateString = "Datum : " . $dateFromEx[0] . " - " . $dateToEx[0];
	else
		$dateString = "Datum : All Logs";

	$pmString = '';
	$userString = '';
	if(!empty($_POST["ORIGINATOR_USER_ID"]))
	{
		$pmString = 'ProjectLeider/s: ';

		$getUser = new CUser;
		$arFilterUser = array();
		foreach ($_POST["ORIGINATOR_USER_ID"] as $key => $value) 
		{
			$resUser = $getUser->GetByID($value);
			if($arUser = $resUser->Fetch())
				$arFilterUser[] = $arUser["NAME"] . " [" . $value . "]";
		}

		$pmString .= implode(" | ", $arFilterUser);
	}
	else
		$pmString = 'ProjectLeider/s: All Projectleiders';

	if(!empty($_POST["USER_ID"]))
	{
		if(!empty($_POST["ORIGINATOR_USER_ID"])) $userString = 'Member/s: ';
		else $userString = 'Member/s: ';

		$getUser = new CUser;
		$arFilterUser = array();
		foreach ($_POST["USER_ID"] as $key => $value) 
		{
			$resUser = $getUser->GetByID($value);
			if($arUser = $resUser->Fetch())
				$arFilterUser[] = $arUser["NAME"] . " [" . $value . "]";
		}
		$userString .= implode(" | ", $arFilterUser);
	}
	else {
		$userString = 'Member/s: All Members';
	}

	

    $writer = WriterFactory::create(Type::XLSX);
	$writer->openToFile($fileName);

	$border = (new BorderBuilder())
    ->setBorderBottom('486924', Border::WIDTH_MEDIUM, Border::STYLE_SOLID)
    ->setBorderTop('486924', Border::WIDTH_MEDIUM, Border::STYLE_SOLID)
    ->setBorderLeft('486924', Border::WIDTH_MEDIUM, Border::STYLE_SOLID)
    ->setBorderRight('486924', Border::WIDTH_MEDIUM, Border::STYLE_SOLID)
    ->build();


	$writer->addRow(array(" "));
	$style = 
	$writer->addRowWithStyle(array("Urenspecificatie"), (new StyleBuilder())->setFontBold()->build());
	$writer->addRow(array($dateString));
	if(isset($_POST["isPM"]) && $_POST["isPM"] == "Y")
		$writer->addRow(array($pmString));
	$writer->addRow(array($userString));
	$writer->addRow(array(" "));
	$writer->addRowWithStyle(array('Project ID', 'Taak nummer', 'Taak omschrijving', 'DEAL Naam', 'Medewerker', 'Datum', 'Omschrijving', 'Uren', 'Totaal Uren'), (new StyleBuilder())->setFontBold()->setFontSize(11)->setFontColor(Color::BLACK)->setBackgroundColor('90D246')->setBorder($border)->build());

	if(!empty($arTimeLogs))
	{
		foreach($arTimeLogs as $project_id => $projectInfo) 
		{
			foreach ($projectInfo["LOGS"] as $xml_id => $tasks) 
			{
				foreach ($tasks as $key => $taskInfo) 
				{
					$writer->addRowWithStyle(array($project_id, $xml_id, $taskInfo["TASK_TITLE"], "[" . $taskInfo["DEAL_ID"] . "] " . $taskInfo["DEAL_TITLE"], "[" . $taskInfo["USER_ID"] . "] " . $taskInfo["USER_NAME"], $taskInfo["DATE"], $taskInfo["COMMENTS"], $taskInfo["HOURS_LOG"]), (new StyleBuilder())->build());
				}
				$writer->addRowWithStyle(array(" ", " ", " ", " ", " ", " ", " ", " ", $projectInfo["LOGS_TOTAL_HOURS"][$xml_id]), (new StyleBuilder())->setFontBold()->build());
			}
			$writer->addRow(array(" ", " ", " ", " ", " ", " ", " ", " "));
		}		
	}

	$writer->close();

	$maxRead = 1 * 1024 * 1024; 
	$fh = fopen($fileName, 'r');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . $fname . '"');

	while (!feof($fh)) {
	    echo fread($fh, $maxRead);
	    ob_flush();
	}

	unlink($fileName);
	
}
exit();

?>