<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Create Group");
?> <?$APPLICATION->IncludeComponent("bitrix:extranet.group_create", ".default", array(
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/extranet/workgroups/create/",
	"PATH_TO_GROUP" => "/extranet/workgroups/group/#group_id#/",
	"PATH_TO_USER" => "/extranet/contacts/personal/user/#user_id#/",
	"SEF_URL_TEMPLATES" => array(
		"index" => "index.php",
		"invite" => "#group_id#/invite/",
	)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>