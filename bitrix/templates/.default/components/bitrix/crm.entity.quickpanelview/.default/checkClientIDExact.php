<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(isset($_POST) && !empty($_POST))
{
	global $USER, $DB;
	$companyID = 0;

	$checkClienExactID = "select bucc." . CUF_CM_CLIENTID_EXACT . ", bcd.COMPANY_ID from b_crm_deal bcd
						  inner join b_crm_company bcc
						  on bcc.ID = bcd.COMPANY_ID
						  inner join b_uts_crm_company bucc
						  on bcc.ID = bucc.VALUE_ID where bcd.ID = " . $DB->ForSQL($_POST["ID"]);
	$resClient = $DB->Query($checkClienExactID);
	if($arClient = $resClient->Fetch())
	{
		$companyID = $arClient["COMPANY_ID"];

		if(!empty($arClient[CUF_CM_CLIENTID_EXACT]))
			$data["success"] = true;
		else
			$data["success"] = false;
	}
	else
		$data["success"] = false;

	if($data["success"] == false)
	{
		$eventName = "CLIENT_MISSING_ID";
		$arFields = array();

		if(empty($companyID))
			$arFields["LINK"] = $_SERVER["SERVER_NAME"] . "/crm/deal/show/" . $_POST["ID"] . "/";
		else
			$arFields["LINK"] = $_SERVER["SERVER_NAME"] . "/crm/company/edit/" . $companyID . "/#main_" . CUF_CM_FACTUUR_ADRES;

		$arFields["DEAL_ID"] = $_POST["ID"];

		CEvent::SendImmediate($eventName, SITE_ID, $arFields, "N", CLIENT_MISSING_ID_TEMPLATE);
	}
}
else
	$data["success"] = false;



echo json_encode($data);
?>