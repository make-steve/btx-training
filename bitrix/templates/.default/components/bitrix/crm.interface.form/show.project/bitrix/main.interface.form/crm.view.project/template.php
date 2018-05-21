<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
global $APPLICATION, $USER;
$APPLICATION->AddHeadScript('/bitrix/js/crm/interface_form.js');
$APPLICATION->AddHeadScript('http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/components/bitrix/crm.interface.form/show.project/bitrix/main.interface.form/crm.view.project/slider.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/components/bitrix/crm.interface.form/show.project/bitrix/main.interface.form/crm.view.project/script.js');

$jsCoreInit = array('date', 'popup', 'ajax', 'crm_activity_planner', 'crm_visit_tracker');

CJSCore::Init($jsCoreInit);
CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/activity.js');
CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/crm.js');
CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/communication_search.js');
CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/common.js');

$PROJECT_FINANCIAL = $arParams['DATA_ALL']['PROJECT_FINANCIAL'];
$DEAL_FINANCIAL = $arParams['DATA_ALL']['DEAL_FINANCIAL'];
$TASK_FINANCIAL = $arParams['DATA_ALL']['TASK_FINANCIAL'];

$arDeal = $arParams['DATA_ALL']['DEALS'];

$arProjectDealSettings = $arParams["DATA_SETTINGS"];

$arProject = $arParams['DATA'];
$userID = $USER->GetID();
?>
<br/>
	<div class="crm-project-financial-table-placeholder">
		<div id="wrap" class="crm-project-financial-table-wrap">
			<div class="crm-project-financial-table-inner-wrap">

			<?php if(count($TASK_FINANCIAL) > 0):?>
	
				<table class="crm-project-financial-table" border="0" width="100%" cellspacing="0">
					<tbody>
						<tr>
							<td class="crm-project-financial-header">
								<div class="crm-project-financial-header-left">
									<div class="crm-project-financial-header-left-inner">
										<div id="title" class="crm-project-financial-header-title">
											<span class="crm-project-financial-header-title-text">Financieel overzicht totaal</span>
											<span id="deal_finan_showhide" class="crm-lead-header-contact-btn finanhide"></span>
										</div>
									</div>
								</div>
								<div class="crm-project-header-header-right">
									<div class="crm-project-header-right-inner">
										<div class="crm-project-header-contact-btns">
										</div>
									</div>
								</div>
							</td>
						</tr>
						<tr><td class="crm-project-financial-padding-blue"></td></tr>
						<tr id="project_financial_container">
							<td class="crm-project-financial-content-cell">
								<table class="crm-project-financial-container" border="0" cellspacing="0">
									<tbody>
										<tr>
											<td width="25%">&nbsp;</td>
											<td width="27%">&nbsp;</td>
											<td width="16%" class="project-financial-content-head"><?=GetMessage('FINANCIAL_HEAD_BUDGET')?></td>
											<td width="16%" class="project-financial-content-head"><?=GetMessage('FINANCIAL_HEAD_REAL')?></td>
											<td width="16%" class="project-financial-content-head"><?=GetMessage('FINANCIAL_HEAD_DIFFERENCE')?></td>
										</tr>
										<?foreach($PROJECT_FINANCIAL as $financialKey => $financial):

											$financialData = $financial['DATA'];
										?>
											<tr>
												<td class="project-financial-content-label <?=$financial['CLASS']?>"><?=GetMessage("FINANCIAL_LABEL_" . $financialKey)?></td>
												<td class="<?=$financial['CLASS']?>">&nbsp;</td>
												<td class="project-financial-content-value <?=$financial['CLASS']?>"><?=($financialKey == 'BILLED' || $financialKey == 'PAID') ? " - " : "€ " . formatMoney($financialData['BUDGET'])?></td>
												<td class="project-financial-content-value <?=$financial['CLASS']?>">€ <?=formatMoney($financialData['ACTUAL'])?></td>
												<td class="project-financial-content-value <?=$financial['CLASS']?>"><?=($financialKey == 'BILLED' || $financialKey == 'PAID') ? " - " : "€ " . formatMoney($financialData['DIFF'])?></td>
											</tr>
										<?endforeach;?>
									</tbody>
								</table>
							</td>
						</tr>
						<tr><td class="crm-project-financial-padding-blue"></td></tr>
					</tbody>
				</table>
			<?php endif;?>
		</div>
	</div>
</div>
<br/>


<div id="bx-crm-project-form">
	<?
	foreach($arDeal as $dealKey => $dealValue):

		$settings = " crm-lead-header-contact-btn-open";
		$taskSettings = 'style="display: none"';

		if(!empty($arProjectDealSettings[$dealValue['ID']])) {
			$settings = $arProjectDealSettings[$dealValue['ID']];

			if(!empty($dealValue['TASK'])) {
				$settings = " crm-lead-header-contact-btn-close";
				$taskSettings = 'style="display: table-row"';
			}
			else {
				if($arProjectDealSettings[$dealValue['ID']] == " crm-lead-header-contact-btn-close"){
					$taskSettings = 'style="display: none"';
					$settings = " crm-lead-header-contact-btn-open";
				}
				else {
					$taskSettings = 'style="display: table-row"';
					$settings = " crm-lead-header-contact-btn-close";
				}
			}
		}
		else
		{
			$settings = " crm-lead-header-contact-btn-close";
			$taskSettings = 'style="display: table-row"';
		}

	?>
	<div class="crm-project-deal-table-placeholder">
		<div class="crm-project-deal-table-wrap">
			<div class="crm-project-deal-table-inner-wrap">
				<table class="crm-project-deal-table crm-project-deal" border="0" width="100%" cellspacing="0">
					<tbody>
						<tr>
							<td class="crm-project-deal-header">
								<div class="crm-project-deal-header-left">
									<div class="crm-project-deal-header-left-inner">
										<div id="title" class="crm-project-deal-header-title">
											<span class="crm-project-deal-header-title-text">Taken - Deal nr.<?=$dealValue['ID']?> [<?=$dealValue['TITLE']?>]</span> 
											<a href="/crm/deal/show/<?=$dealValue['ID']?>/" class="open_deal" target="_blank" title="<?=$dealValue['TITLE']?>"></a>
											<span onclick="showDealTasks(this)" rel="<?=$arProject['ID']?>-<?=$dealValue['ID']?>" id="_projectdeal_toggle_btn" class="crm-lead-header-contact-btn<?php echo $settings;?>"></span>
										</div>
									</div>
								</div>
								<div class="crm-project-header-header-right">
									<div class="crm-project-header-right-inner">
										<div class="crm-project-header-contact-btns">
									
										</div>
									</div>
								</div>
							</td>
						</tr>

						<tr class="crm_project_deal_<?=$dealValue['ID']?>_container" <?php echo $taskSettings;?>>
							<?php if(count($TASK_FINANCIAL[$dealValue['ID']]) > 0) : ?>
								<td class="crm-project-deal-detail">
									<table class="crm-project-deal-financial-table" border="0" width="100%">
										<tbody>
											<tr>
												<td class="crm-project-deal-financial-header">
													<div class="crm-project-deal-financial-header-left">
														<div class="crm-project-deal-financial-header-left-inner">
															<div id="title" class="crm-project-deal-financial-header-title">
																<span class="crm-project-deal-financial-header-title-text">Financieel overzicht totaal</span>
															</div>
														</div>
													</div>
													<div class="crm-project-deal-financial-header-right">
														<div class="crm-project-header-right-inner">
															<div class="crm-project-header-contact-btns">
														
															</div>
														</div>
													</div>
												</td>
											</tr>
											<tr><td class="crm-project-deal-financial-padding-blue"></td></tr>
											<tr>
												<td class="crm-project-deal-financial-content-cell">
													<table class="crm-project-deal-financial-container" width="100%" border="0" cellspacing="0">
														<tbody>
															<tr>
																<td width="25%">&nbsp;</td>
																<td width="27%">&nbsp;</td>
																<td width="16%" class="project-deal-financial-head"><?=GetMessage('FINANCIAL_HEAD_BUDGET')?></td>
																<td width="16%" class="project-deal-financial-head"><?=GetMessage('FINANCIAL_HEAD_REAL')?></td>
																<td width="16%" class="project-deal-financial-head"><?=GetMessage('FINANCIAL_HEAD_DIFFERENCE')?></td>
															</tr>
															<?foreach($DEAL_FINANCIAL[$dealValue['ID']] as $financialKey => $financial):

																$financialData = $financial['DATA'];
															?>
																<tr>
																	<td class="project-deal-financial-label <?=$financial['CLASS']?>"><?=GetMessage("FINANCIAL_LABEL_" . $financialKey)?></td>
																	<td class="<?=$financial['CLASS']?>"">&nbsp;</td>
																	<td class="project-deal-financial-value <?=$financial['CLASS']?>"><?=($financialKey == 'BILLED' || $financialKey == 'PAID') ? " - " : "€ " . formatMoney($financialData['BUDGET'])?></td>
																	<td class="project-deal-financial-value <?=$financial['CLASS']?>">€ <?=formatMoney($financialData['ACTUAL'])?></td>
																	<td class="project-deal-financial-value <?=$financial['CLASS']?>"><?=($financialKey == 'BILLED' || $financialKey == 'PAID') ? " - " : "€ " . formatMoney($financialData['DIFF'])?></td>
																</tr>
															<?endforeach;?>
														</tbody>
													</table>
												</td>
											</tr>
											<tr><td class="crm-project-deal-financial-padding-blue"></td></tr>
										</tbody>
									</table>
								</td>
							<?php else : ?>
								<td>&nbsp;</td>
							<?php endif; ?>
						</tr>

						<?
						$countKey = 1;
						foreach($dealValue['TASK'] as $arTaskKey => $arTaskValue):?>
						<tr class="crm_project_deal_<?=$dealValue['ID']?>_container" <?php echo $taskSettings;?>>
							<td class="crm-project-deal-task">
								<table class="crm-project-deal-task-table" border="0" width="100%"  cellspacing="0">
									<tbody>
										<tr>
											<td class="crm-project-deal-task-header">
												<div class="crm-project-deal-task-header-left">
													<div class="crm-project-deal-task-header-left-inner">
														<div id="title" class="crm-project-deal-task-header-title">
															<span class="crm-project-deal-task-header-title-text"><a href="/company/personal/user/<?php echo $arTaskValue["CREATED_BY_ID"]?>/tasks/task/view/<?php echo $arTaskValue["ID"]?>/" class="task-detail-title"><?=$arTaskValue['SUBJECT']?></a></span>
														</div>
													</div>
												</div>
												<div class="crm-project-header-header-right">
													<div class="crm-project-header-right-inner">
														<div class="crm-project-header-contact-btns">
															<?php 
															$checkLock = checkTaskLock($arTaskValue["ID"]);
															if($checkLock) {
																$set = "0"; #reverse unlock
																$lockString = "lock";
															}
															else {
																$set = "1"; #reverse unlock
																$lockString = "unlock";
															}

															if($userID == $arTaskValue["CREATED_BY_ID"] || $userID == $arTaskValue["RESPONSIBLE_ID"] || $USER->isAdmin()):?>
																<a rel="<?php echo $arTaskValue["ID"]?>" onclick="lockTaskAction(this, <?php echo $set?>)" class="crm-tasks-title-<?php echo $lockString?>"></a>
																<a href="/company/personal/user/<?php echo $arTaskValue["CREATED_BY_ID"]?>/tasks/task/edit/<?php echo $arTaskValue["ID"]?>/">
																	<span id="section_deal_info_edit" class="crm-tasks-title-edit"></span>
																</a>
																<?//$canDeleteTask = isTaskAllowed2Delete($dealValue['ID'], $arTaskValue["ID"])?>
																<?if($arTaskValue["CAN_DELETE"] == 'Y'):?>
																	<a rel="<?php echo $arTaskValue["ID"]?>" onclick="delTaskAction(this)" class="crm-tasks-title-del"></a>
																<?endif;?>
															<?php endif;?>
														</div>
													</div>
												</div>
											</td>
										</tr>

										<tr><td class="crm-financial-project-task-header-white"></td></tr>
										<tr>
											<td class="crm-project-deal-task-content-cell">
												<div class="crm-project-deal-task-content-header-info">
													<div class="task-col task-col-2">
														<span class="task-col-label">Taak nummer</span>
														<span class="task-col-value"><a href="/company/personal/user/<?php echo $arTaskValue["CREATED_BY_ID"]?>/tasks/task/view/<?php echo $arTaskValue["ID"]?>/" class="task_detail"><?=$arTaskValue['TASK_NUMBER']?></a></span>
													</div>
													<div class="task-col task-col-2">
														<span class="task-col-label">Acquisiteur</span>
														<span class="task-col-value"><?=$arTaskValue['RESPONSIBLE_OBJ']['LINK']?></span>
													</div>
													<div class="task-col task-col-2">
														<span class="task-col-label">Projectleider</span>
														<span class="task-col-value"><?=$arTaskValue['CREATED_BY_OBJ']['LINK']?></span>
													</div>
													<div class="task-col task-col-4 position-right">
														<span class="task-col-label">Status</span>
														<span class="task-col-value"><?=$arTaskValue['STATUS']?></span>
													</div>
													<div class="clear"></div>
												</div>
												<div class="crm-project-deal-task-content-detail-info">
													<table class="crm-project-deal-task-content-detail-info-table" border="0" cellspacing="0">
														<tr>
															<th width="20%"></th>
															<th width="10%" class="project-deal-task-head"><?=GetMessage('FINANCIAL_HEAD_BUDGET')?></th>
															<th width="10%" class="project-deal-task-head"><?=GetMessage('FINANCIAL_HEAD_REAL')?></th>
															<th width="10%" class="project-deal-task-head"><?=GetMessage('FINANCIAL_HEAD_DIFFERENCE')?></th>
															<th width="50%">
																<div class="slider-container">
																	<table class="sliderpercentage">
																		<tbody>
																			<tr>
																				<td><span>0%</span></td>
																				<td><span>25%</span></td>
																				<td><span>50%</span></td>
																				<td><span>75%</span></td>
																				<td><img src="/assets/img/projectdealflaggraph.png" style="position: absolute;margin-top: -25px;"><span>100%</span></td>
																				<td><span>125%</span></td>
																				<td><span>>150%</span></td>
																			</tr>
																		</tbody>
																	</table>
																</div>
															</th>
														</tr>
														<?

														$countScale = 0;
														$arSkipItems = array('ACQUISITION_COSTS');
														foreach($TASK_FINANCIAL[$dealValue['ID']][$arTaskKey] as $taskKey => $task):

															if(in_array( $taskKey ,$arSkipItems )) continue;
															$taskData = $task['DATA'];

															if($countScale <= 3)
															{
																$actualData = (doubleval($taskData['ACTUAL']) / doubleval($taskData['BUDGET'])) * 100;

																$percentage = 0;

																//if(empty($actualData)) $actualData = rand(1, 149); #remove when using real data
																	
																$color = "";
																$width = ($actualData / 150) * 150;
																$percentage = 0;

																if($width < 100) $color = "";
																else if($width == 100) $color = "orange ";
																else $color = "red ";
															}

														?>
															<tr>
																<td class="project-deal-task-content-label <?=$task['CLASS']?>"><?=GetMessage("TASKS_LABEL_" . $taskKey)?></td>
																<td class="project-deal-task-content-value <?=$task['CLASS']?>"><?=($taskKey == 'BILLED' || $taskKey == 'PAID') ? " - " : "€ " . formatMoney($taskData['BUDGET'])?></td>
																<td class="project-deal-task-content-value <?=$task['CLASS']?>">€ <?=formatMoney($taskData['ACTUAL'])?></td>
																<td class="project-deal-task-content-value <?=$task['CLASS']?>"><?=($taskKey == 'BILLED' || $taskKey == 'PAID') ? " - " : "€ " . formatMoney($taskData['DIFF'])?></td>
																<td class="<?=$task['CLASS']?>">
																	<?php if($countScale <= 3):?>
																		<div class="slider-container">
																			<table class="sliderpercentage" cellspacing="0" cellpadding="0">
																				<tbody>
																					<tr>
																						<td>
																							<?php
																							$thisWidth = 0;
																							if($width >= 25)
																								$thisWidth = 100;
																							else if($width < 25)
																								$thisWidth = (($width - 0) / 25) * 100;

																							$borderRight = "";
																							if($thisWidth < 100 || $width == 25) 
																								$borderRight = 'class="tdradiusright"';

																							$addText = "";
																							$addTDBorder = "";
																							if($width >= 0 && $width <= 25)
																								$addText = "<div class='showpercentage' style='left: " . ($thisWidth + 8) . "%'>" . round($width) . "%</div>";
																						
																							?>
																							<div class="meter nostripes <?php echo $color?>firstd">
																								<div style="width: <?php echo $thisWidth;?>%" <?php echo $borderRight?>></div>
																								<?php echo $addText;?>
																							</div>
																						</td>
																						<td>
																							<?php
																							$thisWidth = 0;
																							if($width >= 50)
																								$thisWidth = 100;
																							else if($width > 25 && $width < 50)
																								$thisWidth = (($width - 25) / 25) * 100;

																							$borderRight = "";
																							if($thisWidth < 100 || $width == 50) 
																								$borderRight = 'class="tdradiusright"';

																							$addText = "";
																							$addTDBorder = "";
																							if($width > 25 && $width <= 50)
																								$addText = "<div class='showpercentage' style='left: " . ($thisWidth + 8) . "%'>" . round($width) . "%</div>";
																							
																							if($width < 25)
																								$addTDBorder = " showborderlines";
																							?>
																							<div class="meter nostripes <?php echo $color?>innertds<?php echo $addTDBorder?>">
																								<div style="width: <?php echo $thisWidth;?>%" <?php echo $borderRight?>></div>
																								<?php echo $addText;?>
																							</div>
																						</td>
																						<td>
																							<?php
																							$thisWidth = 0;
																							if($width >= 75)
																								$thisWidth = 100;
																							else if($width > 50 && $width < 75)
																								$thisWidth = (($width - 50) / 25) * 100;

																							$borderRight = "";
																							if($thisWidth < 100 || $width == 75) 
																								$borderRight = 'class="tdradiusright"';
																							
																							$addText = "";
																							$addTDBorder = "";
																							if($width > 50 && $width <= 75)
																								$addText = "<div class='showpercentage' style='left: " . ($thisWidth + 8) . "%'>" . round($width) . "%</div>";
																							
																							if($width < 50)
																								$addTDBorder = " showborderlines";
																							?>
																							<div class="meter nostripes <?php echo $color?>innertds<?php echo $addTDBorder?>">
																								<div style="width: <?php echo $thisWidth;?>%" <?php echo $borderRight?>></div>
																								<?php echo $addText;?>
																							</div>
																						</td>
																						<td>
																							<?php
																							$thisWidth = 0;
																							if($width >= 100)
																								$thisWidth = 100;
																							else if($width > 75 && $width < 100)
																								$thisWidth = (($width - 75) / 25) * 100;

																							$borderRight = "";
																							if($thisWidth < 100 || $width == 100) 
																								$borderRight = 'class="tdradiusright"';

																							$addText = "";
																							$addTDBorder = "";
																							if($width > 75 && $width <= 100)
																								$addText = "<div class='showpercentage' style='left: " . ($thisWidth + 8) . "%'>" . round($width) . "%</div>";
																							
																							if($width < 75)
																								$addTDBorder = " showborderlines";
																							?>
																							<div class="meter nostripes <?php echo $color?>innertds<?php echo $addTDBorder?>">
																								<div style="width: <?php echo $thisWidth;?>%" <?php echo $borderRight?>></div>
																								<?php echo $addText;?>
																							</div>
																						</td>
																						<td>
																							<?php
																							$thisWidth = 0;
																							if($width >= 125)
																								$thisWidth = 100;
																							else if($width > 100 && $width < 125)
																								$thisWidth =  (($width - 100) / 25) * 100;

																							$borderRight = "";
																							if($thisWidth < 100 || $width == 125) 
																								$borderRight = 'class="tdradiusright"';
																							
																							$addText = "";
																							$addTDBorder = "class='hideborderlinesflag'";
																							if($width > 100 && $width <= 125)
																								$addText = "<div class='showpercentage' style='left: " . ($thisWidth + 8) . "%'>" . round($width) . "%</div>";
																							
																							if($width < 100)
																								$addTDBorder = "class='showborderlinesflag'";
																							?>
																							<img src="/assets/img/finishline.png" <?php echo $addTDBorder?>/>
																							<div class="meter nostripes <?php echo $color?>innertds">
																								<div style="width: <?php echo $thisWidth;?>%" <?php echo $borderRight?>></div>
																								<?php echo $addText;?>
																							</div>
																						</td>
																						<td>
																							<?php
																							$thisWidth = 0;
																							if($width >= 150)
																								$thisWidth = 100;
																							else if($width > 125 && $width < 150)
																								$thisWidth = (($width - 125) / 25) * 100;

																							$borderRight = "";
																							if($thisWidth < 100 || $width == 150) 
																								$borderRight = 'class="tdradiusright"';

																							$addText = "";
																							$addTDBorder = "";
																							if($width > 125 && $width <= 150)
																								$addText = "<div class='showpercentage' style='left: " . ($thisWidth + 8) . "%'>" . intval($width) . "%</div>";
																							
																							if($width < 125)
																								$addTDBorder = " showborderlines";
																							?>
																							<div class="meter nostripes <?php echo $color?>innertds<?php echo $addTDBorder?>">
																								<div style="width: <?php echo $thisWidth;?>%" <?php echo $borderRight?>></div>
																								<?php echo $addText;?>
																							</div>
																						</td>
																						<td>
																							<?php
																							$thisWidth = 0;
																							if($width > 150) 
																								$thisWidth = 100;

																							$borderRight = "";
																							$addText = "";
																							$addTDBorder = "";
																							if($thisWidth == 100) {
																								$borderRight = 'class="tdradiusright"';
																								$addText = "<div class='showpercentagemax'>>150%</div>";
																							}
																							else
																								$addTDBorder = " showborderlinesend";
																							?>
																							<div class="meter nostripes <?php echo $color?>lasttd<?php echo $addTDBorder?>">
																								<div style="width: <?php echo $thisWidth;?>%" <?php echo $borderRight?>></div>
																								<?php echo $addText;?>
																							</div>
																						</td>
																					</tr>
																				</tbody>
																			</table>
																		</div>
																	<?php endif;?>
																</td>
															</tr>
															<?php $countScale++;?>
														<?endforeach;?>
													</table>
												</div>
												<div class="crm-project-deal-task-content-footer-info">
													<div class="task-col task-col-2">
														<span class="task-col-label">Klant</span>
														<?
														$taskCompanyName = $arProject['CUSTOMER_NAME'];
														if($arTaskValue['UF']['COMPANY_NAME'] != "") {
															$taskCompanyName = $arTaskValue['UF']['COMPANY_NAME'];
														}
														?>
														<span class="task-col-value"><?=$taskCompanyName?></span>
													</div>
													<div class="task-col task-col-2">
														<span class="task-col-label">Referentie klant</span>
														<span class="task-col-value"><?=$arTaskValue['UF']['REFERENTIE_KLANT']?></span>
													</div>
													<div class="task-col task-col-2">
														<span class="task-col-label">Contact person</span>
														<span class="task-col-value"><?=$dealValue['CONTACT_PERSON']['LINK']?></span>
													</div>
													<div class="task-col task-col-4 ">
														<span class="task-col-label">Toelichting facturatie</span>
														<span class="task-col-value"><?=$arTaskValue['UF']['FACTUURMOMENTEN']?></span>
													</div>
													<div class="clear"></div>
												</div>
											</td>
										</tr>
										<tr>
										</tr>
										<tr><td class="crm-financial-project-task-header-white"></td></tr>
									</tbody>
								</table>
							</td>
						</tr>
						<?$countKey++; endforeach;?>
						<tr class="crm_project_deal_<?=$dealValue['ID']?>_container" <?php echo $taskSettings;?>>
							<td>
								<?php
								$activityEditorID = "deal_" . $dealValue["ID"] . "_actions_grid_crm_activity_editor";
								$APPLICATION->IncludeComponent(
									'bitrix:crm.activity.editor',
									'projectcard',
									array(
										'EDITOR_ID' => $activityEditorID,
										'PREFIX' => "deal_" . $dealValue["ID"],
										'OWNER_TYPE' => 'DEAL',
										'OWNER_ID' => 0,
										'READ_ONLY' => false,
										'ENABLE_UI' => false,
										'ENABLE_TOOLBAR' => true,
										'DEAL_OWNER_ID' => $dealValue["ID"]
									),
									null,
									array('HIDE_ICONS' => 'Y')
								);

								?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<br/>
	<?endforeach;?> 
</div>