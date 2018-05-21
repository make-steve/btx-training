<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (CModule::IncludeModule('crm'))
{
	$CrmPerms = new CCrmPerms($GLOBALS["USER"]->GetID());
	$aMenuLinksExt = array();
	if (!$CrmPerms->HavePerm('LEAD', BX_CRM_PERM_NONE, 'ADD'))
	{
		$aMenuLinksExt[] =
			Array(
				"Добавить лид",
				"/crm/lead/edit/0/",
				Array(),
				Array(),
				""
			);
	}
	if (!$CrmPerms->HavePerm('LEAD', BX_CRM_PERM_NONE, 'READ'))
	{
		$aMenuLinksExt[] =
			Array(
				"Список лидов",
				"/crm/lead/list/",
				Array(),
				Array(),
				""
			);
	}
	if (!$CrmPerms->HavePerm('LEAD', BX_CRM_PERM_NONE, 'ADD'))
	{
		$aMenuLinksExt[] =
			Array(
				"Импорт лидов",
				"/crm/lead/import/",
				Array(),
				Array(),
				""
			);
	}

	$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
}
?>