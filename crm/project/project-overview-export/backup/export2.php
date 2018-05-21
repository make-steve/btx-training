<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


if(isset($_POST["export"]))
{
	$query = "select mtl.*, bt.TITLE as TASK_TITLE, mp.TITLE as PROJECT_TITLE, bcd.TITLE as DEAL_TITLE 
				from m_timetable_log mtl
				inner join b_tasks bt
				on mtl.TASK_ID = bt.ID 
				inner join m_project mp
				on mp.ID = mtl.PROJECT_ID
				inner join b_crm_deal bcd
				on bcd.ID = mtl.DEAL_ID
				where mtl.TASK_ID <> 0 ";
	
	#dateFilter
	$where = "";
	if(!empty($_POST["DATE_INSERT_from"]) && empty($_POST["DATE_INSERT_to"]))
	{
		$dateFrom = $_POST["DATE_INSERT_from"] . " 00:00:00";
		$dateTo = date('Y-m-d H:i:s');
		$where .= "AND (mtl.DATE_INSERT BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "') ";
	}
	else if(empty($_POST["DATE_INSERT_from"]) && !empty($_POST["DATE_INSERT_to"]))
	{
		$dateFrom = "0000-00-00 00:00:00";
		$dateTo = $_POST["DATE_INSERT_to"];
		$where .= "AND (mtl.DATE_INSERT BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "') ";
	}
	else if(!empty($_POST["DATE_INSERT_from"]) && !empty($_POST["DATE_INSERT_to"]))
	{
		$dateFrom = $_POST["DATE_INSERT_from"] . " 00:00:00";
		$dateTo = $_POST["DATE_INSERT_to"] . " 00:00:00";
		$where .= "AND (mtl.DATE_INSERT BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "') ";
	}
	else
	{
		$dateFrom = "0000-00-00 00:00:00";
		$dateTo = date('Y-m-d H:i:s');
		$where .= "AND (mtl.DATE_INSERT BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "') ";
	}

	#userFilter
	if(!empty($_POST["USER_ID"][0]))
		$where .= "AND (mtl.USER_ID IN (" . implode(", ", $_POST["USER_ID"]) . ")) ";
	
	$arTimeLogs = array();

	$query .= $where . "ORDER BY mtl.PROJECT_ID ASC, mtl.DATE_INSERT DESC";

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

		$dateInsert = explode(" ", $arTimeLog["DATE_INSERT"]);

		$timeLogs["USER_ID"] = $arTimeLog["USER_ID"];
		$timeLogs["USER_NAME"] = $arUsers[$arTimeLog["USER_ID"]]["NAME"];
		$timeLogs["DATE"] = $dateInsert[0];
		$timeLogs["COMMENTS"] = $arTimeLog["COMMENTS"];
		$timeLogs["HOURS_LOG"] = $arTimeLog["HOURS_LOG"];

		$arTimeLogs[$arTimeLog["PROJECT_ID"]]["TASKS"][$arTimeLog["XML_ID"]][] = $timeLogs; 
		$arTimeLogs[$arTimeLog["PROJECT_ID"]]["TASKS_TOTAL_HOURS"][$arTimeLog["XML_ID"]] += $arTimeLog["HOURS_LOG"]; 
	}

	
	function cleanData(&$str)
	{
		$str = preg_replace("/\t/", "\\t", $str);
		$str = preg_replace("/\r?\n/", "\\n", $str);
		if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
	}

	$putContent = "";
	$flag = false;

	if(!empty($arTimeLogs))
	{
		// File Name For Download
		$fileName = $_SERVER["DOCUMENT_ROOT"] . "/crm/project/project-manager-export/exportfiles/project_overview_" . date('Y_m_d_H_i_s') . ".csv";

		
		$fp = fopen($fileName, 'a');

		$csv_fields[0]   = array();
		$count_rows = 0;


		$dateFromEx = array();
		if($dateFrom == "0000-00-00 00:00:00")
			$dateFrom[0] = "From the start";
		else
			$dateFromEx = explode(" ", $dateFrom);

		$dateToEx = explode(" ", $dateTo);

		$count_rows++;

		if(!empty($_POST["DATE_INSERT_from"]) || !empty($_POST["DATE_INSERT_to"]))
			$csv_fields[$count_rows][] = $dateFrom[0] . " - " . $dateToEx[0];
		else
			$csv_fields[$count_rows][] = "Datum : All Logs";
        
        $count_rows++;
        
        if(!empty($_POST["USER_ID"]))
		{
			$csv_fields[$count_rows][] = 'Users: ';
			foreach ($arUsers as $user_id => $userInfo) 
				$csv_fields[$count_rows][] = $userInfo["NAME"] . " [" . $user_id . "]";
		}
		else
			$csv_fields[$count_rows][] = "Users: All Users";

        $count_rows++;

        $csv_fields[$count_rows][] = 'Project ID';
        $csv_fields[$count_rows][] = 'Task Number';
        $csv_fields[$count_rows][] = "Task omschrijving";
        $csv_fields[$count_rows][] = "DEAL Naam";
        $csv_fields[$count_rows][] = "Wedewerker";
        $csv_fields[$count_rows][] = "Datum";
        $csv_fields[$count_rows][] = "Omschrijving";
        $csv_fields[$count_rows][] = "Uren";
        $csv_fields[$count_rows][] = "Totaal Uren";

        $count_rows++;

        foreach($arTimeLogs as $project_id => $projectInfo) 
        {
        	foreach ($projectInfo["TASKS"] as $xml_id => $tasks) 
			{
				foreach ($tasks as $key => $taskInfo) 
				{
					$csv_fields[$count_rows][] = $project_id;
					$csv_fields[$count_rows][] = $xml_id;
					$csv_fields[$count_rows][] = $taskInfo["TASK_TITLE"];
					$csv_fields[$count_rows][] = $taskInfo["DEAL_ID"] . "] " . $taskInfo["DEAL_TITLE"];
					$csv_fields[$count_rows][] = $taskInfo["USER_ID"] . "] " . $taskInfo["USER_NAME"];
					$csv_fields[$count_rows][] = $taskInfo["DATE"];
					$csv_fields[$count_rows][] = $taskInfo["COMMENTS"];
					$csv_fields[$count_rows][] = $taskInfo["HOURS_LOG"];
					$csv_fields[$count_rows][] = " ";

					$count_rows++;
				}

				$csv_fields[$count_rows][] = " ";
				$csv_fields[$count_rows][] = " ";
				$csv_fields[$count_rows][] = " ";
				$csv_fields[$count_rows][] = " ";
				$csv_fields[$count_rows][] = " ";
				$csv_fields[$count_rows][] = " ";
				$csv_fields[$count_rows][] = " ";
				$csv_fields[$count_rows][] = " ";
				$csv_fields[$count_rows][] = $projectInfo["TASKS_TOTAL_HOURS"][$xml_id];
				$count_rows++;
			}

			$csv_fields[$count_rows][] = " ";
			$csv_fields[$count_rows][] = " ";
			$csv_fields[$count_rows][] = " ";
			$csv_fields[$count_rows][] = " ";
			$csv_fields[$count_rows][] = " ";
			$csv_fields[$count_rows][] = " ";
			$csv_fields[$count_rows][] = " ";
			$csv_fields[$count_rows][] = " ";
			$csv_fields[$count_rows][] = " ";
			$count_rows++;
        }
	}

	foreach ($csv_fields as $fields) 
		fputcsv($fp, $fields);
	
	

	fclose($fp);

	$fileNameCSV = "project_overview_" . date('Y_m_d_H_i_s') . ".csv";

	$APPLICATION->RestartBuffer();
	header("Content-Disposition: attachment; filename=\"$fileNameCSV\"");
	header("Content-Type: application/vnd.ms-excel");
	readfile($fileName);
	
}
exit();
?>