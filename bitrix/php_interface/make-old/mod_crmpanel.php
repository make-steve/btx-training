<?
AddEventHandler('crm', 'OnAfterCrmControlPanelBuild', array('mod_controlPanel', 'OnAfterCrmControlPanelBuild'));

class mod_controlPanel {

	function OnAfterCrmControlPanelBuild(&$items) {

		$listPage = "/crm/project/list/";
		$items[] = $stdItems['PROJECT'] = array(
			'ID' => 'PROJECT',
			'MENU_ID' => 'menu_crm_project',
			'NAME' => "Projects",
			'TITLE' => "Projects",
			'URL' => CComponentEngine::MakePathFromTemplate($listPage),
			'ICON' => 'project'
		);

        $listPage = "/crm/tasks/";
        $items[] = $stdItems['TASK'] = array(
            'ID' => 'TASK',
            'MENU_ID' => 'menu_crm_task',
            'NAME' => "Takenlijst",
            'TITLE' => "Takenlijst",
            'URL' => CComponentEngine::MakePathFromTemplate($listPage),
            'ICON' => 'task'
        );
	}
}
?>