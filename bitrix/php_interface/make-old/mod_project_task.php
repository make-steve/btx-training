<?php

class mod_project_task {

	private $tbl_budget = "m_project_task_budget";
	private $tbl_actual = "m_project_task_actual";
	private $tbl_diff = "m_project_task_diff";
	private $dealid;
	private $taskid;
	private $_DB;
	private $allData;

	function __construct($deal, $task, $hasTask = true) {
		//code here	
		global $DB;
		$this->dealid = intval($deal);
		$this->taskid = intval($task);
		$this->_DB = $DB;

		if(($this->dealid > 0 && $this->taskid > 0) || !$hasTask) {
			
			$this->getAllTaskData();
		}
	}

	function setAllData($_allData) {

		$this->allData = $_allData;
	}

	function getAllData() {

		return $this->allData;
	}

	function calcFields() {
		return array(
			'ACQUISITION_COSTS', // Acquisitie kosten
			'COST_IF', // Kosten uren IF
			'THIRD_PARTY_COST', // Kosten derden
			'COST_LIFT', // Kosten LIFT
			'TOTAL_COSTS', // Totale kosten
			'REVENUE', // Inkomsten
			'RESCUE', // Rendement
			'BILLED', // Gefactureerd
			'PAID' //Betaald
		);
	}

	function totalCostFields() {
		return array(
			0 => 'ACQUISITION_COSTS',
			1 => 'COST_IF',
			2 => 'THIRD_PARTY_COST',
			3 => 'COST_LIFT',
			'TOTAL' => 'TOTAL_COSTS',
		);
	}

	function totalRescueFields() {
		return array(
			'REVENUE' => 'REVENUE',
			'TOTAL_COSTS' => 'TOTAL_COSTS',
			'TOTAL' => 'RESCUE',
		);
	}

	function calcItems() {

		return array(
			'b' => "BUDGET",
			'a' => "ACTUAL",
			'd' => "DIFF",
		);
	}

	function calcTables() {

		return array(
			"BUDGET" => $this->tbl_budget,
			"ACTUAL" => $this->tbl_actual,
			"DIFF" => $this->tbl_diff,
		);
	}

	function calcResult() {
		return "DIFF";
	}

	function prepareGet4DB() {

		$arPrep = array();
		foreach($this->calcItems() as $key => $ar) {

			$arPrep[] = $key . ".`". $ar . "_ID`";

			foreach($this->calcFields() as $field)
				$arPrep[] = $key . ".`" . $field . "` " . $ar . "_" . $field;
		}

		return $arPrep;
	}

	function prepareUpdate4DB($arData) {

		$arReturn = array();
		foreach($arData as $key => $data) {
			$arReturn[] = "`".$key."` = '".$this->_DB->ForSql($data)."'";
		}

		return implode(" , ", $arReturn);
	}

	function itemResult2Array($arData, $dataItem) {

		$arResultData = array();
		$arCaclFields = array();
		foreach($this->calcItems() as $ar) {

			$arResultData[$ar."_ID"] = $arData[$ar."_ID"];
			$arCaclFields[$ar."_".$dataItem] = $arData[$ar."_".$dataItem];
		}

		$arResultData['CALCULATE'] = $arCaclFields;

		return $arResultData;
	}

	function addEntries() {

		$this->addData2Table($this->tbl_budget);
		$this->addData2Table($this->tbl_actual);
		$this->addData2Table($this->tbl_diff);
	}

	function addData2Table($tbl) {

		$tableName = $tbl;
		$dealId = $this->dealid;
		$taskId = $this->taskid;

		$addEntry = "INSERT INTO ".
							"`".$tableName."` ".
						"SET ".
							"`DEAL_ID` = ".$this->_DB->ForSql($dealId).", ".
							"`TASK_ID` = '".$this->_DB->ForSql($taskId)."' ";

		$this->_DB->Query($addEntry, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	function addAcq2Table($tbl) {

		$tableName = $tbl;
		$dealId = $this->dealid;
		$taskId = 0;

		$addEntry = "INSERT INTO ".
							"`".$tableName."` ".
						"SET ".
							"`DEAL_ID` = ".$this->_DB->ForSql($dealId).", ".
							"`TASK_ID` = '".$this->_DB->ForSql($taskId)."' ";

		$this->_DB->Query($addEntry, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	function setTaskData($dataItem, $value, $prefix, $setonly = false) {

		// get data first
		$arTaskData = $this->setTaskDataValue($dataItem, $value, $prefix);

		// calculate based on new value
		$arNewValue = $this->calculateDiff($dataItem, $prefix);

		if(!$setonly) {

			// calculate All
			$this->calculateAll();
		}

		// Save changes
		$this->saveTaskData();
	}

	function setTaskDataValue($dataItem, $value, $prefix) {

		if(trim($dataItem) == "") return;

		$_allData = $this->allData;
		if(array_key_exists($dataItem, $_allData[$prefix])) {
			$_allData[$prefix][$dataItem] = doubleVal($value);

			if($dataItem == 'REVENUE')
				$_allData[$prefix]["BILLED"] = doubleVal($value);
		}

		$this->allData = $_allData;
	}

	function getAllTaskData() {

		$this->allData = array();
		$dealId = $this->dealid;
		$taskId = $this->taskid;

		$arResult = array();
		$arSelect = $this->prepareGet4DB($dataItem);
		$dbGetData = "SELECT ".
							implode(" , ", $arSelect)." ".
						"FROM `".$this->tbl_budget."` b ".
						"LEFT JOIN `".$this->tbl_actual."` a ".
							"ON b.`DEAL_ID` = a.`DEAL_ID` AND b.`TASK_ID` = a.`TASK_ID` ".
						"LEFT JOIN `".$this->tbl_diff."` d ".
							"ON b.`DEAL_ID` = d.`DEAL_ID` AND b.`TASK_ID` = d.`TASK_ID` ".
						"WHERE ".
							"b.`DEAL_ID` = ".$this->_DB->ForSql($dealId)." AND ".
							"b.`TASK_ID` = '".$this->_DB->ForSql($taskId)."' ";

		$rsGetData = $this->_DB->Query($dbGetData, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($arGetData = $rsGetData->fetch()) {

			//$arResult = $this->itemResult2Array($arGetData, $dataItem);
			foreach($this->calcItems() as $calc) {

				foreach($this->calcFields() as $fields) {

					$arResult[$calc][$fields] = $arGetData[$calc . "_" . $fields];
				}
			}
		}

		$this->allData = $arResult;
	}

	function getAcquisitieKosten() {

		$this->allData = array();
		$dealId = $this->dealid;
		$taskId = 0;

		$arResult = array();
		$arSelect = $this->prepareGet4DB($dataItem);
		$dbGetData = "SELECT ".
							implode(" , ", $arSelect)." ".
						"FROM `".$this->tbl_budget."` b ".
						"LEFT JOIN `".$this->tbl_actual."` a ".
							"ON b.`DEAL_ID` = a.`DEAL_ID` AND b.`TASK_ID` = a.`TASK_ID` ".
						"LEFT JOIN `".$this->tbl_diff."` d ".
							"ON b.`DEAL_ID` = d.`DEAL_ID` AND b.`TASK_ID` = d.`TASK_ID` ".
						"WHERE ".
							"b.`DEAL_ID` = ".$this->_DB->ForSql($dealId)." AND ".
							"b.`TASK_ID` = '".$this->_DB->ForSql($taskId)."' ";

		$rsGetData = $this->_DB->Query($dbGetData, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($arGetData = $rsGetData->fetch()) {

			//$arResult = $this->itemResult2Array($arGetData, $dataItem);
			foreach($this->calcItems() as $calc) {

				foreach($this->calcFields() as $fields) {

					$arResult[$calc][$fields] = $arGetData[$calc . "_" . $fields];
				}
			}
		}

		return $arResult;
	}

	function getAllTaskDataByTable($table) {

		if(trim($dataItem) == "") return;

		$dealId = $this->dealid;
		$taskId = $this->taskid;

		$arResult = array();
		$dbGetData = "SELECT * ".
						"FROM `".$table."` b ".
						"WHERE ".
							"b.`DEAL_ID` = ".$this->_DB->ForSql($dealId)." AND ".
							"b.`TASK_ID` = '".$this->_DB->ForSql($taskId)."' ";

		$rsGetData = $this->_DB->Query($dbGetData, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($arGetData = $rsGetData->fetch()) {
			$arResult = $arGetData;
		}

		return $arResult;
	}

	function save2Table($table, $rowsVal) {

		$tableName = $table;
		$dealId = $this->dealid;
		$taskId = $this->taskid;

		$updateEntry = "UPDATE ".
							"`".$tableName."` ".
						"SET ".$rowsVal." ".
						"WHERE ".
							"`DEAL_ID` = ".$this->_DB->ForSql($dealId)." AND ".
							"`TASK_ID` = '".$this->_DB->ForSql($taskId)."' ";

		$this->_DB->Query($updateEntry, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	function saveTaskData($arData, $dataItem) {

	
		$dealId = $this->dealid;
		$taskId = $this->taskid;

		$arCalcTables = $this->calcTables();
		$_allData = $this->allData;

		foreach($_allData as $key => $data) {

			$updateVal = $this->prepareUpdate4DB($data);
			$this->save2Table($arCalcTables[$key], $updateVal);
		}
	}

	function calculateDiff($dataItem, $prefix) {

		$arCalcItems = $this->calcItems();
		$_allData = $this->allData;

		$budget = doubleVal($_allData['BUDGET'][$dataItem]);
		$actual = doubleVal($_allData['ACTUAL'][$dataItem]);

		// calculate
		$diff = $budget - $actual;
		$_allData['DIFF'][$dataItem] = doubleVal($diff);

		$this->allData = $_allData;
	}

	function calculateAll() {

		// Calculate total cost
		$this->calculate_totalCost();

		// Calculate Rendement
		$this->calculate_rescue();
	}

	function calculate_totalCost() {

		$_allData = $this->allData;
		$arCalcTotalCost = $this->totalCostFields();

		foreach($_allData as $dataKey => $data) {

			$total = 0;
			foreach($arCalcTotalCost as $fieldKey => $field) {
				
				if($fieldKey !== "TOTAL") {
					$total = $total + doubleVal($data[$field]);
				}
			}

			$_allData[$dataKey][$arCalcTotalCost['TOTAL']] = $total;
		}
		
		$this->allData = $_allData;
	}

	function calculate_rescue() {

		$_allData = $this->allData;
		$arCalcRescue = $this->totalRescueFields();

		foreach($_allData as $dataKey => $data) {

			$total = 0;
			$rev = doubleVal($data[$arCalcRescue['REVENUE']]);
			$cost = doubleVal($data[$arCalcRescue['TOTAL_COSTS']]);

			$total = $rev - $cost;
			
			$_allData[$dataKey][$arCalcRescue['TOTAL']] = $total;
		}
		
		$this->allData = $_allData;
	}

	function setUpOrigValue($origData) {

		$_allData = $this->allData;

		foreach($_allData['BUDGET'] as $budgetKey => $budget) {

			if(array_key_exists($budgetKey, $origData))
				$_allData['BUDGET'][$budgetKey] = $origData[$budgetKey];
		}

		return $_allData;
	}
}
?>