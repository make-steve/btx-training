<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 */

$gridID = $arParams['~GRID_ID'];
$gridIDLc = strtolower($gridID);
$filterID = isset($arParams['~FILTER_ID']) ? $arParams['~FILTER_ID'] : $gridID;
$filterIDLc = strtolower($filterID);

//region Prepare custom fields
if(isset($arParams['~FILTER']) && is_array($arParams['~FILTER']))
{
	$entitySelectors = array();
	$userSelectors = array();
	foreach($arParams['~FILTER'] as $filterItem)
	{
		if(!(isset($filterItem['type'])
			&& $filterItem['type'] === 'custom_entity'
			&& isset($filterItem['selector'])
			&& is_array($filterItem['selector']))
		)
		{
			continue;
		}

		$selector = $filterItem['selector'];

		$selectorType = isset($selector['TYPE']) ? $selector['TYPE'] : '';
		$selectorData = isset($selector['DATA']) && is_array($selector['DATA']) ? $selector['DATA'] : null;

		if(empty($selectorData))
		{
			continue;
		}

		if($selectorType === 'crm_entity')
		{
			$entitySelectors[] = $selectorData;
		}
		elseif($selectorType === 'user')
		{
			$userSelectors[] = $selectorData;
		}
	}

	//region CRM Entity Selectors
	if(!empty($entitySelectors))
	{
		Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/crm.js');
		?><script type="text/javascript">
		BX.ready(
			function()
			{
				BX.CrmEntitySelector.messages =
				{
					"selectButton": "<?=GetMessageJS('CRM_GRID_ENTITY_SEL_BTN')?>",
					"noresult": "<?=GetMessageJS('CRM_GRID_SEL_SEARCH_NO_RESULT')?>",
					"search": "<?=GetMessageJS('CRM_GRID_ENTITY_SEL_SEARCH')?>",
					"last": "<?=GetMessageJS('CRM_GRID_ENTITY_SEL_LAST')?>"
				};
				BX.CrmEntityType.setCaptions(<?=CUtil::PhpToJSObject(CCrmOwnerType::GetJavascriptDescriptions())?>);
			}
		);<?
			foreach($entitySelectors as $entitySelector)
			{
				$selectorID = $entitySelector['ID'];
				$fieldID = $entitySelector['FIELD_ID'];
				$entityTypeNames = $entitySelector['ENTITY_TYPE_NAMES'];
				$isMultiple = $entitySelector['IS_MULTIPLE'];
				$title = isset($entitySelector['TITLE']) ? $entitySelector['TITLE'] : '';
				?>BX.ready(
			function()
			{
				BX.CrmUIFilterEntitySelector.create(
					"<?=CUtil::JSEscape($selectorID)?>",
					{
						fieldId: "<?=CUtil::JSEscape($fieldID)?>",
						entityTypeNames: <?=CUtil::PhpToJSObject($entityTypeNames)?>,
						isMultiple: <?=$isMultiple ? 'true' : 'false'?>,
						title: "<?=CUtil::JSEscape($title)?>"
					}
				);
			}
		);<?
		}
		?></script><?
	}
	//endregion
	//region User Selectors

	$enableSonetUserSelector = false;

	if(!empty($userSelectors))
	{
		if($enableSonetUserSelector)
		{
			\Bitrix\Main\Loader::includeModule("socialnetwork");
			$destSort = CSocNetLogDestination::GetDestinationSort(array("DEST_CONTEXT" => "CRM_FILTER_USER"));
			$last = array();
			CSocNetLogDestination::fillLastDestination($destSort, $last);

			$destUserIDs = array();
			if(isset($last['USERS']))
			{
				foreach($last['USERS'] as $code)
				{
					$destUserIDs[] = str_replace('U', '', $code);
				}
			}

			$dstUsers = CSocNetLogDestination::GetUsers(array('id' => $destUserIDs));
			$structure = CSocNetLogDestination::GetStucture(array('LAZY_LOAD' => true));

			$department = $structure['department'];
			$departmentRelation = $structure['department_relation'];
			$departmentRelationHead = $structure['department_relation_head'];

			?><script type="text/javascript"><?
			foreach($userSelectors as $userSelector)
			{
				$selectorID = $userSelector['ID'];
				$fieldID = $userSelector['FIELD_ID'];
				?>BX.ready(
				function()
				{
					BX.FilterUserSelector2.create(
						"<?=CUtil::JSEscape($selectorID)?>",
						{
							fieldId: "<?=CUtil::JSEscape($fieldID)?>",
							users: <?=CUtil::PhpToJSObject($dstUsers)?>,
							department: <?=CUtil::PhpToJSObject($department)?>,
							departmentRelation: <?=CUtil::PhpToJSObject($departmentRelation)?>,
							last: <?=CUtil::PhpToJSObject(array_change_key_case($last, CASE_LOWER))?>
						}
					);
				}
			);<?
			}
			?></script><?
		}
		else
		{
			$componentName = "{$filterID}_FILTER_USER";
			$APPLICATION->IncludeComponent(
				'bitrix:intranet.user.selector.new',
				'',
				array(
					'MULTIPLE' => 'N',
					'NAME' => $componentName,
					'INPUT_NAME' => strtolower($componentName),
					'SHOW_EXTRANET_USERS' => 'NONE',
					'POPUP' => 'Y',
					'SITE_ID' => SITE_DIR,
					'NAME_TEMPLATE' => $nameTemplate
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);
			?><script type="text/javascript"><?
			foreach($userSelectors as $userSelector)
			{
				$selectorID = $userSelector['ID'];
				$fieldID = $userSelector['FIELD_ID'];
				?>BX.ready(
					function()
					{
						BX.FilterUserSelector.create(
							"<?=CUtil::JSEscape($selectorID)?>",
							{
								fieldId: "<?=CUtil::JSEscape($fieldID)?>",
								componentName: "<?=CUtil::JSEscape($componentName)?>"
							}
						);
					}
				);<?
			}
			?></script><?
		}
	}
	//endregion
}
//endregion

$isBitrix24Template = SITE_TEMPLATE_ID === 'bitrix24';

//region Filter Navgation Bar
$navigationBarConfig = null;
$navigationBarID = "{$filterIDLc}_nav_bar";
$navigationBar = isset($arParams['~NAVIGATION_BAR']) && is_array($arParams['~NAVIGATION_BAR'])
	? $arParams['~NAVIGATION_BAR'] : array();

$navigationBarItems = isset($navigationBar['ITEMS']) ? $navigationBar['ITEMS'] : null;
$hasNavigationBar = !empty($navigationBarItems);
if($hasNavigationBar)
{
	$navigationBarConfig = array('items' => array());
	if(isset($navigationBar['BINDING']))
	{
		$navigationBarConfig['binding'] = $navigationBar['BINDING'];
	}

	if($isBitrix24Template)
	{
		$this->SetViewTarget('below_pagetitle', 100);
	}

	?><div class="crm-view-switcher pagetitle-align-right-container">
<!--	<div class="crm-view-switcher-name">--><?//=GetMessage('CRM_INT_FILTER_NAV_BAR_TITLE')?><!--:</div>-->
	<div class="crm-view-switcher-list"><?
		$itemQty = 0;
		foreach($navigationBarItems as $barItem)
		{
			$itemQty++;
			$itemID = isset($barItem['id']) ? $barItem['id'] : $itemQty;
			$itemName = isset($barItem['name']) ? $barItem['name'] : $itemID;
			$itemUrl = isset($barItem['url']) ? $barItem['url'] : '';

			$itemElementID = strtolower("{$gridID}_{$itemID}");
			$itemConfig = array('id' => $itemID, 'name' => $itemName, 'elementId' => $itemElementID, 'url' => $itemUrl);
			$className = 'crm-view-switcher-list-item';
			if(isset($barItem['active']) && $barItem['active'])
			{
				$itemConfig['active'] = true;
				$className = "{$className} crm-view-switcher-list-item-active";
			}
			$navigationBarConfig['items'][] = $itemConfig;
			?><div id="<?=htmlspecialcharsbx($itemElementID)?>" class="<?=$className?>">
				<?=htmlspecialcharsbx($itemName)?>
			</div><?
		}
		?></div>
	</div>
	<script type="text/javascript">
		BX.ready(
			function()
			{
				BX.InterfaceGridFilterNavigationBar.create(
					"<?=CUtil::JSEscape($navigationBarID)?>",
					BX.CrmParamBag.create(<?=CUtil::PhpToJSObject($navigationBarConfig)?>)
				);
			}
		);
	</script><?

	if($isBitrix24Template)
	{
		$this->EndViewTarget();
	}
}
//endregion

$viewID = isset($arParams['~RENDER_INTO_VIEW']) ? $arParams['~RENDER_INTO_VIEW'] : '';
if($viewID === '')
{
	$viewID = $isBitrix24Template ? 'inside_pagetitle' : 'crm-grid-filter';
}

$this->SetViewTarget($viewID, 0);
//region Filter
?><div class="pagetitle-container pagetitle-flexible-space" style="overflow: hidden;"><?
$APPLICATION->IncludeComponent(
	'bitrix:main.ui.filter',
	'',
	array(
		'GRID_ID' => $gridID,
		'FILTER_ID' => $filterID,
		'FILTER' => $arParams['~FILTER'],
		'FILTER_FIELDS' => isset($arParams['~FILTER_FIELDS']) ? $arParams['~FILTER_FIELDS'] : array(),
		'FILTER_PRESETS' => $arParams['~FILTER_PRESETS'],
		'DISABLE_SEARCH' => isset($arParams['~DISABLE_SEARCH']) && $arParams['~DISABLE_SEARCH'] === true,
		'VALUE_REQUIRED_MODE' => isset($arParams['~VALUE_REQUIRED_MODE']) && $arParams['~VALUE_REQUIRED_MODE'] === true,
		'ENABLE_LIVE_SEARCH' => isset($arParams['~ENABLE_LIVE_SEARCH']) && $arParams['~ENABLE_LIVE_SEARCH'] === true,
		'ENABLE_LABEL' => true
	),
	$component
);
//endregion

if(!defined("PROJECT_MANAGER_GROUP"))
	define("PROJECT_MANAGER_GROUP", 23);

global $USER;
$arGroups = $USER->GetUserGroupArray();
?>
	<?php if(in_array(PROJECT_MANAGER_GROUP, $arGroups)):?>
		<div id="toolbar_lead_list" class="pagetitle-container pagetitle-align-right-container">	
			<a href="/crm/project/project-manager-export/" title="Export Project">
				<span class="webform-small-button webform-small-button-blue bx24-top-toolbar-add crm-deal-add-button">Export</span>
			</a>
		</div>
	<?php endif;?>
</div><?
$this->EndViewTarget();