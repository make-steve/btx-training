<?
class mod_task_data {

 	private $taskid;
 	private $taskUfDropdown = array(
 		'TECHNOLOGY' => CUF_T_TECHNOLOGY,
        'PRODUCT_GROUP' => CUF_T_PRODUCTGROUP,
 		'TASK_TYPE' => CUF_T_TYPE,
 	);

    private $types = array('regie', 'vast', 'intern');
 	
 	private $taskUfCalc = array(
		'REVENUE' => CUF_T_INKOMSTEN,
		'THIRD_PARTY_COST' => CUF_T_KOSTEN_DERDEN,
		'COST_LIFT' => CUF_T_KOSTEN_LIFT,
		'COST_IF' => CUF_T_KOSTEN_UREN_IF,
		'ACQUISITION_COSTS' => CUF_T_COST_ACQUISITION,
		'YIELD' => CUF_T_RENDEMENT,
 	);
 	private $taskUFFields = array(
 		'REFERENTIE_KLANT' => CUF_T_REF_CLIENT,
 		'COST_IF_MORE_MULTI' => CUF_T_KOSTEN_UREN_BREAKDOWN_ID,
 	);
 	private $_USER_FIELD_MANAGER;

 	function __construct($id) {

 		global $USER_FIELD_MANAGER;
 		$this->taskid = $id;
 		$this->_USER_FIELD_MANAGER = $USER_FIELD_MANAGER;
 	}

 	public function getTaskInfo($id) {

 		/*if(CModule::IncludeModule('task')) {

 			$arTask = CTasks::GetByID($id);
 			echo '<pre>';
 				print_r($arTask);
 			echo '</pre>';
 		}*/
 	}

 	public function getUF() {

 		return $this->taskUfDropdown;
 	}

 	public function getTaskUFFields() {

 		return $this->taskUFFields;
 	}

 	public function getDropdownUF() {

 		$ufDpFields = $this->taskUfDropdown;
 		$this->taskUFDpValue = $this->_USER_FIELD_MANAGER->GetUserFields("TASKS_TASK", 0, $langID);

 		$arDpList = array();
 		foreach($this->taskUFDpValue as $taskDp) {

 			if(in_array($taskDp['FIELD_NAME'], $ufDpFields)) {

 				$enumEntity = new \CUserFieldEnum();
				$dbResultEnum = $enumEntity->GetList(
					array('SORT' => 'ASC'),
					array('USER_FIELD_ID' => intval($taskDp['ID']))
				);
				
				$listItems = array();
				while($enum = $dbResultEnum->Fetch())
				{
					$listItems[$enum['XML_ID']] = $enum['ID'];
				}

				$arDpList[$taskDp['FIELD_NAME']] = $listItems;
 			}
 		}

 		return $arDpList;
 	}

 	public function getCaclFields() {
 		return $this->taskUfCalc;
 	}

 	public function getSpecificFieldByName($name) {
 		return ($this->taskUfCalc[$name] != "") ? $this->taskUfCalc[$name] : $this->taskUFFields[$name];
 	}

 	public function getTaskFieldValue($taskid) {

 		$taskFieldValue = $this->_USER_FIELD_MANAGER->GetUserFields("TASKS_TASK", intval($taskid), $langID);
 		return $taskFieldValue;
 	}

 	public function getDefaultKlant($dealid) {

 		global $DB;
 		$_dealId = intval($dealid);
 		$defaultReferentieKlant = "";
 		// get first task from deal

 		$rsGetFirstTask = $DB->Query("SELECT `ID`, `ASSOCIATED_ENTITY_ID` FROM `b_crm_act` WHERE `OWNER_ID` = ".intval($_dealId)." ORDER BY `CREATED` ASC;");
		
	    if($arGetFirstTask = $rsGetFirstTask->Fetch()) {

	    	$fieldValue = $this->getTaskFieldValue($arGetFirstTask['ASSOCIATED_ENTITY_ID']);
	    	$defaultReferentieKlant = $fieldValue[$this->taskUFFields['REFERENTIE_KLANT']]['VALUE'];
	    }

	    return $defaultReferentieKlant;
 	}
 }
?>