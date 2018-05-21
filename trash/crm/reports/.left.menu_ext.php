<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$aMenuLinksExt = array(
);
if (CModule::IncludeModule('crm') && CModule::IncludeModule('report'))
{
	$aMenuLinksExt[] =
		Array(
			"Список отчетов",
			"/crm/reports/report/",
			Array(),
			Array(),
			""
		);
	$CrmPerms = new CCrmPerms($GLOBALS["USER"]->GetID());
	if (!$CrmPerms->HavePerm('DEAL', BX_CRM_PERM_NONE, 'READ'))
	{
		$aMenuLinksExt[] =
			Array(
				"Воронка продаж",
				"/crm/reports/index.php",
				Array(),
				Array(),
				""
			);
	}
	$obRep = CReport::GetList('crm');
	while($arRep = $obRep->fetch())
	{
		$aMenuLinksExt[] =
			Array(
				$arRep['TITLE'],
				CComponentEngine::MakePathFromTemplate("/crm/reports/report/view/#report_id#/", array('report_id' => $arRep['ID'])),
				Array(),
				Array(),
				""
			);
	}
}
$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);

?>