<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/deal/index.php");
$APPLICATION->SetTitle("Projects");
?>
<?$APPLICATION->IncludeComponent(
	"make:crm.project",
	"",
	Array(
		"SEF_MODE" => "Y",
		"ELEMENT_ID" => $_REQUEST["project_id"],
		"SEF_FOLDER" => "/crm/project/",
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"list" => "list/",
			"edit" => "edit/#project_id#/",
			"show" => "show/#project_id#/"
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"list" => Array(),
			"edit" => Array(),
			"show" => Array(),
		)
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>