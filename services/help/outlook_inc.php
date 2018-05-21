<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/help/outlook_inc.php");

CModule::IncludeModule("intranet");
CModule::IncludeModule("iblock");
?>
<table style="margin-top:7px;">
<tr><td><li><a href="javascript:<?echo htmlspecialcharsbx(CIntranetUtils::GetStsSyncURL(array('LINK_URL' => '/company/'), 'contacts'))?>"><?=GetMessage("SERVICES_LINK1")?></a></td></tr>
<tr><td><li><a href="javascript:<?echo htmlspecialcharsbx(CIntranetUtils::GetStsSyncURL(array('LINK_URL' => '/'.$USER->GetID().'/'), 'tasks'))?>"><?=GetMessage("SERVICES_LINK2")?></a></td></tr>
<?

if(COption::GetOptionInt("intranet", 'iblock_calendar', 0)>0):
	$dbRs = CIBlockSection::GetList(Array(), Array("IBLOCK_ID"=>COption::GetOptionInt("intranet", 'iblock_calendar', 0), 'CREATED_BY'=>$USER->GetID()));
	if($arRs = $dbRs->Fetch()):
		$dbRs2 = CIBlockSection::GetList(Array(), Array('SECTION_ID'=>$arRs["ID"]));
		while($arRs2 = $dbRs2->GetNext()):
		?>
		<tr><td><li><a href="javascript:<?echo htmlspecialcharsbx(CIntranetUtils::GetStsSyncURL(array('ID' => $arRs2["ID"], 'LINK_URL' => 'company/personal/user/'.$USER->GetID().'/calendar/'), 'calendar'))?>">Connect <?=$arRs2["NAME"]?></a></td></tr>
		<?endwhile?>
	<?endif?>
<?endif?>
</table>
