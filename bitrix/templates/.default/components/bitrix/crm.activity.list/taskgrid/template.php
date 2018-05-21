<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();


Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/autorun_proc.js');

Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/interface_grid.js');
if (CModule::IncludeModule('bitrix24') && !\Bitrix\Crm\CallList\CallList::isAvailable())
{
	CBitrix24::initLicenseInfoPopupJS();
}

use Bitrix\Crm\Activity\Provider\ProviderManager;

$isInternal = $arResult['IS_INTERNAL'];
$currentUserID = $arResult['CURRENT_USER_ID'] ;

$gridManagerID = $arResult['UID'].'_MANAGER';
$gridManagerCfg = array(
	'ownerType' => 'ACTIVITY',
	'gridId' => $arResult['UID'],
	'formName' => "form_{$arResult['UID']}",
	'allRowsCheckBoxId' => "actallrows_{$arResult['UID']}",
	'filterFields' => array()
);

$arResult['PREFIX'] = isset($arResult['PREFIX']) ? strval($arResult['PREFIX']) : 'activity_list';
$gridEditorID = $arResult['PREFIX'].'_crm_activity_grid_editor';
$editorItems = array();
$isEditable = !$arResult['READ_ONLY'];

$arResult['GRID_DATA'] = array();

$dateTimeOptions = array('TIME_FORMAT' => '<span class="crm-activity-time">#TIME#</span>');
foreach($arResult['ITEMS'] as &$item)
{
	$provider = CCrmActivity::GetActivityProvider($item);
	// Preparing of grid row -->
	$openViewJS = "BX.CrmActivityEditor.items['{$gridEditorID}'].openActivityDialog(BX.CrmDialogMode.view, {$item['ID']}, {});";
	$arActions = array(
		array(
			'TITLE' => GetMessage('CRM_ACTION_SHOW'),
			'TEXT' => GetMessage('CRM_ACTION_SHOW'),
			'ONCLICK' => $openViewJS,
			'DEFAULT' => true
		)
	);

	$itemTypeID = intval($item['TYPE_ID']);

	if($isEditable)
	{
		if($item['CAN_EDIT'] && ($itemTypeID === CCrmActivityType::Call || $itemTypeID === CCrmActivityType::Meeting ||
				(
					$itemTypeID === CCrmActivityType::Provider
					&& $provider
					&& $provider::isTypeEditable($item['PROVIDER_TYPE_ID'], $item['DIRECTION'])
				)
			)
		)
		{
			$arActions[] = array(
				'TITLE' => GetMessage('CRM_ACTION_EDIT'),
				'TEXT' => GetMessage('CRM_ACTION_EDIT'),
				'ONCLICK' => "(new BX.Crm.Activity.Planner()).showEdit({ID:{$item['ID']}});",
			);
		}

		if($item['CAN_COMPLETE'] && $itemTypeID !== CCrmActivityType::Email) //Email is always COMPLETED
		{
			if(isset($item['COMPLETED'])
				&& $item['COMPLETED'] === 'Y')
			{
				$arActions[] = array(
					'TITLE' => GetMessage('CRM_ACTION_MARK_AS_NOT_COMPLETED'),
					'TEXT' => GetMessage('CRM_ACTION_MARK_AS_NOT_COMPLETED'),
					'ONCLICK' => "BX.CrmActivityEditor.items['{$gridEditorID}'].setActivityCompleted({$item['ID']}, false);",
				);
			}
			else
			{
				$arActions[] = array(
					'TITLE' => GetMessage('CRM_ACTION_MARK_AS_COMPLETED'),
					'TEXT' => GetMessage('CRM_ACTION_MARK_AS_COMPLETED'),
					'ONCLICK' => "BX.CrmActivityEditor.items['{$gridEditorID}'].setActivityCompleted({$item['ID']}, true);",
				);
			}
		}

		if($item['CAN_DELETE'])
		{
			$arActions[] = array(
				'TITLE' => GetMessage('CRM_ACTION_DELETE'),
				'TEXT' => GetMessage('CRM_ACTION_DELETE'),
				'ONCLICK' => "BX.CrmActivityEditor.items['{$gridEditorID}'].deleteActivity({$item['ID']}, false);",
			);
		}

		$eventParam = array(
			'ID' => $item['ID'],
		);
		foreach(GetModuleEvents('crm', 'onCrmActivityListItemBuildMenu', true) as $event)
		{
			ExecuteModuleEventEx($event, array('CRM_ACTIVITY_LIST_MENU', $eventParam, &$arActions));
		}
	}

	$typeID = isset($item['~TYPE_ID']) ? intval($item['~TYPE_ID']) : CCrmActivityType::Undefined;
	$direction = isset($item['~DIRECTION']) ? intval($item['~DIRECTION']) : CCrmActivityDirection::Undefined;
	$typeClassName = '';
	$typeTitle = '';
	if($typeID === CCrmActivityType::Meeting):
		$typeClassName = 'crm-activity-meeting';
		$typeTitle = GetMessage('CRM_ACTION_TYPE_MEETING');
	elseif($typeID === CCrmActivityType::Call):
		if($direction === CCrmActivityDirection::Outgoing):
			$typeClassName = 'crm-activity-call-outgoing';
			$typeTitle = GetMessage('CRM_ACTION_TYPE_CALL_OUTGOING');
		else:
			$typeClassName = 'crm-activity-call-incoming';
			$typeTitle = GetMessage('CRM_ACTION_TYPE_CALL_INCOMING');
		endif;
	elseif($typeID === CCrmActivityType::Email):
		if($direction === CCrmActivityDirection::Outgoing):
			$typeClassName = 'crm-activity-email-outgoing';
			$typeTitle = GetMessage('CRM_ACTION_TYPE_EMAIL_OUTGOING');
		else:
			$typeClassName = 'crm-activity-email-incoming';
			$typeTitle = GetMessage('CRM_ACTION_TYPE_EMAIL_INCOMING');
		endif;
	elseif($typeID === CCrmActivityType::Task):
		$typeClassName = 'crm-activity-task';
		$typeTitle = GetMessage('CRM_ACTION_TYPE_TASK');
	elseif($typeID === CCrmActivityType::Provider && $provider !== null):
		$typeTitle = $provider::getTypeName($item['PROVIDER_TYPE_ID'], $item['DIRECTION']);
		if (
			isset($item['ORIGINATOR_ID']) &&
			in_array($item['ORIGINATOR_ID'], array('BITRIX', 'WORDPRESS', 'DRUPAL', 'JOOMLA', 'MAGENTO'))
		)
		{
			$typeClassName = 'crm-activity-crm_external_channel_cms';
		}
		elseif (isset($item['PROVIDER_ID']))
		{
			$typeClassName = 'crm-activity-'.strtolower($item['PROVIDER_ID']);
			if (
				isset($item['DIRECTION']) &&
				(
					$item['DIRECTION'] == CCrmActivityDirection::Incoming ||
					$item['DIRECTION'] == CCrmActivityDirection::Outgoing
				)
			)
			{
				$typeClassName .= ' ' . $typeClassName
					.'-'.($item['DIRECTION'] == CCrmActivityDirection::Incoming ? 'incoming' : 'outgoing');
			}
		}
	endif;

	$subject = isset($item['~SUBJECT']) ? $item['~SUBJECT'] : '';
	if($subject !== '')
	{
		$typeTitle = "{$typeTitle}. {$subject}";
	}

	$typeTitle = htmlspecialcharsbx($typeTitle);

	$subjectHtml = '<div title="'.$typeTitle.'" class="crm-activity-info '.$typeClassName.'"><a alt="'.$typeTitle.'" class="crm-activity-subject" href="#" onclick="'.htmlspecialcharsbx($openViewJS).' return false;">'.(isset($item['SUBJECT']) ? $item['SUBJECT'] : '').'</a>';

	$priority = isset($item['~PRIORITY']) ? intval($item['~PRIORITY']) : CCrmActivityPriority::None;
	if($priority === CCrmActivityPriority::High)
	{
		$subjectHtml .= '<div class="crm-activity-important" title="'.htmlspecialcharsbx(GetMessage('CRM_ACTION_IMPORTANT')).'"></div>';
	}
	$subjectHtml .= '</div>';

	$completed = isset($item['~COMPLETED']) ? strtoupper($item['~COMPLETED']) : 'N';
	if($completed === 'Y'):
		$completedClassName = 'crm-activity-completed';
		$completedTitle = GetMessage('CRM_ACTION_COMPLETED');
		$completedOnClick = 'return false;';
	else:
		$completedClassName = 'crm-activity-not-completed';
		$completedTitle = GetMessage($item['CAN_COMPLETE'] ? 'CRM_ACTION_CLICK_TO_COMPLETE' : 'CRM_ACTION_NOT_COMPLETED');
		$completedOnClick = $item['CAN_COMPLETE'] ? 'BX.CrmActivityEditor.items[\''.$gridEditorID.'\'].setActivityCompleted('.$item['ID'].', true); return false;' : 'return false;';
	endif;

	$completedHtml = '<a class="'.$completedClassName.'" title="'.$completedTitle.'" alt="'.$completedTitle.'" href="#" onclick="'.$completedOnClick.'"></a>';
	$descriptionHtml = isset($item['DESCRIPTION_HTML']) ? $item['DESCRIPTION_HTML'] : '';

	$enableDescriptionCut = isset($item['ENABLE_DESCRIPTION_CUT']) ? $item['ENABLE_DESCRIPTION_CUT'] : false;
	if($enableDescriptionCut && strlen($descriptionHtml) > 64)
	{
		$descriptionHtml = substr($descriptionHtml, 0, 64).'<a href="#" onclick="BX.CrmInterfaceGridManager.expandEllipsis(this); return false;">...</a><span class="bx-crm-text-cut-on">'.substr($descriptionHtml, 64).'</span>';
	}

	$arRowData =
		array(
			'id' => $item['~ID'],
			'actions' => $arActions,
			'data' => $item,
			'editable' => $isEditable,
			'columnClasses' => array('COMPLETED' => 'bx-minimal'),
			'columns' => array(
				'SUBJECT'=> $subjectHtml,
				'RESPONSIBLE_FULL_NAME' => $item['~RESPONSIBLE_FULL_NAME'] !== '' ?
					'<a href="'.htmlspecialcharsbx($item['PATH_TO_RESPONSIBLE']).'" id="balloon_'.$arResult['GRID_ID'].'_'.$item['ID'].'">'.htmlspecialcharsbx($item['~RESPONSIBLE_FULL_NAME']).'</a>'.
						'<script type="text/javascript">BX.tooltip('.$item['RESPONSIBLE_ID'].', "balloon_'.$arResult['GRID_ID'].'_'.$item['ID'].'", "");</script>'
					: '',
				'CREATED' => '<span class="crm-activity-date-time">'.FormatDate('SHORT', MakeTimeStamp($item['~CREATED'])).'</span>',
				'START_TIME' => isset($item['~START_TIME']) && $item['~START_TIME'] !== '' ? '<span class="crm-activity-date-time">'.CCrmComponentHelper::TrimDateTimeString(FormatDate('FULL', MakeTimeStamp($item['~START_TIME'])), $dateTimeOptions).'</span>' : '',
				'END_TIME' => isset($item['~END_TIME']) && $item['~END_TIME'] !== '' ? '<span class="crm-activity-date-time">'.CCrmComponentHelper::TrimDateTimeString(FormatDate('FULL', MakeTimeStamp($item['~END_TIME'])), $dateTimeOptions).'</span>' : '',
				'DEADLINE' => isset($item['~DEADLINE']) && $item['~DEADLINE'] !== '' ? '<span class="crm-activity-date-time">'.CCrmComponentHelper::TrimDateTimeString(FormatDate('FULL', MakeTimeStamp($item['~DEADLINE'])), $dateTimeOptions).'</span>' : '',
				'COMPLETED' => $completedHtml,
				'DESCRIPTION' => $descriptionHtml
				)
		);

	$ownerTypeID = isset($item['OWNER_TYPE_ID']) ? intval($item['OWNER_TYPE_ID']) : 0;
	$ownerID = isset($item['OWNER_ID']) ? intval($item['OWNER_ID']) : 0;
	$ownerInfo = null;
	if($ownerID > 0
		&& ($ownerTypeID === CCrmOwnerType::Deal || $ownerTypeID === CCrmOwnerType::Lead)
		&& isset($arResult['OWNER_INFOS'][$ownerTypeID])
		&& isset($arResult['OWNER_INFOS'][$ownerTypeID][$ownerID])
	)
	{
		$ownerInfo = $arResult['OWNER_INFOS'][$ownerTypeID][$ownerID];
		$showPath = isset($ownerInfo['SHOW_URL']) ? $ownerInfo['SHOW_URL'] : '';
		$title = isset($ownerInfo['TITLE']) ? $ownerInfo['TITLE'] : '';
		if($showPath !== '' && $title !== '')
		{
			$arRowData['columns']['REFERENCE'] = '<a target="_blank" href="'.htmlspecialcharsbx($showPath).'">'.htmlspecialcharsbx($title).'</a>';
		}
	}

	$commLoaded = isset($item['COMMUNICATIONS_LOADED']) ? $item['COMMUNICATIONS_LOADED'] : true;
	$communications = $commLoaded && isset($item['COMMUNICATIONS']) ? $item['COMMUNICATIONS'] : array();

	if($arResult['DISPLAY_CLIENT'])
	{
		$columnHtml = '';
		$clientInfo = isset($item['CLIENT_INFO']) ? $item['CLIENT_INFO'] : null;
		if(is_array($clientInfo))
		{
			$columnHtml= CCrmViewHelper::PrepareEntityBaloonHtml(
				array(
					'ENTITY_TYPE_ID' => $clientInfo['ENTITY_TYPE_ID'],
					'ENTITY_ID' => $clientInfo['ENTITY_ID'],
					'PREFIX' => "{$arResult['UID']}_{$item['~ID']}_CLIENT",
					'TITLE' => isset($clientInfo['TITLE']) ? $clientInfo['TITLE'] : '',
					'SHOW_URL' => isset($clientInfo['SHOW_URL']) ? $clientInfo['SHOW_URL'] : ''
				)
			);
		}
		$arRowData['columns']['CLIENT'] = $columnHtml;
	}

	$arResult['GRID_DATA'][] = $arRowData;
	// <-- Preparing grig row

	// Preparing activity editor item -->
	$commData = array();
	if(!empty($communications))
	{
		foreach($communications as &$arComm)
		{
			CCrmActivity::PrepareCommunicationInfo($arComm);
			$commData[] = array(
				'id' => $arComm['ID'],
				'type' => $arComm['TYPE'],
				'value' => $arComm['VALUE'],
				'entityId' => $arComm['ENTITY_ID'],
				'entityType' => CCrmOwnerType::ResolveName($arComm['ENTITY_TYPE_ID']),
				'entityTitle' => $arComm['TITLE'],
				'entityUrl' => CCrmOwnerType::GetShowUrl($arComm['ENTITY_TYPE_ID'], $arComm['ENTITY_ID'])
			);
		}
		unset($arComm);
	}

	$responsibleID = isset($item['~RESPONSIBLE_ID']) ? intval($item['~RESPONSIBLE_ID']) : 0;
	$responsibleUrl = isset($item['PATH_TO_RESPONSIBLE']) ? $item['PATH_TO_RESPONSIBLE'] : '';
	if($responsibleUrl === '')
	{
		$responsibleUrl = CComponentEngine::MakePathFromTemplate(
			$arResult['PATH_TO_USER_PROFILE'],
			array('user_id' => $responsibleID)
		);
	}

	$editorItem = array(
		'ID' => $item['~ID'],
		'typeID' => $item['~TYPE_ID'],
		'subject' => $item['~SUBJECT'],
		'description' => isset($item['DESCRIPTION_RAW']) ? $item['DESCRIPTION_RAW'] : '',
		'descriptionBBCode' => isset($item['DESCRIPTION_BBCODE']) ? $item['DESCRIPTION_BBCODE'] : '',
		'descriptionHtml' => isset($item['DESCRIPTION_HTML']) ? $item['DESCRIPTION_HTML'] : '',
		'direction' => intval($item['~DIRECTION']),
		'location' => $item['~LOCATION'],
		'start' => isset($item['~START_TIME']) ? ConvertTimeStamp(MakeTimeStamp($item['~START_TIME']), 'FULL', SITE_ID) : '',
		'end' => isset($item['~END_TIME']) ? ConvertTimeStamp(MakeTimeStamp($item['~END_TIME']), 'FULL', SITE_ID) : '',
		'deadline' => isset($item['~DEADLINE']) ? ConvertTimeStamp(MakeTimeStamp($item['~DEADLINE']), 'FULL', SITE_ID) : '',
		'completed' => $item['~COMPLETED'] == 'Y',
		'notifyType' => intval($item['~NOTIFY_TYPE']),
		'notifyValue' => intval($item['~NOTIFY_VALUE']),
		'priority' => intval($item['~PRIORITY']),
		'responsibleID' => $responsibleID,
		'responsibleName' => isset($item['~RESPONSIBLE_FULL_NAME'][0]) ? $item['~RESPONSIBLE_FULL_NAME'] : GetMessage('CRM_UNDEFINED_VALUE'),
		'responsibleUrl' =>  $responsibleUrl,
		'storageTypeID' => intval($item['STORAGE_TYPE_ID']),
		'files' => $item['FILES'],
		'webdavelements' => $item['WEBDAV_ELEMENTS'],
		'diskfiles' => $item['DISK_FILES'],
		'associatedEntityID' => isset($item['~ASSOCIATED_ENTITY_ID']) ? intval($item['~ASSOCIATED_ENTITY_ID']) : 0
	);

	if(!$commLoaded)
	{
		$editorItem['communicationsLoaded'] = false;
	}
	else
	{
		$editorItem['communicationsLoaded'] = true;
		$editorItem['communications'] = $commData;
	}

	if($ownerID > 0 && $ownerTypeID > 0)
	{
		$editorItem['ownerType'] = CCrmOwnerType::ResolveName($ownerTypeID);
		$editorItem['ownerID'] = $ownerID;
		if(is_array($ownerInfo))
		{
			$editorItem['ownerTitle'] = isset($ownerInfo['TITLE']) ? $ownerInfo['TITLE'] : '';
			$editorItem['ownerUrl'] = isset($ownerInfo['SHOW_URL']) ? $ownerInfo['SHOW_URL'] : '';
		}
	}

	$editorItems[] = $editorItem;
	// <-- Preparing activity editor item
}
unset($item);



$enableToolbar = $arResult['ENABLE_TOOLBAR'];
$toolbarID =  strtolower("{$gridEditorID}_toolbar");
$useQuickFilter = $arResult['USE_QUICK_FILTER'];

$prefix = $arResult['GRID_ID'];

//region Action Panel
$controlPanel = array('GROUPS' => array(array('ITEMS' => array())));

if(!$isInternal && $isEditable)
{
	$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
	$applyButton = $snippet->getApplyButton(
		array(
			'ONCHANGE' => array(
				array(
					'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
					'DATA' => array(array('JS' => "BX.CrmUIGridExtension.processApplyButtonClick('{$gridManagerID}')"))
				)
			)
		)
	);

	$actionList = array(array('NAME' => GetMessage('CRM_ACTIVITY_LIST_CHOOSE_ACTION'), 'VALUE' => 'none'));
	$actionList[] = $snippet->getRemoveAction();
	$actionList[] = $snippet->getEditAction();

	$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getRemoveButton();
	$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getEditButton();
	//region Action Button

	//endregion
	$actionList[] = array(
		'NAME' => GetMessage('CRM_ACTION_ASSIGN_TO'),
		'VALUE' => 'assign_to',
		'ONCHANGE' => array(
			array(
				'ACTION' => Bitrix\Main\Grid\Panel\Actions::CREATE,
				'DATA' => array(
					array(
						'TYPE' => Bitrix\Main\Grid\Panel\Types::TEXT,
						'ID' => 'action_responsible_search',
						'NAME' => 'ACTION_RESPONSIBLE_SEARCH'
					),
					array(
						'TYPE' => Bitrix\Main\Grid\Panel\Types::HIDDEN,
						'ID' => 'action_responsible_id',
						'NAME' => 'ACTION_RESPONSIBLE_ID'
					),
					$applyButton
				)
			),
			array(
				'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => array(
					array('JS' => "BX.CrmUIGridExtension.prepareAction('{$gridManagerID}', 'assign_to',  { searchInputId: 'action_responsible_search_control', dataInputId: 'action_responsible_id_control', componentName: '{$prefix}_ACTION_ASSIGNED_BY' })")
				)
			),
			array(
				'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => array(array('JS' => "BX.CrmUIGridExtension.processActionChange('{$gridManagerID}', 'assign_to')"))
			)
		)
	);
	//endregion

	//region Mark as completed
	$actionList[] = array(
		'NAME' => GetMessage('CRM_ACTION_MARK_AS_COMPLETED'),
		'VALUE' => 'mark_as_completed',
		'ONCHANGE' => array(
			array(
				'ACTION' => Bitrix\Main\Grid\Panel\Actions::CREATE,
				'DATA' => array($applyButton)
			)
		)
	);
	//endregion

	//region Mark as not completed
	$actionList[] = array(
		'NAME' => GetMessage('CRM_ACTION_MARK_AS_NOT_COMPLETED'),
		'VALUE' => 'mark_as_not_completed',
		'ONCHANGE' => array(
			array(
				'ACTION' => Bitrix\Main\Grid\Panel\Actions::CREATE,
				'DATA' => array($applyButton)
			)
		)
	);
	//endregion

	$controlPanel['GROUPS'][0]['ITEMS'][] = array(
		"TYPE" => \Bitrix\Main\Grid\Panel\Types::DROPDOWN,
		"ID" => "action_button_{$prefix}",
		"NAME" => "action_button_{$prefix}",
		"ITEMS" => $actionList
	);
	//endregion

	$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getForAllCheckbox();
}
//endregion


$APPLICATION->IncludeComponent(
	'bitrix:crm.interface.grid',
	'titleflex',
	array(
		'GRID_ID' => $arResult['UID'],
		'HEADERS' => $arResult['HEADERS'],
		'SORT' => $arResult['SORT'],
		'SORT_VARS' => $arResult['SORT_VARS'],
		'ROWS' => $arResult['GRID_DATA'],
		'FORM_ID' => $arResult['FORM_ID'],
		'TAB_ID' => $arResult['TAB_ID'],
		'FORM_URI' => $arResult['FORM_URI'],
		'AJAX_ID' => $arResult['AJAX_ID'],
		'AJAX_OPTION_JUMP' => $arResult['AJAX_OPTION_JUMP'],
		'AJAX_OPTION_HISTORY' => $arResult['AJAX_OPTION_HISTORY'],
		'AJAX_LOADER' => isset($arParams['AJAX_LOADER']) ? $arParams['AJAX_LOADER'] : null,
		'FILTER' => $arResult['FILTER'],
		'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
		'RENDER_FILTER_INTO_VIEW' => $useQuickFilter ? 'crm-quick-filter' : '',
		'ACTION_PANEL' => $controlPanel,
		'PAGINATION' => isset($arResult['PAGINATION']) && is_array($arResult['PAGINATION'])
			? $arResult['PAGINATION'] : array(),
		'ENABLE_LIVE_SEARCH' => true,
		'ENABLE_ROW_COUNT_LOADER' => true,
		'PRESERVE_HISTORY' => $arResult['PRESERVE_HISTORY'],
		'NAVIGATION_BAR' => array(
			'ITEMS' => array(
				array(
					'icon' => 'table',
					'id' => 'list',
					'name' => GetMessage('CRM_ACTIVITY_LIST_FILTER_NAV_BUTTON_LIST'),
					'active' => true,
					'url' => $arParams['PATH_TO_ACTIVITY_LIST']
				),
				array(
					'icon' => 'chart',
					'id' => 'widget',
					'name' => GetMessage('CRM_ACTIVITY_LIST_FILTER_NAV_BUTTON_WIDGET'),
					'active' => false,
					'url' => $arParams['PATH_TO_ACTIVITY_WIDGET']
				)
			),
			'BINDING' => array(
				'category' => 'crm.navigation',
				'name' => 'index',
				'key' => strtolower($arResult['NAVIGATION_CONTEXT_ID'])
			)
		),
		'IS_EXTERNAL_FILTER' => $arResult['IS_EXTERNAL_FILTER'],
		'EXTENSION' => array(
			'ID' => $gridManagerID,
			'CONFIG' => array(
				'ownerTypeName' => CCrmOwnerType::ActivityName,
				'gridId' => $arResult['UID'],
				'serviceUrl' => '/bitrix/components/bitrix/crm.activity.list/list.ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
				'loaderData' => isset($arParams['AJAX_LOADER']) ? $arParams['AJAX_LOADER'] : null
			)
		)
	),
	$component
);

?><script type="text/javascript">
	BX.ready(
		function()
		{
			BX.CrmLongRunningProcessDialog.messages =
				{
					startButton: "<?=GetMessageJS('CRM_LRP_DLG_BTN_START')?>",
					stopButton: "<?=GetMessageJS('CRM_LRP_DLG_BTN_STOP')?>",
					closeButton: "<?=GetMessageJS('CRM_LRP_DLG_BTN_CLOSE')?>",
					requestError: "<?=GetMessageJS('CRM_LRP_DLG_REQUEST_ERR')?>"
				};
		}
	);
</script><?

if($arResult['NEED_FOR_REBUILD_SEARCH_CONTENT']):?>
	<script type="text/javascript">
		BX.ready(
			function()
			{
				if(BX.AutorunProcessPanel.isExists("rebuildActivitySearch"))
				{
					return;
				}

				BX.AutorunProcessManager.messages =
					{
						title: "<?=GetMessageJS('CRM_ACTIVITY_REBUILD_SEARCH_CONTENT_DLG_TITLE')?>",
						stateTemplate: "<?=GetMessageJS('CRM_REBUILD_SEARCH_CONTENT_STATE')?>"
					};
				var manager = BX.AutorunProcessManager.create("rebuildActivitySearch",
					{
						serviceUrl: "<?='/bitrix/components/bitrix/crm.activity.list/list.ajax.php?'.bitrix_sessid_get()?>",
						actionName: "REBUILD_SEARCH_CONTENT",
						container: "rebuildActivitySearchWrapper",
						enableLayout: true
					}
				);
				manager.runAfter(100);
			}
		);
	</script>
<?endif;

if ($arResult['SHOW_MISMATCH_NOTIFY']):?>
	<div class="crm-warning-message">
		<?=GetMessage("CRM_WIDGET_COUNTER_MISMATCH_NOTIFY")?>
	</div>
<?endif;

if(!$useQuickFilter):
?><script type="text/javascript">
	BX.ready(
			function()
			{
				var editor = BX.CrmActivityEditor.items['<?= CUtil::JSEscape($gridEditorID)?>'];
				editor.addActivityChangeHandler(
					function()
					{
						if(editor)
						{
							editor.setLocked(true);
							editor.setLockMessage("<?=GetMessageJS("CRM_ACTIVITY_LIST_WAIT_FOR_RELOAD")?>");
							editor.release();
						}

						BX.Main.gridManager.reload('<?= CUtil::JSEscape($arResult['GRID_ID'])?>');
					}
				);

				BX.addCustomEvent(
					window,
					"CrmGridFilterApply",
					function()
					{
						if(editor)
						{
							editor.setLocked(true);
							editor.setLockMessage("<?=GetMessageJS("CRM_ACTIVITY_LIST_WAIT_FOR_RELOAD")?>");
							editor.release();
						}
					}
				);

				//HACK: fix task popup overlay position & size
				BX.CrmActivityEditor.attachInterfaceGridReload();

				BX.Crm.Activity.Planner.Manager.setCallback(
					"onAfterActivitySave",
					function()
					{
						if(editor)
						{
							editor.setLocked(true);
							editor.setLockMessage("<?=GetMessageJS("CRM_ACTIVITY_LIST_WAIT_FOR_RELOAD")?>");
							editor.release();
						}

						BX.Main.gridManager.reload('<?= CUtil::JSEscape($arResult['GRID_ID'])?>');
					}
				);

				BX.CrmActivityVisit.setCallback("onVisitCreated", function()
				{
					var eventArgs = { cancel: false };
					BX.onCustomEvent("BeforeCrmActivityListReload", [eventArgs]);
					if(!eventArgs.cancel)
					{
						if(editor)
						{
							editor.setLocked(true);
							editor.setLockMessage("<?=GetMessageJS("CRM_ACTIVITY_LIST_WAIT_FOR_RELOAD")?>");
							editor.release();
						}

						BX.Main.gridManager.reload('<?= CUtil::JSEscape($arResult['GRID_ID'])?>');
					}
				});
			}
	);
</script><?
else:
?><script type="text/javascript">
	BX.ready(
			function()
			{
				var editor = BX.CrmActivityEditor.items['<?= CUtil::JSEscape($gridEditorID)?>'];
				editor.addActivityChangeHandler(
					function()
					{
						var eventArgs = { cancel: false };
						BX.onCustomEvent("BeforeCrmActivityListReload", [eventArgs]);

						if(!eventArgs.cancel)
						{
							editor.removeActivityChangeHandler(this);
							editor.lockAndRelease("<?=GetMessageJS('CRM_ACTIVITY_LIST_WAIT_FOR_RELOAD')?>");
							BX.Main.gridManager.reload('<?= CUtil::JSEscape($arResult['GRID_ID'])?>');
						}
					}
				);

				BX.Crm.Activity.Planner.Manager.setCallback('onAfterActivitySave',function()
					{
						var eventArgs = { cancel: false };
						BX.onCustomEvent("BeforeCrmActivityListReload", [eventArgs]);

						if(!eventArgs.cancel)
						{
							BX.Main.gridManager.reload('<?= CUtil::JSEscape($arResult['GRID_ID'])?>');
						}
					}
				);

				BX.addCustomEvent(
					window,
					"BXInterfaceGridApplyFilter",
					function() { editor.lockAndRelease("<?=GetMessageJS('CRM_ACTIVITY_LIST_WAIT_FOR_RELOAD')?>"); }
				);

				BX.addCustomEvent(
					window,
					'BXInterfaceGridBeforeReload',
					function() { editor.lockAndRelease("<?=GetMessageJS('CRM_ACTIVITY_LIST_WAIT_FOR_RELOAD')?>"); }
				);

				//HACK: fix task popup overlay position & size
				BX.CrmActivityEditor.attachInterfaceGridReload();

				BX.CrmActivityVisit.setCallback("onVisitCreated", function()
				{
					var eventArgs = { cancel: false };
					BX.onCustomEvent("BeforeCrmActivityListReload", [eventArgs]);

					if(!eventArgs.cancel)
					{
						BX.Main.gridManager.reload('<?= CUtil::JSEscape($arResult['GRID_ID'])?>');
					}
				});
			}
	);
</script><?
endif;
$openViewItemId = isset($arResult['OPEN_VIEW_ITEM_ID']) ? $arResult['OPEN_VIEW_ITEM_ID'] : 0;
$openEditItemId = isset($arResult['OPEN_EDIT_ITEM_ID']) ? $arResult['OPEN_EDIT_ITEM_ID'] : 0;
if($openViewItemId > 0):
?><script type="text/javascript">
	BX.ready(
		function()
		{
			var editor = BX.CrmActivityEditor.items['<?=CUtil::JSEscape($gridEditorID)?>'];
			if(editor)
			{
				editor.viewActivity(<?=$openViewItemId?>);
			}
		}
	);
</script><?
elseif($openEditItemId > 0):
	?><script type="text/javascript">
		BX.ready(
			function()
			{
				var editor = BX.CrmActivityEditor.items['<?=CUtil::JSEscape($gridEditorID)?>'];
				if(editor)
				{
					editor.editActivity(<?=$openEditItemId?>);
				}
			}
		);
	</script><?
endif;