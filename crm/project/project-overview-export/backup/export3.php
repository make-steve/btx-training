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
	$query = "select mtl.*, bt.TITLE as TASK_TITLE, mp.TITLE as PROJECT_TITLE, bcd.TITLE as DEAL_TITLE 
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

	$_POST["USER_ID"] = array_filter($_POST["USER_ID"]);

	#userFilter
	if(!empty($_POST["USER_ID"]))
		$where .= "AND (mtl.USER_ID IN (" . implode(", ", $_POST["USER_ID"]) . ")) ";

	$arTimeLogs = array();
	$query .= $where . "ORDER BY mtl.PROJECT_ID ASC, mtl.DATE_LOG DESC";


	$arUsers = array();
	$resTimeLog = $DB->Query($query);
	while($arTimeLog = $resTimeLog->Fetch())
	{
		$arTimeLogs[$arTimeLog["PROJECT_ID"]]["ID"] = $arTimeLog["ID"]; 
		$arTimeLogs[$arTimeLog["PROJECT_ID"]]["TITLE"] = $arTimeLog["PROJECT_TITLE"]; 


		$timeLogs = array();
		$timeLogs["TASK_NUMBER"] = $arTimeLog["XML_ID"];
		$timeLogs["TASK_TITLE"] = $arTimeLog["TASK_TITLE"];
		$timeLogs["DEAL_ID"] = $arTimeLog["DEAL_ID"];
		$timeLogs["DEAL_TITLE"] = $arTimeLog["DEAL_TITLE"];

		if(!array_key_exists($arTimeLog["USER_ID"], $arUsers))
		{
			$rsUser = CUser::GetByID($arTimeLog["USER_ID"]);
			$arUser = $rsUser->Fetch();

			if(!empty($arUser["LAST_NAME"]))
				$userName = $arUser["LAST_NAME"] . ", ";

			$userName .= implode(" ", Array($arUser["NAME"], $arUser["SECOND_NAME"]));

			$arUsers[$arTimeLog["USER_ID"]]["ID"] = $arTimeLog["USER_ID"];
			$arUsers[$arTimeLog["USER_ID"]]["NAME"] = $userName;
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
	$fileName = $_SERVER["DOCUMENT_ROOT"] . "/crm/project/project-manager-export/exportfiles/project_overview_" . $dateName . ".xlsx";
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

	$userString = '';
	if(!empty($_POST["USER_ID"]))
	{
		$userString .= 'Users: ';

		$getUser = new CUser;
		foreach ($_POST["USER_ID"] as $key => $value) 
		{
			$resUser = $getUser->GetByID($value);
			if($arUser = $resUser->Fetch())
				$userString .= $arUser["NAME"] . " [" . $value . "]";
		}			
		$userString .= '|';
	}
	else
		$userString = 'Users: All Users';

	

	

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