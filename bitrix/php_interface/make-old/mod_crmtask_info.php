<?
class mod_task_data {

 	private $taskid;
 	private $taskUfDropdown = array(
 		'TECHNOLOGY' => 'UF_AUTO_371316967807',
 		'PRODUCT_GROUP' => 'UF_AUTO_371316967808',
 	);
 	
 	private $taskUfCalc = array(
		'REVENUE' => 'UF_AUTO_276127427586',
		'THIRD_PARTY_COST' => 'UF_AUTO_834260910111',
		'COST_LIFT' => 'UF_AUTO_347562553598',
		'COST_IF' => 'UF_AUTO_775103888970',
		'ACQUISITION_COSTS' => 'UF_AUTO_132407273723',
		'YIELD' => 'UF_AUTO_197833829978',
 	);
 	private $taskUFFields = array(
 		'REFERENTIE_KLANT' => 'UF_AUTO_192582141226'
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
 		return $this->taskUfCalc[$name];
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