<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Все документы");
?><?$APPLICATION->IncludeComponent(
	"bitrix:webdav.aggregator",
	"",
	Array(
		"SEF_MODE" => "Y",
		"IBLOCK_TYPE" => "library",
		"IBLOCK_OTHER_IDS" => array("5", "7", "9", "46", "45"),
		"IBLOCK_GROUP_ID" => "5",
		"IBLOCK_USER_ID" => "7",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"SEF_FOLDER" => "/docs/",
		"SEF_URL_TEMPLATES" => Array(
			"USER_FILE_PATH" => "/company/personal/user/#USER_ID#/files/lib/#PATH#",
			"GROUP_FILE_PATH" => "/workgroups/group/#GROUP_ID#/files/#PATH#"
		),
		"VARIABLE_ALIASES" => Array(
			"USER_FILE_PATH" => Array(),
			"GROUP_FILE_PATH" => Array(),
		)
	),
false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
