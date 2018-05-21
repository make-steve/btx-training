<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(isset($_POST) && !empty($_POST))
{
	global $USER, $DB;

	$checkClienExactID = "select bucc.UF_CRM_1511437130 from b_crm_deal bcd
						  inner join b_crm_company bcc
						  on bcc.ID = bcd.COMPANY_ID
						  inner join b_uts_crm_company bucc
						  on bcc.ID = bucc.VALUE_ID where bcd.ID = " . $DB->ForSQL($_POST["ID"]);
	$resClient = $DB->Query($checkClienExactID);
	if($resClient->SelectedRowsCount() > 0)
		$data["success"] = true;
	else
		$data["success"] = false;
}
else
	$data["success"] = false;

echo json_encode($data);
?>