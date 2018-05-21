<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Лента CRM");
$APPLICATION->SetPageProperty("BodyClass", " page-one-column");
if(CModule::IncludeModule("crm") && CCrmPerms::IsAccessEnabled()):

	$currentUserPerms = CCrmPerms::GetCurrentUserPermissions();
	$canEdit = CCrmLead::CheckUpdatePermission(0, $currentUserPerms)
		|| CCrmContact::CheckUpdatePermission(0, $currentUserPerms)
		|| CCrmCompany::CheckUpdatePermission(0, $currentUserPerms)
		|| CCrmDeal::CheckUpdatePermission(0, $currentUserPerms);

	$APPLICATION->IncludeComponent(
		"bitrix:crm.control_panel",
		"",
		array(
			"ID" => "STREAM",
			"ACTIVE_ITEM_ID" => "STREAM",
			"PATH_TO_COMPANY_LIST" => "/extranet/crm/company/",
			"PATH_TO_COMPANY_EDIT" => "/extranet/crm/company/edit/#company_id#/",
			"PATH_TO_CONTACT_LIST" => "/extranet/crm/contact/",
			"PATH_TO_CONTACT_EDIT" => "/extranet/crm/contact/edit/#contact_id#/",
			"PATH_TO_DEAL_LIST" => "/extranet/crm/deal/",
			"PATH_TO_DEAL_EDIT" => "/extranet/crm/deal/edit/#deal_id#/",
			"PATH_TO_INVOICE_LIST" => "/extranet/crm/invoice/",
			"PATH_TO_INVOICE_EDIT" => "/extranet/crm/invoice/edit/#invoice_id#/",
			"PATH_TO_LEAD_LIST" => "/extranet/crm/lead/",
			"PATH_TO_LEAD_EDIT" => "/extranet/crm/lead/edit/#lead_id#/",
			"PATH_TO_REPORT_LIST" => "/extranet/crm/reports/report/",
			"PATH_TO_DEAL_FUNNEL" => "/extranet/crm/reports/",
			"PATH_TO_EVENT_LIST" => "/extranet/crm/events/",
			"PATH_TO_PRODUCT_LIST" => "/extranet/crm/product/",
			"PATH_TO_SETTINGS" => "/extranet/crm/configs/",
			"PATH_TO_SEARCH_PAGE" => "/extranet/search/index.php?where=crm"
		)
	);

	// --> IMPORT RESPONSIBILITY SUBSCRIPTIONS
	$currentUserID = CCrmSecurityHelper::GetCurrentUserID();
	if($currentUserID > 0)
	{
		CCrmSonetSubscription::EnsureAllResponsibilityImported($currentUserID);
	}
	// <-- IMPORT RESPONSIBILITY SUBSCRIPTIONS
	$APPLICATION->IncludeComponent("bitrix:crm.entity.livefeed",
		"",
		array(
			"CAN_EDIT" => $canEdit,
			"FORM_ID" => "",
			"PATH_TO_USER_PROFILE" => "/extranet/company/personal/user/#user_id#/",
			"PATH_TO_GROUP" => "/extranet/workgroups/group/#group_id#/",
			"PATH_TO_CONPANY_DEPARTMENT" => "/extranet/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#"
		),
		null,
		array("HIDE_ICONS" => "Y")
	);
endif;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>