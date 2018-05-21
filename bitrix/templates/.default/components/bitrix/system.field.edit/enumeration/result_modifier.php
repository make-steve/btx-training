<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arUserField = $arParams['arUserField'];
// add default value if has default value
if(isset($arUserField['HAS_DEFAULT_VALUE']) && $arUserField['HAS_DEFAULT_VALUE']) {
	$arResult['VALUE'] = array((string)$arUserField['SETTINGS']['DEFAULT_VALUE']);
}
?>