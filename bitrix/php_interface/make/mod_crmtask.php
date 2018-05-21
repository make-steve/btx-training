<?
AddEventHandler('tasks', 'OnTaskAdd', array('mod_tasks', 'OnTaskAdd'));
AddEventHandler('tasks', 'OnTaskUpdate', array('mod_tasks', 'OnTaskUpdate'));
AddEventHandler('tasks', 'OnBeforeTaskAdd', array('mod_tasks_before', 'OnBeforeTaskAdd'));
AddEventHandler('tasks', 'OnBeforeTaskUpdate', array('mod_tasks_before', 'OnBeforeTaskUpdate'));
AddEventHandler('tasks', 'OnBeforeTaskDelete', array('mod_tasks_before', 'OnBeforeTaskDelete'));
AddEventHandler('crm', 'OnBeforeActivityDelete', array('mod_crm_act_before', 'OnBeforeActivityDelete'));

class mod_tasks_before {

	function OnBeforeTaskAdd(&$arFields) {

		$modTaskData = new mod_task_data();
		$arCalFields = $modTaskData->getCaclFields();

		mod_tasks_before::modifyValue($arCalFields, $arFields);
		mod_tasks_before::modifyUrenMoreDetail($arFields);

		return $arFields;
	}

	function OnBeforeTaskUpdate($id, &$arFields) {
		$modTaskData = new mod_task_data();
		$arCalFields = $modTaskData->getCaclFields();

		mod_tasks_before::modifyValue($arCalFields, $arFields);
		mod_tasks_before::modifyUrenMoreDetail($arFields);

		return $arFields;
	}

	function modifyValue($arCalFields, &$arFields) {

		foreach($arCalFields as $fields) {
			$arFields[$fields] = cleanMoney($arFields[$fields]);
		}

		return $arFields;
	}

	function modifyUrenMoreDetail(&$arFields) {

		$modTaskData = new mod_task_data();
		$urenMoreDetailField = $modTaskData->getSpecificFieldByName('COST_IF_MORE_MULTI');

		if(isset($arFields[$urenMoreDetailField]) && !empty($arFields[$urenMoreDetailField])) {

			foreach($arFields[$urenMoreDetailField] as $key => $val) {

				$fval = $val;
				if(isset($arFields[$urenMoreDetailField."_DROPDOWN"][$key]) && $arFields[$urenMoreDetailField."_DROPDOWN"][$key] != "")
					$fval = $arFields[$urenMoreDetailField."_DROPDOWN"][$key]."-".$val;

				$arFields[$urenMoreDetailField][$key] = $fval;
			}
		}

		return $arFields;
	}

	function OnBeforeTaskDelete($ID, $arTask) {

		if(isset($arTask['UF_CRM_TASK']) && !empty($arTask['UF_CRM_TASK'])) {

			foreach($arTask['UF_CRM_TASK'] as $crmTask) {

				$arEntityID = explode("_", $crmTask);
				$entityType = $arEntityID[0];
				$entityId = $arEntityID[1];

				$canDeleteTask = isTaskAllowed2Delete(intval($entityId), intval($ID));

				if(!$canDeleteTask)
					return false;
			}
		}
	}

}

class mod_crm_act_before {

	function OnBeforeActivityDelete($ID) {

		$__POST = $_POST;
		if((isset($__POST['OWNER_ID']) && intval($__POST['OWNER_ID']) > 0) && $__POST['OWNER_TYPE'] == 'DEAL') {

			$entityType = $__POST['OWNER_TYPE'];
			$entityId = intval($__POST['OWNER_ID']);

			$canDeleteTask = isTaskAllowed2Delete(intval($entityId), intval($ID));

			if(!$canDeleteTask) {

				echo "<script language=javascript>alert('This task cant be deleted because the task is not empty.')</script>";
				
				return false;
			}
		}
	}
}

class mod_tasks {

	function OnTaskAdd($ID, &$arFields) {

		$__POST = $_POST;
		mod_tasks::performTaskSetData($arFields, $__POST, 'ADD');
	}

	function OnTaskUpdate($ID, &$arFields, &$arTaskCopy) {
		$__POST = $_POST;
		mod_tasks::performTaskSetData($arFields, $__POST, 'EDIT');
	}

	function performTaskSetData($arFields, $__POST, $type = "") {

		global $DB;
		$taskId = $arFields['ID'];
		$typeId = 3;
		$isEntityExist = false;
		$arAllowedEntityType = array('D' => '2');

		// check if the task is fro crm and has custom fields
		if(isset($arFields['UF_CRM_TASK']) && !empty($arFields['UF_CRM_TASK']) && (isset($__POST['HAS_CUSTOM_FIELDS']) && $__POST['HAS_CUSTOM_FIELDS'] == 'Y')) {

			foreach($arFields['UF_CRM_TASK'] as $crmTask) {

				$arEntityID = explode("_", $crmTask);
				$entityType = $arEntityID[0];
				$entityId = $arEntityID[1];

				// check if the task is for deal
				if(array_key_exists($entityType, $arAllowedEntityType)) {

					$entityTypeId = 0;
					$dealAcqKosten = 0;
					switch($entityType) {

						case "D":
							$crmDeal = new CCrmDeal(false);
							$isEntityExist = $crmDeal->Exists(intval($entityId));
							$entityTypeId = $arAllowedEntityType[$entityType];
							break;
						default:
							break;
					}

					if($isEntityExist) {

                        // add entry to budget, actual and diif
                        $mod_project_task = new mod_project_task(intval($entityId), intval($taskId));
                        $mod_project_task_info = new mod_task_data(intval($taskId));

                        if($type == "ADD") {
							$mod_project_task->addEntries();
							$mod_project_task->getAllTaskData();
						}

						$arCalcUf = $mod_project_task_info->getCaclFields();

						/*if($arFields[$arCalcUf['ACQUISITION_COSTS']] != '')
							$mod_project_task->setTaskData('ACQUISITION_COSTS', $arFields[$arCalcUf['ACQUISITION_COSTS']], 'BUDGET');*/

						if($arFields[$arCalcUf['COST_IF']] != '')
							$mod_project_task->setTaskData('COST_IF', $arFields[$arCalcUf['COST_IF']], 'BUDGET');

						if($arFields[$arCalcUf['THIRD_PARTY_COST']] != '')
							$mod_project_task->setTaskData('THIRD_PARTY_COST', $arFields[$arCalcUf['THIRD_PARTY_COST']], 'BUDGET');

						if($arFields[$arCalcUf['COST_LIFT']] != '')
							$mod_project_task->setTaskData('COST_LIFT', $arFields[$arCalcUf['COST_LIFT']], 'BUDGET');

						if($arFields[$arCalcUf['REVENUE']] != '')
							$mod_project_task->setTaskData('REVENUE', $arFields[$arCalcUf['REVENUE']], 'BUDGET');

					}
				}
			}
		}
	}
}

function getUrenDropdown() {
	global $DB;

	$dbGetUrenDropdown = 'SELECT * from `m_uren_breakdown`';
    $rsGetUrenDropdown = $DB->Query($dbGetUrenDropdown, false, "File: ".__FILE__."<br>Line: ".__LINE__);

    $arReturn = array("" => "-select-");
    while($arGetUrenDropdown = $rsGetUrenDropdown->fetch()) {

    	$arReturn[$arGetUrenDropdown['code']] =  $arGetUrenDropdown['value'];
    }

    return $arReturn;
}

function getUrenDropdownByCode($code) {
	global $DB;

	$dbGetUrenDropdown = 'SELECT * from `m_uren_breakdown` WHERE `code` = "'.$code.'"';
    $rsGetUrenDropdown = $DB->Query($dbGetUrenDropdown, false, "File: ".__FILE__."<br>Line: ".__LINE__);

    $arReturn = array();
    if($arGetUrenDropdown = $rsGetUrenDropdown->fetch()) {

    	$arReturn = array('code' => $arGetUrenDropdown['code'], 'value' => $arGetUrenDropdown['value']);
    }

    return $arReturn;
}
?>