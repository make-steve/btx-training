<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/tools/clock.php');

global $APPLICATION, $USER;

$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/crm-entity-show.css");

if(SITE_TEMPLATE_ID === 'bitrix24')
{
	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/bitrix24/crm-entity-show.css");
}

$jsCoreInit = array('date', 'popup', 'ajax', 'crm_activity_planner', 'crm_visit_tracker');
if($arResult['ENABLE_DISK'])
{
	$jsCoreInit[] = 'uploader';
	$jsCoreInit[] = 'file_dialog';
}
CJSCore::Init($jsCoreInit);

CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/activity.js');
CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/crm.js');
CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/communication_search.js');
CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/common.js');


$prefixUpper = strtoupper($arResult['PREFIX']);
$prefixLower = strtolower($arResult['PREFIX']);

$containerID = $arResult['CONTAINER_ID'];
$hasOwnContainer = false;
if($containerID === '')
{
	$containerID = $prefixLower.'_container';
	$hasOwnContainer = true;
}

$editorID = $arResult['EDITOR_ID'];
$type = $arResult['EDITOR_TYPE'];
if($type === '')
{
	$type = 'MIXED';
}

$toolbarID = $arResult['TOOLBAR_ID'];
$hasOwnToolbar = false;
$enableToolbar = !$arResult['READ_ONLY'] && $arResult['ENABLE_TOOLBAR'];
if($enableToolbar && $toolbarID === '')
{
	$toolbarID = $editorID.'_toolbar';
	$hasOwnToolbar = true;
}

$toolbarID = strtolower($toolbarID);

$userFullName = '';
$curUser = CCrmSecurityHelper::GetCurrentUser();
if($curUser)
{
	$userFullName = CUser::FormatName(
		CSite::GetNameFormat(false),
		array(
			'LOGIN' => $curUser->GetLogin(),
			'NAME' => $curUser->GetFirstName(),
			'SECOND_NAME' => $curUser->GetSecondName(),
			'LAST_NAME' => $curUser->GetLastName(),
		),
		true,
		false
	);
}

$arResult['ENABLE_TASK_TRACING'] = true;

$editorCfg = array(
	'type' => $type,
	'serviceUrl' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
	'enableUI' => $arResult['ENABLE_UI'],
	'enableToolbar' => $enableToolbar,
	'toolbarID' => $toolbarID,
	'readOnly' => $arResult['READ_ONLY'],
	'enableTaskTracing' => $arResult['ENABLE_TASK_TRACING'],
	'enableTasks' => $arResult['ENABLE_TASK_ADD'],
	'enableCalendarEvents' => $arResult['ENABLE_CALENDAR_EVENT_ADD'],
	'enableEmails' => $arResult['ENABLE_EMAIL_ADD'],
	'ownerType' => $arResult['OWNER_TYPE'],
	'ownerID' => $arParams['DEAL_OWNER_ID'],
	'ownerTitle' => CCrmOwnerType::GetCaption($arResult['OWNER_TYPE_ID'], $arResult['OWNER_ID']),
	'ownerUrl' => CCrmOwnerType::GetShowUrl($arResult['OWNER_TYPE_ID'], $arResult['OWNER_ID']),
	'prefix' => $arResult['PREFIX'],
	'containerID' => $containerID,
	'uploadID' => $prefixLower.'_upload_container',
	'uploadControlID' => $prefixLower.'_activity_uploader',
	'uploadInputID' => $prefixLower.'_activity_saved_file',
	'callClockID' => $prefixLower.'_call_clock_container',
	'callClockInputID' => $prefixLower.'_call_time',
	'meetingClockID' => $prefixLower.'_meeting_clock_container',
	'meetingClockInputID' => $prefixLower.'_meeting_time',
	'emailLheContainerID' => $prefixLower.'_email_lhe_container',
	'emailLheID' => $prefixLower.'_email_editor',
	'emailLheJsName' => $prefixLower.'_email_editor',
	'emailUploadContainerID' => $prefixLower.'_email_upload_container',
	'emailUploadControlID' => $prefixLower.'_activity_email_uploader',
	'emailUploadInputID' => $prefixLower.'_activity_email_saved_file',
	'userID' => $curUser->GetID(),
	'userFullName'=> $userFullName,
	'userEmail' => $curUser->getEmail(),
	'userEmail2' => $arResult['USER_CRM_EMAIL'],
	'crmEmail' => $arResult['SHARED_CRM_EMAIL'],
	'lastUsedEmail' => CUserOptions::GetOption('crm', 'activity_email_addresser', ''),
	//'lastUsedMailTemplateID' => CCrmMailTemplate::GetLastUsedTemplateID($arResult['OWNER_TYPE_ID'], $curUser->GetID()),
	'lastUsedMailTemplateID' => 0,
	'serverTime' => time() + CTimeZone::GetOffset(),
	'imagePath' => $this->GetFolder().'/images/',
	'defaultStorageTypeId' => $arResult['STORAGE_TYPE_ID'],
	'enableDisk' => $arResult['ENABLE_DISK'],
	'enableWebDav' => $arResult['ENABLE_WEBDAV'],
	'webDavSelectUrl' => $arResult['WEBDAV_SELECT_URL'],
	'webDavUploadUrl' => $arResult['WEBDAV_UPLOAD_URL'],
	'webDavShowUrl' => $arResult['WEBDAV_SHOW_URL'],
	'buttonID' => $arResult['BUTTON_ID'],
	'serviceContainerID' => $prefixLower.'_service_container',
	'userSearchJsName' => $prefixLower.'_USER_SEARCH',
	'ownershipSelectorData' => array(
		'items' => CCrmEntitySelectorHelper::PreparePopupItems(
			'DEAL',
			false,
			$arResult['NAME_TEMPLATE']
		),
		'messages' => CCrmEntitySelectorHelper::PrepareCommonMessages()
	),
	'callToFormat' => CCrmCallToUrl::GetFormat(CCrmCallToUrl::Bitrix),
	'mailTemplateData' => $mailTemplateData,
	'disableStorageEdit' => $arResult['DISABLE_STORAGE_EDIT'],
	'addEventUrl' => $arResult['CREATE_EVENT_URL'],
	'formId' => $arResult['FORM_ID'],
	'eventTabId' => $arResult['EVENT_VIEW_TAB_ID']
);

$toolbarButtons = array();
$toolbarButtons[] = array(
	'TEXT' => "Voeg taak toe",
	'TITLE' => "Voeg taak toe",
	'ICON' => 'btn-new crm-activity-command-add-task',
);
$APPLICATION->IncludeComponent(
	'bitrix:crm.interface.toolbar',
	'projecttaskbutton',
	array(
		'TOOLBAR_ID' => $toolbarID,
		'BUTTONS' => $toolbarButtons
	),
	$component,
	array('HIDE_ICONS' => 'Y')
);


$APPLICATION->IncludeComponent(
	'bitrix:tasks.iframe.popup',
	'',
	array(
		'ON_BEFORE_HIDE' => $arResult['ENABLE_TASK_TRACING'] ? 'BX.CrmActivityEditor.onBeforeHide' : 'BX.DoNothing',
		'ON_AFTER_HIDE' => $arResult['ENABLE_TASK_TRACING'] ? 'BX.CrmActivityEditor.onAfterHide' : 'BX.DoNothing',
		'ON_BEFORE_SHOW' => $arResult['ENABLE_TASK_TRACING'] ? 'BX.CrmActivityEditor.onBeforeShow' : 'BX.DoNothing',
		'ON_AFTER_SHOW' => $arResult['ENABLE_TASK_TRACING'] ? 'BX.CrmActivityEditor.onAfterShow' : 'BX.DoNothing',
		'ON_TASK_ADDED' => $arResult['ENABLE_TASK_TRACING'] ? 'BX.CrmActivityEditor.onPopupTaskAdded' : 'BX.DoNothing',
		'ON_TASK_CHANGED' => $arResult['ENABLE_TASK_TRACING'] ? 'BX.CrmActivityEditor.onPopupTaskChanged' : 'BX.DoNothing',
		'ON_TASK_DELETED' => $arResult['ENABLE_TASK_TRACING'] ? 'BX.CrmActivityEditor.onPopupTaskDeleted' : 'BX.DoNothing'
	),
	$component,
	array('HIDE_ICONS' => 'Y')
);

?>
<script type="text/javascript">
	BX.ready(
		function()
		{
			var editor = BX.CrmActivityEditor.create(
			'<?= CUtil::JSEscape($editorID) ?>',
			<?= CUtil::PhpToJSObject($editorCfg) ?>,
			<?= CUtil::PhpToJSObject(isset($arResult['EDITOR_ITEMS']) ? $arResult['EDITOR_ITEMS'] : array()) ?>
			);

			if(typeof(BX.CrmActivityEditor.messages) === 'undefined')
			{
				BX.CrmActivityEditor.messages =
				{
					"yes": "<?= GetMessage('MAIN_YES') ?>",
					"no": "<?= GetMessage('MAIN_NO') ?>",
					"deletionConfirm": "<?= GetMessageJS('CRM_ACTIVITY_LIST_DELETION_CONFIRM') ?>",
					"editButtonTitle": "<?= GetMessageJS('CRM_ACTIVITY_LIST_EDIT_BTN_TTL')?>",
					"deleteButtonTitle": "<?= GetMessageJS('CRM_ACTIVITY_LIST_DEL_BTN_TTL')?>",
					"saveDlgButton": "<?= GetMessageJS('CRM_ACTIVITY_DLG_BTN_SAVE')?>",
					"cancelShortDlgButton": "<?= GetMessageJS('CRM_ACTIVITY_DLG_BTN_CANCEL_SHORT')?>",
					"editDlgButton": "<?= GetMessageJS('CRM_ACTIVITY_DLG_BTN_EDIT')?>",
					"closeDlgButton": "<?= GetMessageJS('CRM_ACTIVITY_DLG_BTN_CLOSE')?>",
					"sendDlgButton": "<?= GetMessageJS('CRM_ACTIVITY_DLG_BTN_SEND')?>",
					"replyDlgButton": "<?= GetMessageJS('CRM_ACTIVITY_DLG_BTN_REPLY')?>",
					"forwardDlgButton": "<?= GetMessageJS('CRM_ACTIVITY_DLG_BTN_FORWARD')?>",
					"invalidEmailError": "<?= GetMessageJS('CRM_ACTIVITY_ERROR_INVALID_EMAIL')?>",
					"invalidPhoneError": "<?= GetMessageJS('CRM_ACTIVITY_ERROR_INVALID_PHONE')?>",
					"addresseeIsEmpty": "<?= GetMessageJS('CRM_ACTIVITY_EMAIL_EMPTY_FROM_FIELD')?>",
					"addresserIsEmpty": "<?= GetMessageJS('CRM_ACTIVITY_EMAIL_EMPTY_TO_FIELD')?>",

					"dataLoading" : "<?= GetMessageJS('CRM_ACTIVITY_DATA_LOADING')?>",
					"showAllCommunication" : "<?= GetMessageJS('CRM_ACTIVITY_SHOW_ALL_COMMUNICATIONS')?>",
					"prevPage" : "<?= GetMessageJS('CRM_ACTIVITY_PREV_PAGE')?>",
					"nextPage" : "<?= GetMessageJS('CRM_ACTIVITY_NEXT_PAGE')?>"
				};

				<?if($arResult['ENABLE_DISK']):?>
					BX.CrmActivityEditor.messages["diskAttachFiles"] = "<?= GetMessageJS('CRM_ACTIVITY_DISK_ATTACH_FILE')?>";
					BX.CrmActivityEditor.messages["diskAttachedFiles"] = "<?= GetMessageJS('CRM_ACTIVITY_DISK_ATTACHED_FILES')?>";
					BX.CrmActivityEditor.messages["diskSelectFile"] = "<?= GetMessageJS('CRM_ACTIVITY_DISK_SELECT_FILE')?>";
					BX.CrmActivityEditor.messages["diskSelectFileLegend"] = "<?= GetMessageJS('CRM_ACTIVITY_DISK_SELECT_FILE_LEGEND')?>";
					BX.CrmActivityEditor.messages["diskUploadFile"] = "<?= GetMessageJS('CRM_ACTIVITY_DISK_UPLOAD_FILE')?>";
					BX.CrmActivityEditor.messages["diskUploadFileLegend"] = "<?= GetMessageJS('CRM_ACTIVITY_DISK_UPLOAD_FILE_LEGEND')?>";
				<?endif;?>

				<?if($arResult['ENABLE_WEBDAV']):?>
					BX.CrmActivityEditor.messages["webdavFileLoading"] = "<?= GetMessageJS('CRM_ACTIVITY_WEBDAV_FILE_LOADING')?>";
					BX.CrmActivityEditor.messages["webdavFileAlreadyExists"] = "<?= GetMessageJS('CRM_ACTIVITY_WEBDAV_FILE_ALREADY_EXISTS')?>";
					BX.CrmActivityEditor.messages["webdavFileAccessDenied"] = "<?= GetMessageJS('CRM_ACTIVITY_WEBDAV_FILE_ACCESS_DENIED')?>";
					BX.CrmActivityEditor.messages["webdavAttachFile"] = "<?= GetMessageJS('CRM_ACTIVITY_WEBDAV_ATTACH_FILE')?>";
					BX.CrmActivityEditor.messages["webdavTitle"] = "<?= GetMessageJS('CRM_ACTIVITY_WEBDAV_TITLE')?>";
					BX.CrmActivityEditor.messages["webdavDragFile"] = "<?= GetMessageJS('CRM_ACTIVITY_WEBDAV_DRAG_FILE')?>";
					BX.CrmActivityEditor.messages["webdavSelectFile"] = "<?= GetMessageJS('CRM_ACTIVITY_WEBDAV_SELECT_FILE')?>";
					BX.CrmActivityEditor.messages["webdavSelectFromLib"] = "<?= GetMessageJS('CRM_ACTIVITY_WEBDAV_SELECT_FROM_LIB')?>";
					BX.CrmActivityEditor.messages["webdavLoadFiles"] = "<?= GetMessageJS('CRM_ACTIVITY_WEBDAV_LOAD_FILES')?>";
				<?endif;?>
			}

			if(typeof(BX.CrmActivityEditor.flashPlayerUrl) === 'undefined')
			{
				BX.CrmActivityEditor.flashPlayerUrl = "<?=CUtil::JSEscape($arResult['FLASH_PLAYER_URL'])?>";
			}

			if(typeof(BX.CrmActivityEditor.flashPlayerApiUrl) === 'undefined')
			{
				BX.CrmActivityEditor.flashPlayerApiUrl = "<?=CUtil::JSEscape($arResult['FLASH_PLAYER_API_URL'])?>";
			}

			if(typeof(BX.CrmCommunicationSearch.messages) === 'undefined')
			{
				BX.CrmCommunicationSearch.messages =
				{
					"SearchTab": "<?= GetMessageJS('CRM_ACTIVITY_LIST_COMMUNICATION_SEARCH_TAB')?>",
					"NoData": "<?= GetMessageJS('CRM_ACTIVITY_LIST_COMMUNICATION_SEARCH_NO_DATA')?>"
				}
			}

			if(typeof(BX.CrmActivityCalEvent.messages) === 'undefined')
			{
				BX.CrmActivityCalEvent.messages =
				{
					"addMeetingDlgTitle": "<?= GetMessageJS('CRM_ACTIVITY_DLG_TTL_ADD_MEETING')?>",
					"addCallDlgTitle": "<?= GetMessageJS('CRM_ACTIVITY_DLG_TTL_ADD_CALL')?>",
					"editDlgTitle": "<?= GetMessageJS('CRM_ACTIVITY_DLG_TTL_EDIT')?>",
					"viewDlgTitle": "<?= GetMessageJS('CRM_ACTIVITY_DLG_TTL_VIEW')?>",
					"activity": "<?= GetMessageJS('CRM_ACTIVITY_TYPE_ACTIVITY')?>",
					"meeting": "<?= GetMessageJS('CRM_ACTIVITY_TYPE_MEETING')?>",
					"call": "<?= GetMessageJS('CRM_ACTIVITY_TYPE_CALL')?>",
					"subject": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_SUBJECT')?>",
					"meetingDescrHint": "<?= GetMessageJS('CRM_ACTIVITY_DLG_MEETING_DESCR_HINT')?>",
					"callDescrHint": "<?= GetMessageJS('CRM_ACTIVITY_DLG_CALL_DESCR_HINT')?>",
					"datetime": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_DATETIME')?>",
					"setDate": "<?= GetMessageJS('CRM_ACTIVITY_DLG_SET_DATE')?>",
					"enableNotification": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_SET_REMINDER')?>",
					"location": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_LOCATION')?>",
					"direction": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_DIRECTION')?>",
					"partner": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_PARTNER')?>",
					"meetingSubject": "<?= GetMessageJS('CRM_ACTIVITY_DLG_MEETING_DEFAULT_SUBJECT')?>",
					"callSubject": "<?= GetMessageJS('CRM_ACTIVITY_DLG_CALL_DEFAULT_SUBJECT')?>",
					"meetingSubjectHint": "<?= GetMessageJS('CRM_ACTIVITY_DLG_MEETING_SUBJECT_HINT')?>",
					"callSubjectHint": "<?= GetMessageJS('CRM_ACTIVITY_DLG_CALL_SUBJECT_HINT')?>",
					"status": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_STATUS')?>",
					"priority": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_PRIORITY')?>",
					"type": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_TYPE')?>",
					"description": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_DESCRIPTION')?>",
					"responsible": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_RESPONSIBLE')?>",
					"undefinedType": "<?= GetMessageJS('CRM_ACTIVITY_DLG_UNDEFINED_TYPE')?>",
					"change": "<?= GetMessageJS('CRM_ACTIVITY_DLG_CHANGE_OWNER')?>",
					"owner": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_OWNER')?>",
					"ownerNotDefined": "<?= GetMessageJS('CRM_ACTIVITY_DLG_CAL_EVENT_OWNER_NOT_DEFINED')?>",
					"files": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_FILES')?>",
					"records": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_RECORDS')?>",
					"download": "<?= GetMessageJS('CRM_ACTIVITY_DLG_DOWNLOAD')?>"
				};
			}

			if(typeof(BX.CrmActivityEmail.messages) === 'undefined')
			{
				BX.CrmActivityEmail.messages =
				{
					"addEmailDlgTitle": "<?= GetMessageJS('CRM_ACTIVITY_DLG_TTL_ADD_EMAIL')?>",
					"viewDlgTitle": "<?= GetMessageJS('CRM_ACTIVITY_DLG_TTL_VIEW')?>",
					"email": "<?= GetMessageJS('CRM_ACTIVITY_TYPE_EMAIL')?>",
					"to": "<?= GetMessageJS('CRM_ACTIVITY_EMAIL_TO')?>",
					"from": "<?= GetMessageJS('CRM_ACTIVITY_EMAIL_FROM')?>",
					"subject": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_SUBJECT')?>",
					"template": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_TEMPLATE')?>",
					"description": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_DESCRIPTION')?>",
					"direction": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_DIRECTION')?>",
					"addresser": "<?= GetMessageJS('CRM_ACTIVITY_DLG_EMAIL_FIELD_ADDRESSER')?>",
					"addressee": "<?= GetMessageJS('CRM_ACTIVITY_DLG_EMAIL_FIELD_ADDRESSEE')?>",
					"datetime": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_DATETIME')?>",
					"change": "<?= GetMessageJS('CRM_ACTIVITY_DLG_CHANGE_OWNER')?>",
					"owner": "<?= GetMessageJS('CRM_ACTIVITY_DLG_FIELD_OWNER')?>",
					"ownerNotDefined": "<?= GetMessageJS('CRM_ACTIVITY_DLG_EMAIL_OWNER_NOT_DEFINED')?>",
					"noTemplate": "<?= GetMessageJS('CRM_ACTIVITY_DLG_NO_EMAIL_TEMPLATE')?>"
				};
			}

			if(typeof(BX.CrmActivityMenu.messages) === 'undefined')
			{
				BX.CrmActivityMenu.messages =
				{
					"task": "<?= GetMessageJS('CRM_ACTIVITY_DLG_MENU_TASK')?>",
					"call": "<?= GetMessageJS('CRM_ACTIVITY_DLG_MENU_CALL')?>",
					"meeting": "<?= GetMessageJS('CRM_ACTIVITY_DLG_MENU_MEETING')?>"
				};
			}

			BX.CrmActivityNotifyType.descrTemplate = "<?= CUtil::JSEscape(GetMessage('CRM_ACTIVITY_NOTIFY_DESCR')) ?>";

			BX.CrmActivityType.setListItems(<?= CUtil::PhpToJSObject(CCrmActivityType::PrepareListItems()) ?>);
			BX.CrmActivityStatus.setListItems(
				{
					<?= CCrmActivityType::Activity ?>: <?= CUtil::PhpToJSObject(CCrmActivityStatus::PrepareListItems(CCrmActivityType::Activity)) ?>,
					<?= CCrmActivityType::Meeting ?>: <?= CUtil::PhpToJSObject(CCrmActivityStatus::PrepareListItems(CCrmActivityType::Meeting)) ?>,
					<?= CCrmActivityType::Call ?>: <?= CUtil::PhpToJSObject(CCrmActivityStatus::PrepareListItems(CCrmActivityType::Call)) ?>
				}
			);

			BX.CrmActivityNotifyType.setListItems(<?= CUtil::PhpToJSObject(CCrmActivityNotifyType::PrepareListItems()) ?>);
			BX.CrmActivityPriority.setListItems(<?= CUtil::PhpToJSObject(CCrmActivityPriority::PrepareListItems()) ?>);
			BX.CrmActivityDirection.setListItems(
				{
					<?= CCrmActivityType::Call ?>: <?= CUtil::PhpToJSObject(CCrmActivityDirection::PrepareListItems(CCrmActivityType::Call)) ?>,
					<?= CCrmActivityType::Email ?>: <?= CUtil::PhpToJSObject(CCrmActivityDirection::PrepareListItems(CCrmActivityType::Email)) ?>
				}
			);

			BX.CrmSipManager.getCurrent().setServiceUrl(
				"CRM_<?=CUtil::JSEscape(CCrmOwnerType::LeadName)?>",
				"/bitrix/components/bitrix/crm.lead.show/ajax.php?<?=bitrix_sessid_get()?>"
			);

			BX.CrmSipManager.getCurrent().setServiceUrl(
				"CRM_<?=CUtil::JSEscape(CCrmOwnerType::ContactName)?>",
				"/bitrix/components/bitrix/crm.contact.show/ajax.php?<?=bitrix_sessid_get()?>"
			);

			BX.CrmSipManager.getCurrent().setServiceUrl(
				"CRM_<?=CUtil::JSEscape(CCrmOwnerType::CompanyName)?>",
				"/bitrix/components/bitrix/crm.company.show/ajax.php?<?=bitrix_sessid_get()?>"
			);

			if(typeof(BX.CrmSipManager.messages) === 'undefined')
			{
				BX.CrmSipManager.messages =
				{
					"unknownRecipient": "<?= GetMessageJS('CRM_SIP_MGR_UNKNOWN_RECIPIENT')?>",
					"makeCall": "<?= GetMessageJS('CRM_SIP_MGR_MAKE_CALL')?>"
				};
			}
		}
	);

	var isRefreshable = false;

	BX.CrmActivityEditor.onBeforeShow = function() {
		alert("Create Before new task");
	};
	BX.CrmActivityEditor.onAfterShow = function() {
		alert("Create After new task");
	};

/*	onBeforeHide = function() {
		console.log("closebefore");
		console.log(isRefreshable);
		if(isRefreshable) location.reload();
	};
	onAfterHide = function() {
		console.log("closeafter");
		console.log(isRefreshable);
		if(isRefreshable) location.reload();
	};
	onPopupTaskAdded = function(task) {
		console.log("added");
		isRefreshable = true;
		console.log(isRefreshable);
	};
	onPopupTaskChanged = function(task) {
		console.log("change");
		isRefreshable = true;
		console.log(isRefreshable);
	};
	onPopupTaskDeleted = function(task) {
		console.log("deleted");
		isRefreshable = true;
		console.log(isRefreshable);
	};*/
</script>
