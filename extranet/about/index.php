<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Official Information");
?> <?$APPLICATION->IncludeComponent("bitrix:iblock.tv", "round", array(
	"IBLOCK_TYPE" => "services",
	"IBLOCK_ID" => "#VIDEO_IBLOCK_ID#",
	"ALLOW_SWF" => "N",
	"DISPLAY_PANEL" => "Y",
	"PATH_TO_FILE" => "#VIDEO_PATH_TO_FILE_ID#",
	"DURATION" => "#VIDEO_DURATION_ID#",
	"WIDTH" => "400",
	"HEIGHT" => "300",
	"LOGO" => "/bitrix/components/bitrix/iblock.tv/templates/.default/images/logo.png",
	"SECTION_ID" => "#VIDEO_SECTION_ID#",
	"ELEMENT_ID" => "#VIDEO_ELEMENT_ID#",
	"DEFAULT_SMALL_IMAGE" => "/bitrix/components/bitrix/iblock.tv/templates/.default/images/default_small.png",
	"DEFAULT_BIG_IMAGE" => "/bitrix/components/bitrix/iblock.tv/templates/.default/images/default_big.png",
	"SORT_BY1" => "ACTIVE_FROM",
	"SORT_ORDER1" => "DESC",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600"
	),
	false
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>