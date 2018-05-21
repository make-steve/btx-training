<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(isset($_POST) && !empty($_POST))
{
	global $USER, $DB;

	$Project = new Project;

	$data = $Project->getProjectByDealId($_POST["ID"]);
	if(!empty($data))
		$data["success"] = true;
	else
		$data["success"] = false;
	
	echo json_encode($data);
}
?>