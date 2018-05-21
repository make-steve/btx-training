<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $USER, $DB;

if(!empty($_POST))
{
	$projectID = $_POST["projectID"];
	$dealID = $_POST["dealID"];
	$userID = $USER->GetID();
	$showTasks = $_POST["showTasks"];

	if(strlen($showTasks) > 1 || empty($showTasks))
		$showTasks = "Y";

	$query = "";
	$checkExists = "select ID from m_projectdeal_detail_settings where PROJECT_ID = '" . $DB->ForSQL($projectID) . "' and DEAL_ID = '" . $DB->ForSQL($dealID) . "' and USER_ID = '" . $DB->ForSQL($userID) . "';" ;

	$resProjectDeal = $DB->Query($checkExists);
	if($resProjectDeal->SelectedRowsCount() > 0)
		$query = "update m_projectdeal_detail_settings set SHOW_TASKS = '" . $DB->ForSQL($showTasks) . "' where PROJECT_ID = '" . $DB->ForSQL($projectID) . "' and DEAL_ID = '" . $DB->ForSQL($dealID) . "'";
	else
		$query = "insert into m_projectdeal_detail_settings (PROJECT_ID, DEAL_ID, USER_ID, SHOW_TASKS) values ('" . $DB->ForSQL($projectID) . "', '" . $DB->ForSQL($dealID) . "', '" . $DB->ForSQL($userID) . "', '" . $DB->ForSQL($showTasks) . "');";

	$DB->Query($query);

	echo "true";
}
else
	echo "false";
