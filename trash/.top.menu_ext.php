<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (SITE_TEMPLATE_ID !== "bitrix24")
	return;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/.top.menu_ext.php");

GLOBAL $USER;
$USER_ID = $USER->GetID();
	
$arMenuB24 = Array(
	Array(
		GetMessage("TOP_MENU_FAVORITE"),
		SITE_DIR, 
		Array(), 
		Array("class" => "menu-favorites"), 
		"" 
	),	
);
if (CModule::IncludeModule("socialnetwork"))
	$arMenuB24[] = Array(
		GetMessage("TOP_MENU_GROUPS"),
		SITE_DIR."workgroups/",
		Array(),
		Array("class" => "menu-groups"),
		"CBXFeatures::IsFeatureEnabled('Workgroups')"
	);
//extranet groups
if (CModule::IncludeModule("extranet") && CBXFeatures::IsFeatureEnabled('Workgroups') && CBXFeatures::IsFeatureEnabled('Extranet') && CModule::IncludeModule("socialnetwork"))
{
	$arGroupFilterMy = array(
			"USER_ID" => $USER_ID,
			"<=ROLE" => SONET_ROLES_USER,
			"GROUP_ACTIVE" => "Y",
			"!GROUP_CLOSED" => "Y",
			"GROUP_SITE_ID" => CExtranet::GetExtranetSiteID()
		);

	$dbGroups = CSocNetUserToGroup::GetList(
		array(),
		$arGroupFilterMy,
		false,
		false
		//array('ID')
	);
	if ($arGroups = $dbGroups->GetNext())
	{
		$arMenuB24[] = Array(
			GetMessage("TOP_MENU_GROUPS_EXTRANET"),
			SITE_DIR."workgroups/extranet/",
			Array(),
			Array("class" => "menu-groups-extranet"),
			"CBXFeatures::IsFeatureEnabled('Workgroups')"
		);
	}
}

$arPathPattern = array("\/company\/$", "\/docs\/$", "\/services\/$", "\/workgroups\/$", "\/crm\/$", "\/about\/$");
$arClasses = array("menu-employees", "menu-docs", "menu-services", "menu-groups-extranet", "menu-crm", "menu-company");
$aNewMenuLinks = array();
foreach ($aMenuLinks as $key => $arItem)
{
	$bfound = false;
	$arNewItem = $arItem;
	$arNewItem[0] = ToUpper($arNewItem[0]);
	foreach ($arPathPattern as $key => $pattern)
	{
		$matches = "";
		preg_match("/".$pattern."/is", $arItem[1], $matches);
		if ($matches[0])
		{
			$arNewItem[3] = array("class" => $arClasses[$key]);
			break;
		}
	}
	if ($key != 3) $aNewMenuLinks[] = $arNewItem;		
}

$rsSite = CSite::GetList($by="sort", $order="asc", $arFilter=array("ACTIVE" => "Y"));
$numSites = 0;
while ($arSite = $rsSite->GetNext())
{
	$numSites++;
}
if ($numSites > 1)
{
	$aNewMenuLinks[] = Array(
		GetMessage("TOP_MENU_DEPARTMENTS"),
		"/departments/",
		Array(),
		Array("class" => "menu-departments"),
		""
	);
}

$aMenuLinks = array_merge($arMenuB24, $aNewMenuLinks);
?>