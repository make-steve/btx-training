<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="fields string" id="main_<?=$arParams["arUserField"]["FIELD_NAME"]?>"><?
foreach ($arResult["VALUE"] as $res):

?><div class="fields string"><?
	if($arParams["arUserField"]["SETTINGS"]["ROWS"] < 2):
?><input type="text"
	<?if($arParams["arUserField"]['SETTINGS']['DISABLED'] != 'Y'):?>
	 name="<?=$arParams["arUserField"]["FIELD_NAME"]?>"
	<?endif;?> value="<?=$res?>"<?
	if (intVal($arParams["arUserField"]["SETTINGS"]["SIZE"]) > 0):
		?> size="<?=$arParams["arUserField"]["SETTINGS"]["SIZE"]?>"<?
	endif;
	if (intVal($arParams["arUserField"]["SETTINGS"]["MAX_LENGTH"]) > 0):
		?> maxlength="<?=$arParams["arUserField"]["SETTINGS"]["MAX_LENGTH"]?>"<?
	endif;
	if ($arParams["arUserField"]["EDIT_IN_LIST"]!="Y" || $arParams["arUserField"]['SETTINGS']['DISABLED'] == 'Y'):
		?> disabled="disabled"<?
	endif;
?> class="fields string <?=$arParams["arUserField"]['SETTINGS']['OTHER_CLASS']?>" tabindex="<?=$arParams["arUserField"]['SETTINGS']['TABINDEX']?>">
<?
	if($arParams["arUserField"]['SETTINGS']['DISABLED'] == 'Y'):
?>
	<input type="hidden" name="<?=$arParams["arUserField"]["FIELD_NAME"]?>" 
        class="<?=$arParams["arUserField"]['SETTINGS']['OTHER_CLASS']?>" value="<?=$res?>" tabindex="<?=$arParams["arUserField"]['SETTINGS']['TABINDEX']?>" />
<?
	endif;
	else:
?><textarea class="fields string" name="<?=$arParams["arUserField"]["FIELD_NAME"]?>"<?
	?> cols="<?=$arParams["arUserField"]["SETTINGS"]["SIZE"]?>"<?
	?> rows="<?=$arParams["arUserField"]["SETTINGS"]["ROWS"]?>" <?
	if (intVal($arParams["arUserField"]["SETTINGS"]["MAX_LENGTH"]) > 0):
		?> maxlength="<?=$arParams["arUserField"]["SETTINGS"]["MAX_LENGTH"]?>"<?
	endif;
	if ($arParams["arUserField"]["EDIT_IN_LIST"]!="Y"):
		?> disabled="disabled"<?
	endif;
?>><?=$res?></textarea><?	
	endif;
?></div><?
endforeach;
?></div>
<?if ($arParams["arUserField"]["MULTIPLE"] == "Y" && $arParams["SHOW_BUTTON"] != "N"):?>
<input type="button" value="<?=GetMessage("USER_TYPE_PROP_ADD")?>" onClick="addElement('<?=$arParams["arUserField"]["FIELD_NAME"]?>', this)">
<?endif;?>