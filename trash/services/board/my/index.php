<?
define("NEED_AUTH",true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Доска объявлений");
?><?$APPLICATION->IncludeComponent("bitrix:iblock.element.add", ".default", Array(
	"NAV_ON_PAGE"	=>	"10",
	"USE_CAPTCHA"	=>	"N",
	"USER_MESSAGE_ADD"	=>	"Ваше объявление добавлено",
	"USER_MESSAGE_EDIT"	=>	"Ваше объявление сохранено",
	"DEFAULT_INPUT_SIZE"	=>	"30",
	"IBLOCK_TYPE"	=>	"services",
	"IBLOCK_ID"	=>	"38",
	"PROPERTY_CODES"	=>	array(
		0	=>	"NAME",
		1	=>	"DATE_ACTIVE_TO",
		2	=>	"IBLOCK_SECTION",
		3	=>	"PREVIEW_TEXT",
		4	=>	"#E_MAIL_PROPERTY_ID#",
		5	=>	"115",
		6	=>	"116",
		7	=>	"",
	),
	"PROPERTY_CODES_REQUIRED"	=>	array(
		0	=>	"NAME",
		1	=>	"IBLOCK_SECTION",
		2	=>	"PREVIEW_TEXT",
		3	=>	"116",
		4	=>	"",
	),
	"GROUPS"	=>	array(
		0	=>	"11",
	),
	"STATUS"	=>	array(
		0	=>	"2",
		1	=>	"3",
		2	=>	"1",
	),
	"STATUS_NEW" => "N",
	"ALLOW_EDIT"	=>	"Y",
	"ALLOW_DELETE"	=>	"Y",
	"ELEMENT_ASSOC"	=>	"PROPERTY_ID",
	"ELEMENT_ASSOC_PROPERTY"	=>	"117",
	"MAX_USER_ENTRIES"	=>	"20",
	"MAX_LEVELS"	=>	"1",
	"LEVEL_LAST"	=>	"Y",
	"MAX_FILE_SIZE"	=>	"0",
	"SEF_MODE"	=>	"N",
	"SEF_FOLDER"	=>	"/services/board/my/",
	"AJAX_MODE"	=>	"Y",
	"AJAX_OPTION_SHADOW"	=>	"Y",
	"AJAX_OPTION_JUMP"	=>	"N",
	"AJAX_OPTION_STYLE"	=>	"Y",
	"AJAX_OPTION_HISTORY"	=>	"N",
	"CUSTOM_TITLE_NAME"	=>	"Заголовок",
	"CUSTOM_TITLE_TAGS"	=>	"",
	"CUSTOM_TITLE_DATE_ACTIVE_FROM"	=>	"",
	"CUSTOM_TITLE_DATE_ACTIVE_TO"	=>	"Срок публикации до",
	"CUSTOM_TITLE_IBLOCK_SECTION"	=>	"Категория",
	"CUSTOM_TITLE_PREVIEW_TEXT"	=>	"Текст объявления",
	"CUSTOM_TITLE_PREVIEW_PICTURE"	=>	"",
	"CUSTOM_TITLE_DETAIL_TEXT"	=>	"",
	"CUSTOM_TITLE_DETAIL_PICTURE"	=>	""
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
