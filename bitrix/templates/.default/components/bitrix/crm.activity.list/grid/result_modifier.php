<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


foreach($arResult['ITEMS'] as &$arItem) {

	if($arItem['PROVIDER_ID'] == 'TASKS')
		$arItem['CAN_DELETE'] = isTaskAllowed2Delete($arItem['OWNER_ID'], $arItem['ASSOCIATED_ENTITY_ID']);
}
?>