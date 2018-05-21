<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/.left.menu_ext.php");

if (CModule::IncludeModule('crm'))
{
	$CrmPerms = new CCrmPerms($GLOBALS["USER"]->GetID());
	$arMenuCrm = Array();
	
	if (SITE_TEMPLATE_ID === "bitrix24")
		$arMenuCrm[] = Array(
			GetMessage("MENU_CRM_DESKTOP"),
			"/extranet/crm/",
			Array(),
			Array(),
			""
		);
	$arMenuCrm[] = Array(
		GetMessage("MENU_CRM_STREAM"),
		"/extranet/crm/stream/",
		Array(),
		Array(),
		""
	);
	$arMenuCrm[] = Array(
		GetMessage("MENU_CRM_ACTIVITY"),
		"/extranet/crm/activity/",
		Array(),
		Array(),
		""
	);
	if (!$CrmPerms->HavePerm('CONTACT', BX_CRM_PERM_NONE))
	{
		$arMenuCrm[] = Array(
			GetMessage("MENU_CRM_CONTACT"),
			"/extranet/crm/contact/",
			Array(),
			Array(),
			""
		);
	}
	if (!$CrmPerms->HavePerm('COMPANY', BX_CRM_PERM_NONE))
	{
		$arMenuCrm[] = Array(
			GetMessage("MENU_CRM_COMPANY"),
			"/extranet/crm/company/",
			Array(),
			Array(),
			""
		);
	}
	if (!$CrmPerms->HavePerm('DEAL', BX_CRM_PERM_NONE))
	{
		$arMenuCrm[] = Array(
			GetMessage("MENU_CRM_DEAL"),
			"/extranet/crm/deal/",
			Array(),
			Array(),
			""
		);
	}
	if (!$CrmPerms->HavePerm('INVOICE', BX_CRM_PERM_NONE))
	{
		$arMenuCrm[] = Array(
			GetMessage("MENU_CRM_INVOICE"),
			"/extranet/crm/invoice/",
			Array(), 
			Array(), 
		"" 
		);
	}
	if (!$CrmPerms->HavePerm('LEAD', BX_CRM_PERM_NONE))
	{
		$arMenuCrm[] = Array(
			GetMessage("MENU_CRM_LEAD"),
			"/extranet/crm/lead/",
			Array(),
			Array(),
			""
		);
	}

	$arMenuCrm[] = Array(
		GetMessage("MENU_CRM_PRODUCT"),
		"/extranet/crm/product/",
		Array(),
		Array(),
		""
	);

	if (!$CrmPerms->HavePerm('LEAD', BX_CRM_PERM_NONE) || !$CrmPerms->HavePerm('CONTACT', BX_CRM_PERM_NONE) ||
		!$CrmPerms->HavePerm('COMPANY', BX_CRM_PERM_NONE) || !$CrmPerms->HavePerm('DEAL', BX_CRM_PERM_NONE))
	{
		$arMenuCrm[] = Array(
			GetMessage("MENU_CRM_HISTORY"),
			"/extranet/crm/events/", 
			Array(), 
			Array(), 
			"" 
		);
	}
	
	if (!$CrmPerms->HavePerm('LEAD', BX_CRM_PERM_NONE) || !$CrmPerms->HavePerm('CONTACT', BX_CRM_PERM_NONE) ||
		!$CrmPerms->HavePerm('COMPANY', BX_CRM_PERM_NONE) || !$CrmPerms->HavePerm('DEAL', BX_CRM_PERM_NONE))
	{
		if (IsModuleInstalled('report') || SITE_TEMPLATE_ID !== "bitrix24")
			$arMenuCrm[] = Array(
				GetMessage("MENU_CRM_REPORT"),
				CModule::IncludeModule('report') ? "/extranet/crm/reports/report/" : "/extranet/crm/reports/",
				Array(),
				Array(),
				""
			);
		
		if (SITE_TEMPLATE_ID === "bitrix24")
			$arMenuCrm[] = Array(
				GetMessage("MENU_CRM_FUNNEL"),
				"/extranet/crm/reports/",
				Array(),
				Array(),
				""
			);
	}
	if ($CrmPerms->IsAccessEnabled())
	{
		$arMenuCrm[] = Array(
			GetMessage("MENU_CRM_SETTINGS"),
			"/extranet/crm/configs/",
			Array(),
			Array(),
			""
		);
	}
	$aMenuLinks = array_merge($arMenuCrm, $aMenuLinks);
}

?>