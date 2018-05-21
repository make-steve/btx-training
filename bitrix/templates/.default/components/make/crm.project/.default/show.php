<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION */
$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');

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

?>
<div class="bx-crm-view-form">
<?
$APPLICATION->IncludeComponent(
	'make:crm.project.show',
	'',
	array(
		'PATH_TO_PROJECT_SHOW' => $arResult['PATH_TO_PROJECT_SHOW'],
		'PATH_TO_PROJECT_EDIT' => $arResult['PATH_TO_PROJECT_EDIT'],
		'PATH_TO_PROJECT_LIST' => $arResult['PATH_TO_PROJECT_LIST'],	
		'ELEMENT_ID' => $arResult['VARIABLES']['project_id'],
		'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE']
	),
	$component
);
?>
	
</div>
