<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Документы для руководства");
?>

<?$APPLICATION->IncludeComponent("bitrix:webdav", ".default", Array(
	"IBLOCK_TYPE"	=>	"library",
	"IBLOCK_ID"	=>	"45",
	"BASE_URL"	=>	"/docs/manage/",
	"NAME_FILE_PROPERTY"	=>	"FILE",
	"USE_AUTH"	=>	"Y",
	"SEF_MODE"	=>	"Y",
	"SEF_FOLDER"	=>	"/docs/manage/",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"COLUMNS"	=>	array(
		0	=>	"NAME",
		1	=>	"TIMESTAMP_X",
		2	=>	"USER_NAME",
		3	=>	"FILE_SIZE",
		4	=>	"WF_STATUS_ID"
	),
	"PAGE_ELEMENTS"	=>	"50",
	"PAGE_NAVIGATION_TEMPLATE"	=>	"",
	"STR_TITLE"	=>	"Документы для руководства",
	"UPLOAD_MAX_FILESIZE"	=>	"1024",
	"UPLOAD_MAX_FILE"	=>	"4",
	"SET_TITLE"	=>	"Y",
	"DISPLAY_PANEL"	=>	"N",
	"USE_COMMENTS" => "Y", 
	"FORUM_ID" => "24", 
	"PATH_TO_SMILE" => "/bitrix/images/forum/smile/", 
	"SEF_URL_TEMPLATES"	=>	array(
		"user_view"	=>	"/company/personal/user/#USER_ID#/",
		"sections"	=>	"#PATH#",
		"section_edit"	=>	"folder/edit/#SECTION_ID#/#ACTION#/",
		"element"	=>	"element/view/#ELEMENT_ID#/",
		"element_edit"	=>	"element/edit/#ACTION#/#ELEMENT_ID#/",
		"element_history"	=>	"element/history/#ELEMENT_ID#/",
		"element_history_get"	=>	"element/historyget/#ELEMENT_ID#/#ELEMENT_NAME#",
		"element_upload"	=>	"element/upload/#SECTION_ID#/",
		"help"	=>	"help"
	)
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>