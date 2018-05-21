<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $USER, $DB;

if(!empty($_POST))
{
	$taskID = $_POST["taskID"];
	$setID = $_POST["set"];
	CModule::IncludeModule("tasks");

	$updateQuery = "update b_uts_tasks_task set UF_AUTO_713286985141 = '" . $DB->ForSQL($setID) . "' where VALUE_ID = '" . $DB->ForSQL($taskID) . "'";
	$success = $DB->Query($updateQuery);
	
	if($success)
		echo "true";
	else
		echo "false";
}
else
	echo "false";
