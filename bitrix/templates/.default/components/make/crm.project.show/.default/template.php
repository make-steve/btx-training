<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Crm\Integration\StorageType;

if (!empty($arResult['ERROR_MESSAGE']))
{
	ShowError($arResult['ERROR_MESSAGE']);
}

global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/crm-entity-show.css");
if(SITE_TEMPLATE_ID === 'bitrix24')
{
	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/bitrix24/crm-entity-show.css");
}

//Preliminary registration of disk api.
if(CCrmActivity::GetDefaultStorageTypeID() === StorageType::Disk)
{
	CJSCore::Init(array('uploader', 'file_dialog'));
}

$arResult['CRM_CUSTOM_PAGE_TITLE'] = GetMessage(
	'CRM_DEAL_SHOW_TITLE',
	array(
		'#ID#' => $arResult['ELEMENT']['ID'],
		'#TITLE#' => $arResult['ELEMENT']['PROJECT_NAME']
	)
);

$arTabs = array();
$element = isset($arResult['ELEMENT']) ? $arResult['ELEMENT'] : null;
$data = isset($arResult['DATA']) ? $arResult['DATA'] : null;

$APPLICATION->IncludeComponent(
	'bitrix:crm.entity.quickpanelview',
	'quickpanelview.project',
	array(
		'GUID' => strtolower($arResult['FORM_ID']).'_qpv',
		'FORM_ID' => $arResult['TACTILE_FORM_ID'],
		'PERMISSION_ENTITY_TYPE' => $arResult['PERMISSION_ENTITY_TYPE'],
		'ENTITY_TYPE_NAME' => CCrmOwnerType::DealName,
		'ENTITY_ID' => $arResult['ELEMENT_ID'],
		'ENTITY_FIELDS' => $element,
		'ENTITY_DATA' => $data,
		'ENABLE_INSTANT_EDIT' => $arResult['ENABLE_INSTANT_EDIT'],
		'INSTANT_EDITOR_ID' => $instantEditorID,
		'SERVICE_URL' => '/bitrix/components/bitrix/crm.deal.show/ajax.php?'.bitrix_sessid_get(),
		'SHOW_SETTINGS' => 'Y'
	),
	$component,
	array('HIDE_ICONS' => 'Y')
);

$APPLICATION->IncludeComponent(
	'bitrix:crm.interface.form',
	'show.project',
	array(
		'FORM_ID' => $arResult['FORM_ID'],
		'GRID_ID' => $arResult['GRID_ID'],
		'TACTILE_FORM_ID' => $arResult['TACTILE_FORM_ID'],
		'TABS' => $arTabs,
		'DATA' => $element,
		'DATA_ALL' => $data,
		'SHOW_SETTINGS' => 'Y'
	),
	$component, array('HIDE_ICONS' => 'Y')
);
$APPLICATION->AddHeadScript('/bitrix/js/crm/instant_editor.js');
?>