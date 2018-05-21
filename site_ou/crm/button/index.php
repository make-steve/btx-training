<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/button/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.button",
	".default",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_CONTACT_SHOW" => "/site_ou/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/site_ou/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/site_ou/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/site_ou/crm/company/edit/#company_id#/",
		"PATH_TO_DEAL_SHOW" => "/site_ou/crm/deal/show/#deal_id#/",
		"PATH_TO_DEAL_EDIT" => "/site_ou/crm/deal/edit/#deal_id#/",
		"PATH_TO_USER_PROFILE" => "/site_ou/company/personal/user/#user_id#/",
		"ELEMENT_ID" => $_REQUEST["id"],
		"SEF_FOLDER" => "/site_ou/crm/button/",
		"SEF_URL_TEMPLATES" => Array(
			"list" => "list/",
			"edit" => "edit/#id#/",
		),
		"VARIABLE_ALIASES" => Array(
			"list" => Array(),
			"edit" => Array(),
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>