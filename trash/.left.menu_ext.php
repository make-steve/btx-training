<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (SITE_TEMPLATE_ID !== "bitrix24")
	return;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/.left.menu_ext.php");
	
if (!CModule::IncludeModule("socialnetwork"))
	return;

global $arSocNetFeaturesSettings;
$arUserActiveFeatures = CSocNetFeatures::GetActiveFeatures(SONET_ENTITY_USER, $GLOBALS["USER"]->GetID());
GLOBAL $USER;
$USER_ID = $USER->GetID();

$aMenuB24 = array();
	
$aMenuB24[] = Array(
	GetMessage("LEFT_MENU_LIVE_FEED"),
	SITE_DIR."index.php",
	Array(),
	Array("name" => "live_feed", "counter_id" => "live-feed", "menu_item_id"=>"menu_live_feed"),
	""
);
	
if ($GLOBALS["USER"]->IsAuthorized()):
	if (
		array_key_exists("tasks", $arSocNetFeaturesSettings)
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["tasks"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["tasks"]["allowed"])
		&& in_array("tasks", $arUserActiveFeatures)
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_TASKS"),
			SITE_DIR."company/personal/user/".$USER_ID."/tasks/?F_CANCEL=Y&F_STATE=sR400",
			Array(),
			Array("name" => "tasks", "counter_id" => "tasks_total"),
			"CBXFeatures::IsFeatureEnabled('Tasks')"
		);
	if (
		array_key_exists("calendar", $arSocNetFeaturesSettings)	
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["calendar"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["calendar"]["allowed"])
		&& in_array("calendar", $arUserActiveFeatures)
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_CALENDAR"),
			SITE_DIR."company/personal/user/".$USER_ID."/calendar/",
			Array(),
			Array(),
			"CBXFeatures::IsFeatureEnabled('Calendar')"
		);
	if (
		CModule::IncludeModule("webdav") && $GLOBALS["USER"]->IsAuthorized()
		&& array_key_exists("files", $arSocNetFeaturesSettings)	
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["files"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["files"]["allowed"])
		&& in_array("files", $arUserActiveFeatures)
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_DISC"),
			SITE_DIR."company/personal/user/".$USER_ID."/files/lib/",
			Array(),
			Array(),
			"CBXFeatures::IsFeatureEnabled('PersonalFiles')"
		);
	if (
		CModule::IncludeModule("photogallery") 
		&& array_key_exists("photo", $arSocNetFeaturesSettings)	
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["photo"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["photo"]["allowed"])
		&& in_array("photo", $arUserActiveFeatures)	
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_PHOTO"),
			SITE_DIR."company/personal/user/".$USER_ID."/photo/",
			Array(),
			Array(),
			"CBXFeatures::IsFeatureEnabled('PersonalPhoto')"
		);
	if (
		CModule::IncludeModule("blog") 
		&& array_key_exists("blog", $arSocNetFeaturesSettings)
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["blog"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["blog"]["allowed"])
		&& in_array("blog", $arUserActiveFeatures)	
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_BLOG"),
			SITE_DIR."company/personal/user/".$USER_ID."/blog/",
			Array(),
			Array("counter_id" => "blog_post"),
			""
		);
	if (CModule::IncludeModule("intranet") && CIntranetUtils::IsExternalMailAvailable())
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_MAIL"),
			SITE_DIR."company/personal/mail/",
			Array(),
			Array("counter_id" => "mail_unseen", "warning_link" => SITE_DIR.'company/personal/mail/?STEP=setup', "warning_title" => GetMessage("LEFT_MENU_MAIL_SETTING"), "menu_item_id"=>"menu_external_mail"),
			""
		);
	if (CModule::IncludeModule("bizproc"))
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_BP"),
			SITE_DIR."company/personal/bizproc/",
			Array(),
			Array("counter_id" => "bp_tasks"),
			"CBXFeatures::IsFeatureEnabled('BizProc')"
		);
endif;
$aMenuLinks = array_merge($aMenuLinks, $aMenuB24);
?>