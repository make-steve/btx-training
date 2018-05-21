<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$categoryID = isset($arResult['VARIABLES']['category_id']) ? $arResult['VARIABLES']['category_id'] : -1;

/** @var CMain $APPLICATION */
$APPLICATION->IncludeComponent(
	'bitrix:crm.control_panel',
	'',
	array(
		'ID' => 'PROJECT_LIST',
		'ACTIVE_ITEM_ID' => 'PROJECT',
		'PATH_TO_PROJECT_LIST' => isset($arResult['PATH_TO_PROJECT_LIST']) ? $arResult['PATH_TO_PROJECT_LIST'] : '',
		'PATH_TO_PROJECT_EDIT' => isset($arResult['PATH_TO_PROJECT_EDIT']) ? $arResult['PATH_TO_PROJECT_EDIT'] : '',
	),
	$component
);
$isBitrix24Template = SITE_TEMPLATE_ID === 'bitrix24';

$APPLICATION->ShowViewContent('crm-grid-filter');

$APPLICATION->IncludeComponent(
	'make:crm.project.list',
	'',
	array(
		'DEAL_COUNT' => '20',
		'PATH_TO_PROJECT_LIST' => $arResult['PATH_TO_PROJECT_LIST'],
		'PATH_TO_PROJECT_SHOW' => $arResult['PATH_TO_PROJECT_SHOW'],
		'PATH_TO_PROJECT_EDIT' => $arResult['PATH_TO_PROJECT_EDIT'],
		'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
		'NAVIGATION_CONTEXT_ID' => $arResult['NAVIGATION_CONTEXT_ID'],
		'GRID_ID_SUFFIX' => $categoryID >= 0 ? "P_{$categoryID}" : '',
		'CATEGORY_ID' => $categoryID
	),
	$component
);
?>