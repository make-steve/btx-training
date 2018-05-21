<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новости");
?>

<?$APPLICATION->IncludeComponent("bitrix:news.detail", "official", Array(
	"IBLOCK_TYPE"	=>	"news",
	"IBLOCK_ID"	=>	"35",
	"ELEMENT_ID"	=>	$_REQUEST["ID"],
	"CHECK_DATES"	=>	"Y",
	"FIELD_CODE"	=>	array(
	),
	"PROPERTY_CODE"	=>	array(
		0	=>	"DOC_TYPE",
	),
	"IBLOCK_URL"	=>	"index.php",
	"AJAX_MODE"	=>	"N",
	"AJAX_OPTION_SHADOW"	=>	"Y",
	"AJAX_OPTION_JUMP"	=>	"N",
	"AJAX_OPTION_STYLE"	=>	"Y",
	"AJAX_OPTION_HISTORY"	=>	"N",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"36000000",
	"META_KEYWORDS"	=>	"-",
	"META_DESCRIPTION"	=>	"-",
	"DISPLAY_PANEL"	=>	"N",
	"SET_TITLE"	=>	"Y",
	"INCLUDE_IBLOCK_INTO_CHAIN"	=>	"Y",
	"ADD_SECTIONS_CHAIN"	=>	"Y",
	"ACTIVE_DATE_FORMAT"	=>	"d.m.Y",
	"USE_PERMISSIONS"	=>	"N",
	"DISPLAY_TOP_PAGER"	=>	"N",
	"DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"PAGER_TITLE"	=>	"Страница",
	"PAGER_TEMPLATE"	=>	"",
	"DISPLAY_DATE"	=>	"Y",
	"DISPLAY_NAME"	=>	"Y",
	"DISPLAY_PICTURE"	=>	"Y",
	"DISPLAY_PREVIEW_TEXT"	=>	"N"
	)
);?></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>