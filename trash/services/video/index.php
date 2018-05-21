<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Videokonferenzen");
?>
<?
$APPLICATION->IncludeComponent("bitrix:video", ".default", array(
	"IBLOCK_TYPE" => "events",
	"IBLOCK_ID" => "25",
	"PATH_TO_VIDEO_CONF" => "/services/video/detail.php?ID=#ID#",
	"SET_TITLE" => "Y",
	),
	false
);
?>
<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
