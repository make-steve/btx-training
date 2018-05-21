<?

/**
 * @var $arParams
 * @var $arResult
 * @var $component
 * @global $APPLICATION
 */

	use Bitrix\Main\Localization\Loc;
	use Bitrix\Main\UI\Filter\Type;
	use Bitrix\Main\UI\Filter\DateType;
	use Bitrix\Main\UI\Filter\NumberType;

	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	{
		die();
	}

	global $USER, $APPLICATION;

    // for the takenlijst, do not show the search filter
    if (stripos($APPLICATION->GetCurPage(), '/crm/tasks/')!==false) {
        // $formAction = POST_FORM_ACTION_URI;
        $formAction = '/crm/tasks/import.php';
        ?>
        <style>
            .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            float: left;
            margin-right: 15px;
            }

            .btn {
            display: inline-block;
            text-align: center;
            padding-left: 44px;
            padding-right: 44px;
            padding-top: 3px;
            padding-bottom: 3px;
            cursor: pointer;
            color: #0b66c3;
            font-size: 13px;
            font-weight: normal;
            line-height: 34px;
            vertical-align: middle;
            background-color: #ffffff;
            border: 1px solid #c6cdd3;
            border-radius: 2px;
            -webkit-transition: background 200ms;
            -moz-transition: background 200ms;
            -ms-transition: background 200ms;
            -o-transition: background 200ms;
            transition: background 200ms;
            }
            .btn:hover {
                background-color: #f5f5f5;
            }
            .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            }
            .btn_action {
                background-color: #bbed21;
                border: none;
                padding: 10px;
                font-weight: bold;
                /*color: #535c69;*/
                color: #0b66c3;
                cursor: pointer;
                margin-top: 4px;
            }
            .import_success {
                margin-left: 50px;
                font-style: italic;
                font-weight: 500;
                color: #2067b0;
            }
            .main-grid-more-btn.submit_btn {
                background-color: #bbed21 !important;
            }
        </style>
        <div class="import_block">
            <form method="post" action="<?php echo $formAction;?>" enctype="multipart/form-data">
                <div class="upload-btn-wrapper">
                <button class="btn">Upload Tasks XML</button>
                <input type="file" name="importfile" />
                </div>
                <button type="submit" name="submit" class="btn_action">Submit</button>
            </form>
        </div>
        <?php if (isset($_REQUEST['imported']) && 'y' == strtolower($_REQUEST['imported'])):?>
            <div class="import_success">
                <div class="import-status-content">Import done. Tasks have been updated.</div>
            </div>
        <?php endif;?>
        <?php
        return;
    }


	$arParams["CONFIG"] = $component->prepareConfig();
	$this->addExternalCss($this->GetFolder()."/system-styles.css");

	CJSCore::Init(array('ui', 'dnd'));

	$currentPreset = $arResult["CURRENT_PRESET"];
	$isCurrentPreset = (
			(($currentPreset["ID"] !== "default_filter" && $currentPreset["ID"] !== "tmp_filter") ||
			 ($currentPreset["ID"] === "default_filter" && $currentPreset["FIELDS_COUNT"] > 0) ||
			 ($currentPreset["ID"] === "tmp_filter" && $currentPreset["FIELDS_COUNT"] > 0))
	);

	if (!empty($arResult["TARGET_VIEW_ID"]))
	{
		$this->SetViewTarget($arResult["TARGET_VIEW_ID"], $arResult["TARGET_VIEW_SORT"]);
		$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
		$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."headerless-mode");
	}

	$placeholder = "MAIN_UI_FILTER__PLACEHOLDER_DEFAULT";

	if ($arResult["DISABLE_SEARCH"] || !$arParams["CONFIG"]["SEARCH"])
	{
		$placeholder = "MAIN_UI_FILTER__PLACEHOLDER";
	}

	$arResult = array_merge($arResult, array(
			"CONFIRM_MESSAGE" => Loc::getMessage("MAIN_UI_FILTER__CONFIRM_RESET_MESSAGE"),
			"CONFIRM_APPLY" => Loc::getMessage("MAIN_UI_FILTER__CONFIRM_RESET_APPLY"),
			"CONFIRM_CANCEL" => Loc::getMessage("MAIN_UI_FILTER__BUTTON_CANCEL")
	));
?>

<!-- Final :: Search -->
<div class="main-ui-filter-search<?=$arResult["DISABLE_SEARCH"] || !$arParams["CONFIG"]["SEARCH"] ? " main-ui-filter-no-search" : ""?> main-ui-filter-theme-<?=strtolower($arResult["THEME"])?><?=$arResult["COMPACT_STATE"] ? " main-ui-filter-compact-state" : ""?>" id="<?=$arParams["FILTER_ID"]?>_search_container">
	<input
			type="text"
			tabindex="1"<?
			if($arParams["CONFIG"]["AUTOFOCUS"]):?>autofocus=""<?endif;
			?>value="<?=$arResult["CURRENT_PRESET"]["FIND"]?>"
			name="FIND"
			placeholder="<?=Loc::getMessage($placeholder)?>"
			class="main-ui-filter-search-filter"
			id="<?=$arParams["FILTER_ID"]?>_search"
			autocomplete="off">
	<div class="main-ui-item-icon-block">
		<span class="main-ui-item-icon main-ui-search"></span>
		<span class="main-ui-item-icon main-ui-delete"></span>
	</div>
</div>

<?
	$frame = $this->createFrame()->begin(false);
?>

<script type="text/html" id="<?=$arParams["FILTER_ID"]?>_GENERAL_template">
	<div class="main-ui-filter-wrapper<?=$arParams["VALUE_REQUIRED_MODE"] == true ? " main-ui-filter-value-required-mode" : ""?> main-ui-filter-theme-<?=strtolower($arResult["THEME"])?>">
		<div class="main-ui-filter-inner-container">
			<div class="main-ui-filter-sidebar">
				<div class="main-ui-filter-sidebar-title">
					<h5 class="main-ui-filter-sidebar-title-item"><?=Loc::getMessage("MAIN_UI_FILTER__FILTER")?></h5>
				</div><!--main-ui-filter-sidebar-->
				<div class="main-ui-filter-sidebar-item-container">
					<? if (is_array($arResult["PRESETS"])) : ?>
						<? foreach ($arResult["PRESETS"] as $key => $preset) : ?>
							<div class="main-ui-filter-sidebar-item<?=$preset["ID"] === $arResult["CURRENT_PRESET"]["ID"] ? " main-ui-filter-current-item" : ""?><?
							?><?=$preset["ID"] === "default_filter" || $preset["ID"] === "tmp_filter" ? " main-ui-hide" : ""?><?
							?><?=$preset["IS_PINNED"] && $arParams["CONFIG"]["DEFAULT_PRESET"] ? " main-ui-item-pin" : ""?>" data-id="<?=$preset["ID"]?>"<?
							?><?=$preset["IS_PINNED"] && $arParams["CONFIG"]["DEFAULT_PRESET"] ? " title=\"".Loc::getMessage("MAIN_UI_FILTER__IS_SET_AS_DEFAULT_PRESET")."\"" : " "?>>
								<span class="main-ui-item-icon main-ui-filter-icon-grab" title="<?=Loc::getMessage("MAIN_UI_FILTER__DRAG_TITLE")?>"></span>
								<span class="main-ui-filter-sidebar-item-text-container">
									<span class="main-ui-filter-sidebar-item-text"><?=\Bitrix\Main\Text\HtmlFilter::encode(htmlspecialcharsback($preset["TITLE"]))?></span>
									<input type="text" placeholder="<?=Loc::getMessage("MAIN_UI_FILTER__FILTER_NAME_PLACEHOLDER")?>" value="<?=\Bitrix\Main\Text\HtmlFilter::encode(htmlspecialcharsback($preset["TITLE"]))?>" class="main-ui-filter-sidebar-item-input">
									<span class="main-ui-item-icon main-ui-filter-icon-pin" title="<?=Loc::getMessage("MAIN_UI_FILTER__IS_SET_AS_DEFAULT_PRESET")?>"></span>
								</span>
								<? if ($arParams["CONFIG"]["DEFAULT_PRESET"]) : ?>
									<span class="main-ui-item-icon main-ui-filter-icon-pin" title="<?=Loc::getMessage("MAIN_UI_FILTER__IS_SET_AS_DEFAULT_PRESET")?>"></span>
								<? endif; ?>
								<span class="main-ui-item-icon main-ui-filter-icon-edit" title="<?=Loc::getMessage("MAIN_UI_FILTER__EDIT_PRESET_TITLE")?>"></span>
								<span class="main-ui-item-icon main-ui-delete" title="<?=Loc::getMessage("MAIN_UI_FILTER__REMOVE_PRESET")?>"></span>
								<div class="main-ui-filter-edit-mask"></div>
							</div>
						<? endforeach; ?>
					<? endif; ?>
					<div class="main-ui-filter-sidebar-item main-ui-filter-new-filter">
						<div class="main-ui-filter-edit-mask"></div>
						<input class="main-ui-filter-sidebar-edit-control" type="text" placeholder="<?=Loc::getMessage("MAIN_UI_FILTER__FILTER_NAME_PLACEHOLDER")?>">
					</div>
				</div><!--main-ui-filter-sidebar-item-container-->
			</div><!--main-ui-filter-sidebar-->
			<div class="main-ui-filter-field-container">
				<div class="main-ui-filter-field-container-list">

				</div>

				<div class="main-ui-filter-field-add">
					<span class="main-ui-filter-field-add-item"><?=Loc::getMessage("MAIN_UI_FILTER__ADD_FIELD")?></span>
					<span class="main-ui-filter-field-restore-items"><?=Loc::getMessage("MAIN_UI_FILTER__RESTORE_FIELDS")?></span>
				</div><!--main-ui-filter-field-add-->
			</div><!--main-ui-filter-field-container-->
			<div class="main-ui-filter-bottom-controls">
				<? if ($USER->IsAuthorized()) : ?>
					<div class="main-ui-filter-add-container">
						<span class="main-ui-filter-add-item"><?=Loc::getMessage("MAIN_UI_FILTER__ADD_FILTER")?></span>
						<span class="main-ui-filter-add-edit" title="<?=Loc::getMessage("MAIN_UI_FILTER__FILTER_SETTINGS_TITLE")?>"></span>
						<span class="main-ui-filter-reset-link">
							<span class="main-ui-filter-field-button-item"><?=Loc::getMessage("MAIN_UI_FILTER__RESET_LINK")?></span>
						</span>
					</div><!--main-ui-filter-add-container-->
				<? endif; ?>

				<div class="main-ui-filter-field-preset-button-container">
					<div class="main-ui-filter-field-button-inner">
						<span class="webform-small-button webform-small-button-blue main-ui-filter-field-button main-ui-filter-find">
							<span class="main-ui-filter-field-button-item"><?=Loc::getMessage("MAIN_UI_FILTER__FIND")?></span>
						</span>
						<span class="webform-small-button webform-small-button-transparent main-ui-filter-field-button main-ui-filter-reset">
							<span class="main-ui-filter-field-button-item"><?=Loc::getMessage("MAIN_UI_FILTER__RESET")?></span>
						</span>
					</div>
				</div>
				<div class="main-ui-filter-field-button-container">
					<div class="main-ui-filter-field-button-inner">
						<? if ($USER->CanDoOperation("edit_other_settings")) : ?>
							<label class="main-ui-filter-field-button main-ui-filter-save-for-all" for="save-for-all">
								<input id="save-for-all" class="main-ui-filter-field-button-checkbox" type="checkbox">
								<span class="main-ui-filter-field-button-item"><?=Loc::getMessage("MAIN_UI_FILTER__CONFIRM_APPLY_FOR_ALL_CHECKBOX")?></span>
							</label>
						<? endif; ?>
						<span class="webform-small-button webform-small-button-accept main-ui-filter-field-button main-ui-filter-save">
							<span class="main-ui-filter-field-button-item"><?=Loc::getMessage("MAIN_UI_FILTER__BUTTON_SAVE")?></span>
						</span>
						<span class="webform-small-button webform-small-button-transparent main-ui-filter-field-button main-ui-filter-cancel">
							<span class="main-ui-filter-field-button-item"><?=Loc::getMessage("MAIN_UI_FILTER__BUTTON_CANCEL")?></span>
						</span>
					</div>
				</div>
			</div><!--main-ui-filter-bottom-controls-->
		</div><!--main-ui-filter-inner-container-->
	</div><!--main-ui-filter-wrapper-->
</script>

<script>
	BX.Main.filterManager.push(
		'<?=$arParams["FILTER_ID"]?>',
		new BX.Main.Filter(
			<?=CUtil::PhpToJSObject($arResult)?>,
			<?=CUtil::PhpToJSObject($arParams["CONFIG"])?>,
			<?=CUtil::PhpToJSObject(Type::getList())?>,
			<?=CUtil::PhpToJSObject(DateType::getList())?>,
			<?=CUtil::PhpToJSObject(NumberType::getList())?>
		)
	);
</script>
<?
	$frame->end();

	if (!empty($arResult["TARGET_VIEW_ID"]))
	{
		$this->EndViewTarget();
	}
?>