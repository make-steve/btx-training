<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Birthdays");
?>
<?$APPLICATION->IncludeComponent("bitrix:intranet.structure.birthday.nearest", ".default", array(
	"STRUCTURE_PAGE" => "structure.php",
	"PM_URL" => "/extranet/contacts/personal/messages/chat/#USER_ID#/",
	"PATH_TO_VIDEO_CALL" => "/extranet/contacts/personal/video/#USER_ID#/",
	"STRUCTURE_FILTER" => "structure",
	"NUM_USERS" => "50",
	"NAME_TEMPLATE" => "#NOBR##NAME# #LAST_NAME##/NOBR#",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"SHOW_YEAR" => "M",
	"USER_PROPERTY" => array(
		0 => "PERSONAL_PHONE",
	),
	"DEPARTMENT" => "0",
	"SHOW_FILTER" => "N",
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>