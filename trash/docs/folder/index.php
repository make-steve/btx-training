<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Файловое хранилище");?>
<?$APPLICATION->IncludeComponent("bitrix:webdav", ".default", array(
	"RESOURCE_TYPE" => "FOLDER",
	"FOLDER" => "/docs/folder/files/",
	"USE_AUTH" => "Y",
	"UPLOAD_MAX_FILESIZE"	=>	"1024",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/docs/folder/",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"COLUMNS" => array(
		0 => "NAME",
		1 => "FILE_SIZE",
		2 => "TIMESTAMP_X",
		4 => "",
	),
	"SET_TITLE" => "Y",
	"DISPLAY_PANEL" => "Y"
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>