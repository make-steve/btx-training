<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мои дела");
if (CModule::IncludeModule("crm"))
{
?>
<?$APPLICATION->IncludeComponent("bitrix:crm.activity.list",
	"grid",
	array(
		'PERMISSION_TYPE' => 'WRITE',
		'ENABLE_TOOLBAR' => true,
		'ENABLE_NAVIGATION' => true,
		'DISPLAY_REFERENCE' => true,
		'DISPLAY_CLIENT' => true,
		'AJAX_MODE' => 'Y',
		'PREFIX' => 'MY_ACTIVITIES',		
	),
	false
);?>
<?
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>