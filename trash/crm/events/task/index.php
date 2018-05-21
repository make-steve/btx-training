<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix24/public/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE")/*"Задачи"*/);
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.activity.task.list",
	"",
	Array(
		"ACTIVITY_TASK_COUNT" => "20",
		"ACTIVITY_ENTITY_LINK" => "Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>