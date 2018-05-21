<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Счета");
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.invoice",
	"",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_CONTACT_SHOW" => "/extranet/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/extranet/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/extranet/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/extranet/crm/company/edit/#company_id#/",
		"PATH_TO_DEAL_SHOW" => "/crm/deal/show/#deal_id#/",
		"PATH_TO_DEAL_EDIT" => "/crm/deal/edit/#deal_id#/",
		"PATH_TO_LEAD_SHOW" => "/extranet/crm/lead/show/#lead_id#/",
		"PATH_TO_LEAD_EDIT" => "/extranet/crm/lead/edit/#lead_id#/",
		"PATH_TO_LEAD_CONVERT" => "/extranet/crm/lead/convert/#lead_id#/",
		"PATH_TO_PRODUCT_EDIT" => "/extranet/crm/product/edit/#product_id#/",
		"PATH_TO_USER_PROFILE" => "/extranet/company/personal/user/#user_id#/",
		"ELEMENT_ID" => $_REQUEST["invoice_id"],
		"SEF_FOLDER" => "/crm/invoice/",
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"list" => "list/",
			"edit" => "edit/#invoice_id#/",
			"show" => "show/#invoice_id#/"
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"list" => Array(),
			"edit" => Array(),
			"show" => Array(),
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>