<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Video Conference");
?>
<?
$APPLICATION->IncludeComponent("bitrix:video.conf", ".default", array(
	"IBLOCK_TYPE" => "events",
	"IBLOCK_ID" => "#CALENDAR_RES_VIDEO_IBLOCK_ID#",
	"ID" => $_REQUEST["ID"],
	"PATH_TO_VIDEO_CONF" => "/extranet/contacts/video/detail.php?ID=#ID#",
	"PATH_TO_VIDEO_LIST" => "/extranet/contacts/video/",
	"PATH_TO_USER" => "/extranet/contacts/personal/user/#user_id#/",
	"PATH_TO_MESSAGES_CHAT" => "/extranet/contacts/personal/messages/chat/#user_id#/",
	"SET_TITLE" => "Y"
	),
	false
);
?>
<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
