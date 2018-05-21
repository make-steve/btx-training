<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Tasks\Integration\Bitrix24;

Bitrix24\UI::initLicensePopup('');
?>
<?if(!$arResult['COMPONENT_DATA']['RESTRICTION']['USE']):?>
	<div class="tasks-btn-restricted tasks-uf-panel-restricted">
		<?=Loc::getMessage('TASKS_TUFP_LICENSE_RESTRICTED');?> <a href="<?=Bitrix24\UI::getLicenseUrl()?>" target="_blank"><?=Loc::getMessage('TASKS_TUFP_SHOW_DETAILS');?></a>
	</div>
<?else:?>
	<?$arResult['HELPER']->displayFatals();?>
	<?if(!$arResult['HELPER']->checkHasFatals()):?>
		<?$arResult['HELPER']->displayWarnings();?>

		<?$canUse = $arResult['TEMPLATE_DATA']['CAN_USE'];?>

		<div id="<?=$arResult['HELPER']->getScopeId()?>" class="tasks">

			<div class="tasks-uf-panel">

				<?if($canUse):?>

					<a href="javascript:void(0);" class="js-id-uf-panel-action tasks-btn-customize tasks-uf-panel-settings"></a>

					<?ob_start();?>
                    <?php
                    $cstyle = '';
                    ?>
					<div class="js-id-item-set-item js-id-item-set-item-{{VALUE}} tasks-uf-panel-row tasks {{DEFACEABLE}} {{REQUIRED}} {{EDIT}} {{INVISIBLE}}" data-type="{{USER_TYPE_ID}}" data-multiple="{{DISPLAY_MULTIPLE}}" data-item-value="{{VALUE}}" style="<?=$cstyle?>">
						<div class="tasks-uf-panel-row-geometry">
							<div class="js-id-uf-panel-item-drag tasks-btn-drag"></div>
							<div class="tasks-uf-panel-row-title">
								<div class="tasks-uf-panel-row-title-text">
									<span class="tasks-uf-panel-row-title-red-star tasks-red">*</span>
									<span class="js-id-item-set-item-label">{{DISPLAY}}</span>
								</div>
								<div class="tasks-uf-panel-row-title-edit">
									<input class="js-id-item-set-item-label-edit js-id-uf-panel-item-label-edit" type="text" value="" maxlength="255" />
								</div>
							</div>
							<div class="tasks-uf-panel-row-data">
								<div class="tasks-uf-panel-row-data-value">
									<div class="js-id-item-set-item-field-html">
										{{{FIELD_HTML}}}
									</div>
									<div class="tasks-uf-panel-row-data-value-overlay"></div>
								</div>
								<div class="js-id-item-set-item-form-place tasks-uf-panel-row-data-form-place invisible">
									<div class="js-id-item-set-item-form tasks-uf-panel-form">
										<div class="tasks-uf-panel-form-flags">
											<label title="<?=Loc::getMessage('TASKS_TUFP_FIELD_MULTIPLE_HINT')?>"><input class="js-id-item-set-item-multiple-edit" type="checkbox" /><?=Loc::getMessage('TASKS_TUFP_FIELD_MULTIPLE')?></label>
											<?$createMandatory = $arResult['COMPONENT_DATA']['RESTRICTION']['CREATE_MANDATORY'];?>
											<span <?if(!$createMandatory):?>class="tasks-btn-restricted" title="<?=Loc::getMessage('TASKS_TUFP_LICENSE_RESTRICTED_MANDATORY')?>"<?endif?>>
											<label <?if(!$createMandatory):?>class="disabled"<?endif?>><input class="js-id-item-set-item-required-edit" type="checkbox" <?if(!$createMandatory):?>disabled="disabled"<?endif?>/><?=Loc::getMessage('TASKS_TUFP_FIELD_MANDATORY')?></label>
										</span>
										</div>
										<div class="tasks-uf-panel-form-buttons">
											<button type="button" class="js-id-item-set-item-save webform-small-button"><?=Loc::getMessage('TASKS_COMMON_SAVE')?></button>
											<a class="js-id-item-set-item-cancel tasks-btn-cancel" href="javascript:void(0);"><?=Loc::getMessage('TASKS_COMMON_CANCEL')?></a>
										</div>
										<div class="js-id-item-set-item-error task-message-label error invisible">
										</div>
									</div>
								</div>
							</div>
							<?/*?>
							<div class="tasks-uf-panel-row-buttons">
								<a href="javascript:void(0);" class="js-id-item-set-item-hide tasks-btn-delete tasks-uf-panel-row-button-delete" title="<?=Loc::getMessage('TASKS_TUFP_FIELD_HIDE')?>"></a>
								<?if($arResult['TEMPLATE_DATA']['CAN_EDIT']):?>
									<a href="javascript:void(0);" class="js-id-item-set-item-edit tasks-btn-edit tasks-uf-panel-row-button-edit" title="<?=Loc::getMessage('TASKS_TUFP_FIELD_EDIT')?>"></a>
								<?endif?>
							</div>
							<?*/?>
							<div class="tasks-uf-panel-dnd-after"></div>
						</div>
					</div>
					<?$rowTemplate = ob_get_clean();?>

					<div class="js-id-item-set-items js-id-uf-panel-items tasks-uf-panel-items not-empty">

						<div class="tasks-uf-panel-dnd-after panel"></div>

						<?//todo: migrate to <template> tag when get supported?>
						<script data-bx-id="item-set-item" type="text/html">
							<?=$rowTemplate?>
						</script>
						<script data-bx-id="uf-panel-item-flying" type="text/html">
							<div class="tasks-uf-panel tasks flying">
								<div class="tasks-uf-panel-row">
									<div class="tasks-uf-panel-row-geometry">
										<div class="tasks-btn-drag"></div>
										<div class="tasks-uf-panel-row-title">
											<div class="tasks-uf-panel-row-title-text">
												{{LABEL}}
											</div>
										</div>
									</div>
								</div>
							</div>
						</script>
						<script data-bx-id="item-set-item-field-stub" type="text/html">
							<input class="tasks-uf-panel-row-data-field-stub" type="text" data-type="string double datetime" />
							<label class="tasks-uf-panel-row-data-field-stub fields" data-type="boolean">
								<input type="checkbox" />
							</label>
						</script>

						<?

							$arCalFields = $arParams['TASKS_CALC_FIELDS'];
                            $tabindex = 100;

							foreach($arResult['DATA']['STATE'] as $id => $state):

							$uf = $arResult['DATA']['FIELDS'][$arResult['TEMPLATE_DATA']['ID2CODE'][$id]];
							$ufPublic = $arResult['JS_DATA']['scheme'][$id];
							$code = $uf['CODE'];
	
							$parent = "";
							$sub = array();
							$subMoreParams = array();
							$subTemplate = "";
							$isContinue = false;
							if(!empty($arParams['HIDDEN_FIELDS'])) {

								foreach($arParams['HIDDEN_FIELDS'] as $arField) {

									if($arField['PARENT'] == $arResult['TEMPLATE_DATA']['ID2CODE'][$id]) {

										$parent = $arField['PARENT'];
										$sub = $arField['SUB'];
										$subTemplate = $arField['TEMPLATE'];
										$subMoreParams = $arField['MORE_PARAMS'];
									}


									// this is to skip field that are part of sub fields
									if(in_array($arResult['TEMPLATE_DATA']['ID2CODE'][$id] , $arField['SUB']))
										$isContinue = true;
								}
							}

							if(!$uf || in_array($code, $arParams['EXCLUDE']) || $isContinue)
							{
								continue;
							}

							$html = '';

							if($state['D'])
							{
								if(in_array($uf['FIELD_NAME'], $arCalFields)) {

									$rendementField = $arParams['TASKS_RENDEMENT'];
									if($uf['FIELD_NAME'] == $rendementField) {
										$uf['SETTINGS']['DISABLED'] = 'Y';
										$uf['SETTINGS']['OTHER_CLASS'] = 'rendement_diff';
									}
									else {
										$uf['SETTINGS']['OTHER_CLASS'] = 'calc_rendement';
									}

									$uf['VALUE'] = ($uf['VALUE'] != "") ? formatMoney($uf['VALUE']) : ""; 
                                    $uf['SETTINGS']['TABINDEX'] = $tabindex;
                                    $tabindex++;
								}

								// set default value for fields
								if(isset($arParams['DEFAULT_VALUES']) && !empty($arParams['DEFAULT_VALUES'])) {

									if(array_key_exists($uf['FIELD_NAME'], $arParams['DEFAULT_VALUES'])) {
										$uf['SETTINGS']['DEFAULT_VALUE'] = $arParams['DEFAULT_VALUES'][$uf['FIELD_NAME']];	
										$uf['HAS_DEFAULT_VALUE'] = true; // hack to show the value
										//$uf['ENTITY_VALUE_ID'] = true; // hack to show the value

										$uf['VALUE'] = $arParams['DEFAULT_VALUES'][$uf['FIELD_NAME']];	
									}
								}

								$uf['FIELD_NAME'] = $arParams['INPUT_PREFIX'].'['.$uf['FIELD_NAME'].']';
                                
								ob_start();
								\Bitrix\Tasks\Util\UserField\UI::showEdit($uf, array(
									'PREFER_DEFAULT' => !intval($arParams['DATA']['ID'])
								), $this->__component);

							
								foreach($sub as $_sub) {

									$subUf = $arResult['DATA']['FIELDS'][$_sub];

									// add 1 item if the value is empty
									if(empty($subUf['VALUE'])) $subUf['VALUE'][] = "";

									echo "<span class='task-options-item-param-label'>".$subUf['EDIT_FORM_LABEL']."</span>";

									if($subTemplate == 'dropdown_text') {

										echo "<div id='HIDDEN_".$subUf['FIELD_NAME']."'>";
										echo "<input type='hidden' name='".$subUf['FIELD_NAME']."_multi_field_text' value='ACTION[0][ARGUMENTS][data][".$subUf['FIELD_NAME']."][]'/>";
										echo "<input type='hidden' name='".$subUf['FIELD_NAME']."_multi_field_dropdown' value='ACTION[0][ARGUMENTS][data][".$subUf['FIELD_NAME']."_DROPDOWN][]'/>";
										echo "<input type='hidden' name='".$subUf['FIELD_NAME']."_multi_field_dropdown_list' value='".json_encode($subMoreParams['DROPDOWN'])."'/>";
										echo "</div>";

										$_html = "";
										if(!empty($subUf['VALUE'])) {

											foreach($subUf['VALUE'] as $subValue) {

												$arSubValue = explode("-", $subValue);
												$dropVal = ($arSubValue[0] != "") ? $arSubValue[0] : "";
												$textVal = ($arSubValue[1] != "") ? $arSubValue[1] : "";

												$textfieldName = "ACTION[0][ARGUMENTS][data][".$subUf['FIELD_NAME']."][]";
												$dropfieldName = "ACTION[0][ARGUMENTS][data][".$subUf['FIELD_NAME']."_DROPDOWN][]";

												$_html .= "<div class='tasks-uf-panel-row-data-more-field'>";
												// field dropdown
												$_html .= "<div class='tasks-uf-panel-row-data-more-dropdown'>";

												if(!empty($subMoreParams['DROPDOWN'])) {

													$_html .= "<select class='kosten_more_info' name='".$dropfieldName."'>";

														foreach($subMoreParams['DROPDOWN'] as $subDpKey => $subDpVal) {

															$isSelected = "";
															if($dropVal == $subDpKey) $isSelected = "selected";

															$_html .= "<option value='".$subDpKey."' ".$isSelected." >".$subDpVal."</option>";
														}

													$_html .= "</select>";
												}
												
												$_html .= "</div>";
												$_html .= "<div class='tasks-uf-panel-row-data-more-value'>";
												$_html .= "<div class='fields string'><span class='task_currency'>€</span>";
												$_html .= "<input name='".$textfieldName."' value='".$textVal."' size='".$subUf['SETTINGS']['SIZE']."' class='fields string calc_money' tabindex='".$tabindex."' type='text'/>";
												$_html .= "<span class='delete-dropdown-text' title='delete item'></span>";
												$_html .= "</div></div></div>";
											}
										}
									}

									echo "<div id='CONT_".$subUf['FIELD_NAME']."' class='kosten_uren_info_cont'>".$_html."</div>";

									if($subUf['MULTIPLE'] == 'Y') {
										
										echo "<div class='task-dashed-link-inner-add-cont'><span id='ADD_".$subUf['FIELD_NAME']."' class='task-dashed-link-inner-add add_dropdown_text'>Add</span></div>";
									}
								}
								
								$html = ob_get_clean();

								// replace date icon, no ability to do it in other way
								$html = str_replace('/bitrix/js/main/core/images/calendar-icon.gif', '/bitrix/js/tasks/css/images/calendar.png', $html);
							}
							?>

							<?=$arResult['HELPER']->fillTemplate($rowTemplate, array(
								'USER_TYPE_ID' => $ufPublic['USER_TYPE_ID'],
								'MULTIPLE' => $ufPublic['MULTIPLE'] ? '1' : '0',
								'FIELD_HTML' => $html,
								'DISPLAY' => $ufPublic['LABEL'],
								'VALUE' => $ufPublic['ID'],

								// template logic emulation
								'REQUIRED' => $ufPublic['MANDATORY'] ? 'required' : '',
								'EDIT' => '',
								'INVISIBLE' => $state['D'] ? '' : 'invisible',
								'DEFACEABLE' => in_array($ufPublic['USER_TYPE_ID'], $arResult['JS_DATA']['defaceable']) ? 'defaceable': '',
								'DISPLAY_MULTIPLE' => $ufPublic['MULTIPLE'] || $ufPublic['USER_TYPE_ID'] == 'enumeration' ? '1' : '0',
							));?>

						<?endforeach?>
					</div>

					<div class="tasks-uf-panel-new-item-place js-id-item-set-new-item-place js-id-uf-panel-new-item-place">
					</div>

				<?endif?>

				<?//action buttons?>
				<div class="tasks-uf-panel-bottom-actions<?if(!$canUse):?> tasks-uf-panel-bottom-actions-off<?endif?>">

					<?if($canUse):?>
						<?if($arResult['AUX_DATA']['USER']['IS_SUPER']):?>
							<span class="<?if(!$arResult['COMPONENT_DATA']['RESTRICTION']['MANAGE']):?>tasks-btn-restricted<?endif?>">
								<a class="tasks-uf-panel-btn-action js-id-uf-panel-add-field" href="javascript:void(0);"><?=Loc::getMessage('TASKS_TUFP_FIELD_ADD')?></a>
							</span>
						<?endif?>
						<a class="js-id-uf-panel-un-hide-field" href="javascript:void(0);"><?=Loc::getMessage('TASKS_TUFP_FIELD_UN_HIDE')?></a>
					<?else:?>
						<?=Loc::getMessage('TASKS_TUFP_NO_FIELDS_TO_SHOW');?>
					<?endif?>

				</div>

				<?// contents for un-hide-item popup?>
				<div class="js-id-uf-panel-un-hide-menu no-display">
					<div class="js-id-scrollpane-pane menu-popup tasks-uf-panel-scrollpane tasks-scrollpane">
						<div class="js-id-scrollpane-body js-id-uf-panel-uhmenu menu-popup-items tasks-scrollpane-body">
							<script data-bx-id="uf-panel-menu-item" type="text/html">
								<span title="{{LABEL_EXT}}" data-id="{{ID}}" class="js-id-scrollpane-item menu-popup-item menu-popup-no-icon">
							<span class="menu-popup-item-text">
								<span class="tasks-red {{STAR_INVISIBLE}}">*</span>&nbsp;{{LABEL}}
							</span>
						</span>
							</script>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?$arResult['HELPER']->initializeExtension();?>

	<?endif?>
<?endif?>