<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("График отсутствий");
?><?$APPLICATION->IncludeComponent("bitrix:intranet.absence.calendar", ".default", array(
	"DETAIL_URL_PERSONAL" => "/extranet/contacts/personal/user/#USER_ID#/calendar/?EVENT_ID=#EVENT_ID#",
	"NAME_TEMPLATE" => "#NOBR##LAST_NAME# #NAME##/NOBR#",
	"FILTER_SECTION_CURONLY" => "N",
	"VIEW" => "all"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>