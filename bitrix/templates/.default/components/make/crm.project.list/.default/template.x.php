<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 */

$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/crm-entity-show.css");
if(SITE_TEMPLATE_ID === 'bitrix24')
{
	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/bitrix24/crm-entity-show.css");
}
if (CModule::IncludeModule('bitrix24') && !\Bitrix\Crm\CallList\CallList::isAvailable())
{
	CBitrix24::initLicenseInfoPopupJS();
}

Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/activity.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/interface_grid.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/autorun_proc.js');
Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/crm/css/autorun_proc.css');

$isInternal = $arResult['INTERNAL'];
$callListUpdateMode = $arResult['CALL_LIST_UPDATE_MODE'];
$allowWrite = $arResult['PERMS']['WRITE'];
$allowDelete = $arResult['PERMS']['DELETE'];
$currentUserID = $arResult['CURRENT_USER_ID'];

$gridManagerID = $arResult['GRID_ID'].'_MANAGER';
$gridManagerCfg = array(
	'ownerType' => 'DEAL',
	'gridId' => $arResult['GRID_ID'],
	'formName' => "form_{$arResult['GRID_ID']}",
	'allRowsCheckBoxId' => "actallrows_{$arResult['GRID_ID']}",
	'activityEditorId' => $activityEditorID,
	'serviceUrl' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
	'filterFields' => array()
);

$prefix = $arResult['GRID_ID'];
$prefixLC = strtolower($arResult['GRID_ID']);

$arResult['GRID_DATA'] = array();
$arColumns = array();
foreach ($arResult['HEADERS'] as $arHead)
	$arColumns[$arHead['id']] = false;

foreach($arResult['PROJECT'] as $sKey =>  $arProject)
{

	$jsTitle = isset($arProject['TITLE']) ? CUtil::JSEscape($arProject['TITLE']) : '';
	$jsShowUrl = isset($arProject['PATH_TO_PROJECT_SHOW']) ? CUtil::JSEscape($arProject['PATH_TO_PROJECT_SHOW']) : '';

	$arActivityMenuItems = array();
	$arActivitySubMenuItems = array();
	$arActions = array();

	$arActions[] = array(
		'TITLE' => GetMessage('CRM_PROJECT_SHOW_TITLE'),
		'TEXT' => GetMessage('CRM_PROJECT_SHOW'),
		'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arProject['PATH_TO_PROJECT_SHOW'])."');",
		'DEFAULT' => true
	);

	/*if($arProject['EDIT'])
	{
		$arActions[] = array(
			'TITLE' => GetMessage('CRM_PROJECT_EDIT_TITLE'),
			'TEXT' => GetMessage('CRM_PROJECT_EDIT'),
			'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arProject['PATH_TO_PROJECT_EDIT'])."');"
		);
		$arActions[] = array(
			'TITLE' => GetMessage('CRM_PROJECT_COPY_TITLE'),
			'TEXT' => GetMessage('CRM_PROJECT_COPY'),
			'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arProject['PATH_TO_PROJECT_COPY'])."');"
		);
	}*/

	/*echo '<pre>';
		print_r($arProject);
	echo '</pre>';*/
	$eventParam = array(
		'ID' => $arProject['ID'],
		'CALL_LIST_ID' => $arResult['CALL_LIST_ID'],
		'CALL_LIST_CONTEXT' => $arResult['CALL_LIST_CONTEXT'],
		'GRID_ID' => $arResult['GRID_ID']
	);

	
	$resultItem = array(
		'id' => $arProject['ID'],
		'actions' => $arActions,
		'data' => $arProject,
		'editable' => !$arProject['EDIT'] ? ($arResult['INTERNAL'] ? 'N' : $arColumns) : 'Y',
		'columns' => array(
			'CONTACT_PERSON' => $arProject['CONTACT_PERSON'],
			'POST_ADD' => $arProject['CONTACT_POSTADD'],
			'POSTCODE_PLACE' => $arProject['CONTACT_POSTCODE'].' '.$arProject['CONTACT_PLAATS'],
			'TELEPHONE' => $arProject['CONTACT_TELEPHONE'],
			'EMAIL' => $arProject['CONTACT_EMAIL'],
			'VISITING_ADD' => $arProject['COMPANY_BEZOEKADRES'],
			'VISITING_POSTCODE_PLACE' => $arProject['COMPANY_POSTCODE'].' '.$arProject['COMPANY_PLAATS'],
			'COMPANY_INVOICE' => $arProject['COMPANY_FACTUURBEDRIJF'],
			'COMPANY_BILL_ADD' => $arProject['COMPANY_FACTUURADRES'],
			'COMPANY_TAV' => $arProject['COMPANY_TAV'],
		)
	);

	/*echo '<pre>';
		print_r($resultItem);
	echo '</pre>';*/
	$arResult['GRID_DATA'][] = $resultItem;
	unset($resultItem);
}

// echo '<pre>';
// 	print_r($arResult);
// echo '</pre>';
$APPLICATION->IncludeComponent(
	'bitrix:crm.interface.grid',
	'titleflex',
	array(
		'GRID_ID' => $arResult['GRID_ID'],
		'HEADERS' => $arResult['HEADERS'],
		'SORT' => $arResult['SORT'],
		'SORT_VARS' => $arResult['SORT_VARS'],
		'ROWS' => $arResult['GRID_DATA'],
		'FORM_ID' => $arResult['FORM_ID'],
		'TAB_ID' => $arResult['TAB_ID'],
		'AJAX_ID' => $arResult['AJAX_ID'],
		'AJAX_OPTION_HISTORY' => $arResult['AJAX_OPTION_HISTORY'],
		'AJAX_LOADER' => isset($arParams['AJAX_LOADER']) ? $arParams['AJAX_LOADER'] : null,
		'FILTER' => $arResult['FILTER'],
		'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
		'ENABLE_LIVE_SEARCH' => true,
		'ACTION_PANEL' => $controlPanel,
		'AJAX_OPTION_JUMP' => "N",
		'PAGINATION' => isset($arResult['PAGINATION']) && is_array($arResult['PAGINATION'])
			? $arResult['PAGINATION'] : array(),
		'ENABLE_ROW_COUNT_LOADER' => true,
		'PRESERVE_HISTORY' => $arResult['PRESERVE_HISTORY'],
		'MESSAGES' => $messages,
		'NAVIGATION_BAR' => array(),
		'IS_EXTERNAL_FILTER' => $arResult['IS_EXTERNAL_FILTER'],
		'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
		'EXTENSION' => array(
			'ID' => $gridManagerID,
			'CONFIG' => array(
				'ownerTypeName' => CCrmOwnerType::DealName,
				'gridId' => $arResult['GRID_ID'],
				'activityEditorId' => $activityEditorID,
				'activityServiceUrl' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
				'taskCreateUrl'=> isset($arResult['TASK_CREATE_URL']) ? $arResult['TASK_CREATE_URL'] : '',
				'serviceUrl' => '/bitrix/components/make/crm.project.list/list.ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
				'loaderData' => isset($arParams['AJAX_LOADER']) ? $arParams['AJAX_LOADER'] : null
			),
			'MESSAGES' => array(
				'deletionDialogTitle' => GetMessage('CRM_DEAL_DELETE_TITLE'),
				'deletionDialogMessage' => GetMessage('CRM_DEAL_DELETE_CONFIRM'),
				'deletionDialogButtonTitle' => GetMessage('CRM_DEAL_DELETE'),
				'moveToCategoryDialogTitle' => GetMessage('CRM_DEAL_MOVE_TO_CATEGORY_DLG_TITLE'),
				'moveToCategoryDialogMessage' => GetMessage('CRM_DEAL_MOVE_TO_CATEGORY_DLG_SUMMARY')
			)
		),
	),
	$component
);
?>