<?
AddEventHandler('crm', 'OnAfterCrmControlPanelBuild', array('mod_controlPanel', 'OnAfterCrmControlPanelBuild'));

class mod_controlPanel {

	function OnAfterCrmControlPanelBuild(&$items) {

		global $DB, $USER;

		$listPage = "/crm/project/list/";
		$items[] = $stdItems['PROJECT'] = array(
			'ID' => 'PROJECT',
			'MENU_ID' => 'menu_crm_project',
			'NAME' => "Projects",
			'TITLE' => "Projects",
			'URL' => CComponentEngine::MakePathFromTemplate($listPage),
			'ICON' => 'project',
			'IS_ACTIVE' => false
		);

		if(!$USER->isAdmin())
		{
			$tasksSelect = "select * from b_tasks bt
						inner join m_timetable_log mtl
						on mtl.TASK_ID = bt.ID
						where bt.CREATED_BY = " . $USER->GetID() . " AND bt.STATUS != 5";
			$resTasks = $DB->Query($tasksSelect);

			if($resTasks->SelectedRowsCount() > 0)
			{
				$listPage = "/crm/tasks/";
		        $items[] = $stdItems['TASK'] = array(
		            'ID' => 'TASK',
		            'MENU_ID' => 'menu_crm_task',
		            'NAME' => "Factuuroverzicht",
		            'TITLE' => "Factuuroverzicht",
		            'URL' => CComponentEngine::MakePathFromTemplate($listPage),
		            'ICON' => 'task'
		        );
			}
		}
		else
		{
			$listPage = "/crm/tasks/";
	        $items[] = $stdItems['TASK'] = array(
	            'ID' => 'TASK',
	            'MENU_ID' => 'menu_crm_task',
	            'NAME' => "Factuuroverzicht",
	            'TITLE' => "Factuuroverzicht",
	            'URL' => CComponentEngine::MakePathFromTemplate($listPage),
	            'ICON' => 'task'
	        );
		}
	}
}
?>