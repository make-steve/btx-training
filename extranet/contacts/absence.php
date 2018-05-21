<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Absence Chart");
?><?$APPLICATION->IncludeComponent("bitrix:intranet.absence.calendar", ".default", array(
	"NAME_TEMPLATE" => "#NOBR##NAME# #LAST_NAME##/NOBR#",
	"FILTER_SECTION_CURONLY" => "N",
	"VIEW" => "all"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>