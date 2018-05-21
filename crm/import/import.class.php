<?php
class importFile {

	function __construct() {

		// code here

		CModule::IncludeModule('crm');
		CModule::IncludeModule('tasks');
	
	}

	public function load($dir, $arPage, $perPage = 500) {

		if($dir == '' || !file_exists($dir)) {
			echo 'File not found';
			return;
		}

		$arDataTmp = $this->readFile($dir);
		$arData = $arDataTmp['rowdata'];


		$page = $arPage;
		$total = count( $arData );
		$limit = $perPage;
		$totalPages = ceil( $total/ $limit );

		//$page = max($page, 1);
		//$page = min($page, $totalPages);
		$offset = ($page - 1) * $limit;

		if( $offset < 0 ) $offset = 0;

		$finArData = array_slice( $arData, $offset, $limit );
		// paginate

		return array('header' => $arDataTmp['header'], 'rowdata' => $finArData);
	}

	public function showhtml($arHeader, $arDatas, $isExporting) {

		$html = "";

		if(!$isExporting) {

			$html .= "<meta http-equiv='Content-Type' content='text/html;charset=ISO-8859-1'>";
			$html .= "<meta charset='UTF-8'>";
			$html .= "<form method='POST' action='?action=import&page=1'>";
			$html .= "<input type='submit' value='import' name='import'/>";
			$html .= "</form>";
		}
		$html .= "<table width='100%' border='1'>";
			
			$html .= "<tr>"; // header
				foreach($arHeader as $key => $head) {
					$html .= "<th>".$head." [ ". $key . " ] "."</th>";
				}
			$html .= "</tr>";

			foreach($arDatas as $key => $arData) {

				$html .= "<tr>";
					foreach($arData as $_data) { $html .= "<td>".iconv("UTF-8", "ISO-8859-1", $_data)."</td>"; }
				$html .= "</tr>";
			}
		$html .= "</table>";

		return $html;
	}

	public function getDropdownUF($FIELD_NAME, $MODULE) {

		global $USER_FIELD_MANAGER;
		$langID = 's1';
 		$taskUFDpValue = $USER_FIELD_MANAGER->GetUserFields($MODULE, 0, $langID);

 		$arDpList = array();
 		foreach($taskUFDpValue as $taskDp) {

 			if($taskDp['FIELD_NAME'] == $FIELD_NAME) {

 				$enumEntity = new \CUserFieldEnum();
				$dbResultEnum = $enumEntity->GetList(
					array('SORT' => 'ASC'),
					array('USER_FIELD_ID' => intval($taskDp['ID']))
				);
				
				$listItems = array();
				while($enum = $dbResultEnum->Fetch()) {
					$listItems[$enum['VALUE']] = $enum['ID'];
				}

				$arDpList[$taskDp['FIELD_NAME']] = $listItems;

				break;
 			}
 		}

 		return $arDpList[$FIELD_NAME];
 	}

 	public function getStatus($FIELD_ID) {

 		$arStatus = array();
 		$arStatuses = CCrmStatus::GetStatusList($FIELD_ID);

 		foreach($arStatuses as $key => $status) {

 			$arStatus[$status] = $key;
 		}

 		return $arStatus;
 	}

	private function readFile($csv_path, $delimited = ',')
	{
		header('Content-Type: text/html; charset=UTF-8');
		$count = 1;
		$row_cnt = 0;
		$header = array();
		ini_set('auto_detect_line_endings', true);
		if (($handle = fopen($csv_path, "r")) !== FALSE) {

			$rowData = array();
		    while (($data = fgetcsv($handle, 4000, $delimited)) !== FALSE) {

		    	$inEnc = "Windows-1252";
		    	$outEnc = "UTF-8";
		    	array_walk($data, function (&$str) use ($inEnc, $outEnc) {
	                $str = mb_convert_encoding($str, $outEnc, $inEnc);
	            });

		    	if(++$row_cnt == 1) {
		    		$header = $data;
		    		continue;
		    	}

		        $rowData[] = $data;
		    }
		    ini_set('auto_detect_line_endings',FALSE);
		    fclose($handle);

		    return array('header' => $header, 'rowdata' => $rowData);
		}
	}

	public function consQueryField($arFields) {

		$_arFields = array();
		global $DB;
		foreach($arFields as $key => $value) {

			$_arFields[] = "`".$key."` = '".$DB->ForSQL($value)."'";
		}

		return $_arFields;
	}

	public function getCompanyByID($id) {

		$Project = new Project;
		$arCompanyInsert = array();
		$companyField = $Project->companyFields;
        $arCompany = $Project->getCompanyByIds(intval($id), $companyField);

        $_companyUFs = $Project->companyUF;
        $arCompanyUF = $Project->getUserFieldInfos(array_keys($_companyUFs), 'CRM_COMPANY');
        $arCompany['UF'] = current($Project->getUserFieldValues('company', $arCompanyUF, array(intval($id))));

        if(!empty($arCompany) && intval($id) > 0) {
	        $arCompanyInsert = array(
	            'COMPANY_ID' => intval($id),
	            'COMPANY_NAME' => $arCompany['TITLE'],
	            'UF' => $arCompany['UF'],
	        );
		}

        return $arCompanyInsert;
	}

	public function getContactByID($id) {

		$Project = new Project;
		$arContactInsert = array();
		$honorificList = CCrmStatus::GetStatusList('HONORIFIC');

        $contactField = $Project->contactFields;
        $arContact = $Project->getContactByIds(intval($id), $contactField);

        $_contactUFs = $Project->contactUF;
        $arContactUF = $Project->getUserFieldInfos(array_keys($_contactUFs), 'CRM_CONTACT');
        $arContact['UF'] = current($Project->getUserFieldValues('contact', $arContactUF, array($id)));

        if(!empty($arContact) && intval($id) > 0) {
	        $arContactInsert = array(
	            'CONTACT_ID' => $id,
	            'SALUTATION' => $honorificList[$arContact["HONORIFIC"]],
	            'LAST_NAME' => $arContact['LAST_NAME'],
	            'MIDDLE_NAME' => $arContact['SECOND_NAME'],
	            'NAME' =>  $arContact['NAME'],
	            'UF' => $arContact['UF'],
	            'TELEPHONE' => $arContact["FM"]["PHONE"][0]["VALUE"],
	            'EMAIL' => $arContact["FM"]["EMAIL"][0]["VALUE"],
	        );
		}

        return $arContactInsert;
	}

	public function getProjectPrimDeal($pid) {

		global $DB;

		$primaryDeal = 0;
		$dbGetPrimaryDeal = "SELECT `PRIMARY_DEAL` FROM ".
						"`m_project` ".
						"WHERE ".
							"`ID` = ".intval($pid).";";

		$rsGetPrimaryDeal = $DB->Query($dbGetPrimaryDeal, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($arGetPrimaryDeal = $rsGetPrimaryDeal->Fetch()) {

			$primaryDeal = $arGetPrimaryDeal['PRIMARY_DEAL'];
		}

		return $primaryDeal;
	}

	public function getProjectByID($pid) {

		global $DB;

		$project = array();
		$dbGetProject = "SELECT * FROM ".
						"`m_project` ".
						"WHERE ".
							"`ID` = ".intval($pid).";";

		$rsGetProject = $DB->Query($dbGetProject, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($arGetProject = $rsGetProject->Fetch()) {

			$project = $arGetProject;
		}

		return $project;
	}

	function isDealExist($id) {

		global $DB;
		$isExist = false;
		$dbGetDeal = "SELECT `ID` FROM ".
						"`b_crm_deal` ".
						"WHERE ".
							"`ID` = ".intval($id).";";

		$rsGetDeal = $DB->Query($dbGetDeal, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($arGetDeal = $rsGetDeal->Fetch()) {
			$isExist = true;
		}

		return $isExist;
	}

	function getRealDealID($tmpID) {

		global $DB;
		$realId = "";
		$dbGetDealID = "SELECT `ID` FROM ".
						"`m_exact_deal` ".
						"WHERE ".
							"`EXACT_ID` = ".intval($tmpID).";";

		$rsGetDealID = $DB->Query($dbGetDealID, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($arGetDealID = $rsGetDealID->Fetch()) {
			$realId = $arGetDealID['DEAL_ID'];
		}

		return $realId;
	}

	function doSaveLog($logid, $text) {

		$rootDir = $_SERVER['DOCUMENT_ROOT']."/crm/import/logs/";
		$file = $rootDir . $logid . ".txt";	

		if(!file_exists($file)) 
			fopen($file, "w") or die("Unable to open file!");
		
		$current = file_get_contents($file);
		$current .= $text."\n_________________________________________\n\n";
		file_put_contents($file, $current);
	}

	function cleanMoney($money) {

		return str_replace(array('.', ','), array('', '.'), $money);
	}
}
?>