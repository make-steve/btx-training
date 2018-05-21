<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Резервирование переговорных");
?>
<p><?$APPLICATION->IncludeComponent(
	"bitrix:intranet.reserve_meeting",
	".default",
	array(
		"IBLOCK_TYPE" => "events",
		"IBLOCK_ID" => "44",
		"USERGROUPS_MODIFY" => array(),
		"USERGROUPS_RESERVE" => array(),
		"USERGROUPS_CLEAR" => array(),
		"SEF_MODE" => "N",
		"SET_NAVCHAIN" => "Y",
		"SET_TITLE" => "Y",
		"WEEK_HOLIDAYS" => array(0=>"5",1=>"6",),
	),
	false
);
?></p>

<p><a href="/services/res_c.php">Резервирование переговорных с помощью календаря</a><br /></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>