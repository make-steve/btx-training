<?
AddEventHandler('tasks', 'OnTaskAdd', array('mod_tasks', 'OnTaskAdd'));
AddEventHandler('tasks', 'OnTaskUpdate', array('mod_tasks', 'OnTaskUpdate'));
AddEventHandler('tasks', 'OnBeforeTaskAdd', array('mod_tasks_before', 'OnBeforeTaskAdd'));
AddEventHandler('tasks', 'OnBeforeTaskUpdate', array('mod_tasks_before', 'OnBeforeTaskUpdate'));

class mod_tasks_before {

	function OnBeforeTaskAdd(&$arFields) {

		$modTaskData = new mod_task_data();
		$arCalFields = $modTaskData->getCaclFields();

		mod_tasks_before::modifyValue($arCalFields, $arFields);
		return $arFields;
	}

	function OnBeforeTaskUpdate($id, &$arFields) {
		$modTaskData = new mod_task_data();
		$arCalFields = $modTaskData->getCaclFields();

		mod_tasks_before::modifyValue($arCalFields, $arFields);
		return $arFields;
	}

	function modifyValue($arCalFields, &$arFields) {

		foreach($arCalFields as $fields) {
			$arFields[$fields] = cleanMoney($arFields[$fields]);
		}

		return $arFields;
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
?>