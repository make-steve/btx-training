<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->AddHeadScript('/bitrix/js/crm/interface_form.js');
$APPLICATION->AddHeadScript('/bitrix/js/crm/common.js');
$APPLICATION->AddHeadScript('/bitrix/js/main/dd.js');

$entityTypeName = $arResult['ENTITY_TYPE_NAME'];
$entityTypeID = $arResult['ENTITY_TYPE_ID'];
$entityID = $arResult['ENTITY_ID'];
$entityFields = $arResult['ENTITY_FIELDS'];
$entityData = $arResult['ENTITY_DATA'];

$entityContext = $arResult['ENTITY_CONTEXT'];

$guid = $arResult['GUID'];
$innerWrapperClassName = 'crm-project-header-table crm-project-header-offer';
$config = $arResult['CONFIG'];

$isExpanded = $config['expanded'] === 'Y';
$isFixed = $config['fixed'] === 'Y';

$headerConfig = array();

$arFields = $arParams['ENTITY_FIELDS'];
?>
<?/*?>
<div class="bx-interface-form">
	<div class="message">
		<span class="message-content">Er zijn gegevens die aangevuld moeten worden!</span>
		<span class="crm-offer-title-set-wrap"><span id="section_deal_info_delete" class="crm-offer-title-del"></span></span>
	</div>
</div><?*/?>
<div id="<?="{$guid}_placeholder"?>" class="crm-project-header-table-placeholder">
<div id="<?="{$guid}_wrap"?>" class="crm-project-header-table-wrap">
	<div class="crm-project-header-table-inner-wrap">
		<table id="<?="{$guid}_inner_wrap"?>" class="<?=$innerWrapperClassName?>" border="0">
		<tbody>
			<tr id="<?="{$guid}_header"?>">
				<td class="crm-project-header-header" colspan="2">
					<div class="crm-project-header-header-left">
						<div class="crm-project-header-left-inner">
							<div id="<?="{$guid}_title"?>" class="crm-project-header-title">
								<span class="crm-project-header-title-text">Project overzicht</span>
							</div>
						</div>
					</div>
					<div class="crm-project-header-header-right"><div class="crm-project-header-right-inner"><?
						?><div class="crm-project-header-contact-btns">
							<span id="<?="{$guid}_menu_btn"?>" class="crm-lead-header-contact-btn crm-lead-header-contact-btn-menu"></span>
							<span id="<?="{$guid}_pin_btn"?>" class="crm-lead-header-contact-btn <?=$isFixed ? 'crm-lead-header-contact-btn-pin' : 'crm-lead-header-contact-btn-unpin'?>"></span>
							<span id="<?="{$guid}_toggle_btn"?>" class="crm-lead-header-contact-btn <?=$isExpanded ? 'crm-lead-header-contact-btn-open' : 'crm-lead-header-contact-btn-close'?>"></span>
						</div>
					</div></div>
				</td>
			</tr>
			<tr><td class="crm-project-header-white" colspan="2"></td></tr>
			<tr><td class="crm-project-header-blue" colspan="2"></td></tr>
			<tr>
				<td class="crm-project-header-cell project-col-1">
					<table id="<?="{$guid}_left_container"?>" class="crm-project-header-inner-table project-detail-container">
						<tbody>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_TECHNOLOGY')?></td>
								<td class="project-detail-value"><?=$arFields['TECHNOLOGY']?></td>
							</tr>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_PRODUCT_GROUP')?></td>
								<td class="project-detail-value"><?=$arFields['PRODUCT_GROUP']?></td>
							</tr>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_CUSTOMER_NAME')?></td>
								<td class="project-detail-value"><?=$arFields['CUSTOMER_NAME']?></td>
							</tr>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_CONTACT_PERSON')?></td>
								<td class="project-detail-value"><?=$arFields['CONTACT_PERSON']?></td>
							</tr>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_POST_ADD')?></td>
								<td class="project-detail-value"><?=$arFields['CONTACT_POSTADD']?></td>
							</tr>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_POSTCODE_PLACE')?></td>
								<td class="project-detail-value"><?=$arFields['CONTACT_POSTCODE'].' '.$arFields['CONTACT_PLAATS']?></td>
							</tr>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_TELEPHONE')?></td>
								<td class="project-detail-value"><?=$arFields['CONTACT_TELEPHONE']?></td>
							</tr>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_EMAIL')?></td>
								<td class="project-detail-value"><?=$arFields['CONTACT_EMAIL']?></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td class="crm-project-header-cell project-col-2">
					<table id="<?="{$guid}_left_container"?>" class="crm-project-header-inner-table project-detail-container">
						<tbody>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_VISITING_ADD')?></td>
								<td class="project-detail-value"><?=$arFields['COMPANY_BEZOEKADRES']?></td>
							</tr>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_VISITING_POSTCODE_PLACE')?></td>
								<td class="project-detail-value"><?=$arFields['COMPANY_POSTCODE'].' '.$arFields['COMPANY_PLAATS']?></td>
							</tr>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_COMPANY_INVOICE')?></td>
								<td class="project-detail-value"><?=$arFields['COMPANY_FACTUURBEDRIJF']?></td>
							</tr>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_COMPANY_BILL_ADD')?></td>
								<td class="project-detail-value"><?=$arFields['COMPANY_FACTUURADRES']?></td>
							</tr>
							<tr>
								<td class="project-detail-label"><?=GetMessage('CRM_PROJECT_COMPANY_TAV')?></td>
								<td class="project-detail-value"><?=$arFields['COMPANY_TAV']?></td>
							</tr>
							<tr>
								<td class="project-detail-label">&nbsp;</td>
								<td class="project-detail-value">&nbsp;</td>
							</tr>
							<tr>
								<td class="project-detail-label">&nbsp;</td>
								<td class="project-detail-value">&nbsp;</td>
							</tr>
							<tr>
								<td class="project-detail-label">&nbsp;</td>
								<td class="project-detail-value">&nbsp;</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr><td class="crm-project-header-blue" colspan="2"></td></tr>
			<tr><td class="crm-project-header-blue" colspan="2"></td></tr>
		</tbody>
	</table>
	</div>
</div>
</div>
<div id="<?="{$guid}_message_wrap"?>"></div>
<?

$sipData = isset($entityContext['SIP_MANAGER_CONFIG']) ? $entityContext['SIP_MANAGER_CONFIG'] : array();
if(!empty($sipData)):
?><script type="text/javascript">
	BX.ready(
			function()
			{
				var mgr = BX.CrmSipManager.getCurrent();<?
				foreach($sipData as $item):
				?>
				mgr.setServiceUrl(
					"CRM_<?=CUtil::JSEscape($item['ENTITY_TYPE'])?>",
					"<?=isset($item['SERVICE_URL']) ? CUtil::JSEscape($item['SERVICE_URL']) : ''?>"
				);<?
				endforeach;
				?>
				if(typeof(BX.CrmSipManager.messages) === 'undefined')
				{
					BX.CrmSipManager.messages =
					{
						"unknownRecipient": "<?= GetMessageJS('CRM_ENTITY_QPV_SIP_MGR_UNKNOWN_RECIPIENT')?>",
						"makeCall": "<?= GetMessageJS('CRM_ENTITY_QPV__SIP_MGR_MAKE_CALL')?>"
					};
				}
			}
	);
</script><?
endif;
?><script type="text/javascript">
	BX.ready(
		function() {
			BX.CrmQuickPanelModel.messages =
			{
				notSelected: "<?=GetMessageJS('CRM_ENTITY_QPV_NOT_SELECTED')?>"
			};

			BX.CrmQuickPanelItem.messages =
			{
				editMenuItem: "<?=CUtil::JSEscape(GetMessage('CRM_ENTITY_QPV_EDIT_CONTEXT_MENU_ITEM'))?>",
				deleteMenuItem: "<?=CUtil::JSEscape(GetMessage('CRM_ENTITY_QPV_DELETE_CONTEXT_MENU_ITEM'))?>",
				deletionConfirmation: "<?=GetMessageJS('CRM_ENTITY_QPV_DELETION_CONFIRMATION')?>"
			};

			BX.CrmQuickPanelControl.messages =
			{
				dataNotSaved: "<?=GetMessageJS('CRM_ENTITY_QPV_CONTROL_FIELD_DATA_NOT_SAVED')?>",
				notSelected: "<?=GetMessageJS('CRM_ENTITY_QPV_NOT_SELECTED')?>",
				yes: "<?=GetMessageJS('MAIN_YES')?>",
				no: "<?=GetMessageJS('MAIN_NO')?>"
			};

			BX.CrmQuickPanelResponsible.messages =
			{
				change: "<?=GetMessageJS('CRM_ENTITY_QPV_RESPONSIBLE_CHANGE')?>"
			};

			BX.CrmQuickPanelClientInfo.messages =
			{
				contactNotSelected: "<?=GetMessageJS('CRM_ENTITY_QPV_CONTACT_NOT_SELECTED')?>",
				companyNotSelected: "<?=GetMessageJS('CRM_ENTITY_QPV_COMPANY_NOT_SELECTED')?>"
			};

			BX.CrmQuickPanelView.messages =
			{
				resetMenuItem: "<?=CUtil::JSEscape(GetMessage('CRM_ENTITY_QPV_RESET_MENU_ITEM'))?>",
				saveForAllMenuItem: "<?=CUtil::JSEscape(GetMessage('CRM_ENTITY_QPV_SAVE_FOR_ALL_MENU_ITEM'))?>",
				resetForAllMenuItem: "<?=CUtil::JSEscape(GetMessage('CRM_ENTITY_QPV_RESET_FOR_ALL_MENU_ITEM'))?>",
				dragDropErrorTitle: "<?=CUtil::JSEscape(GetMessage('CRM_ENTITY_QPV_DRAG_DROP_ERROR_TITLE'))?>",
				dragDropErrorFieldNotSupported: "<?=CUtil::JSEscape(GetMessage('CRM_ENTITY_QPV_DRAG_DROP_ERROR_FIELD_NOT_SUPPORTED'))?>",
				dragDropErrorFieldAlreadyExists: "<?=CUtil::JSEscape(GetMessage('CRM_ENTITY_QPV_DRAG_DROP_ERROR_FIELD_ALREADY_EXISTS'))?>"
			};

			BX.CrmQuickPanelView.create(
				"<?=CUtil::JSEscape($guid)?>",
				{
					entityTypeName: "<?=CUtil::JSEscape($entityTypeName)?>",
					entityId: <?=CUtil::JSEscape($entityID)?>,
					prefix: "<?=CUtil::JSEscape($guid)?>",
					canSaveSettingsForAll: <?=$arResult['CAN_EDIT_OTHER_SETTINGS'] ? 'true' : 'false'?>,
					formId: "<?=CUtil::JSEscape($arResult['FORM_ID'])?>",
					entityData: <?=CUtil::PhpToJSObject($arResult['ENTITY_DATA'])?>,
					config: <?=CUtil::PhpToJSObject($config)?>,
					headerConfig: <?=CUtil::PhpToJSObject($headerConfig)?>,
					enableInstantEdit: <?=$arResult['ENABLE_INSTANT_EDIT'] ? 'true' : 'false'?>,
					serviceUrl: "<?='/bitrix/components/bitrix/crm.entity.quickpanelview/settings.php?'.bitrix_sessid_get()?>"
				}
			);

			BX.CrmDragDropBin.messages =
			{
				prompting: "<?=GetMessageJS("CRM_ENTITY_QPV_DD_BIN_PROMPTING")?>"
			};
			BX.CrmDragDropBin.getInstance().showPromptingIfRequired(BX("<?="{$guid}_message_wrap"?>"));
		}
	);
</script>