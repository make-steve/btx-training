<?
AddEventHandler('forum', 'OnCommentAdd', array('mod_meeting_comment', 'OnCommentAdd'));
//AddEventHandler('forum', 'OnAfterCommentTopicAdd', array('mod_topic_comment', 'OnAfterCommentTopicAdd'));

class mod_meeting_comment {

	function OnCommentAdd($type, $id, $arFields) {	
		
		// bases /bitrix/modules/crm/classes/general/livefeed.php
		global $USER;

		$meetingId = $topicId = intval($id);
		$isTopicComment = false;
		if($type == 'MI') {

			$rsMeetingItem = CMeetingInstance::GetList(array(), array('ITEM_ID' => $id));

			if($arMeetingItem = $rsMeetingItem->fetch()) {
				
				$meetingId = intval($arMeetingItem['MEETING_ID']);
			}
			$isTopicComment = true;
		}

		$currentUser = $USER->GetID();
		$rsMeeting = CAllMeeting::GetByID($meetingId);

		if($arMeeting = $rsMeeting->fetch()) {

			$meetingUsers = CAllMeeting::GetUsers($meetingId);

			foreach($meetingUsers as $meetingUser => $meetingRole) {

				if( $currentUser != $meetingUser) $arUsers[] = $meetingUser;

			}

			// for mentioned
			/*if (IsModuleInstalled("im")) {

				//  send mention notifications
				preg_match_all("/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/is".BX_UTF_PCRE_MODIFIER, $message, $arMention);
				if (
					!empty($arMention)
					&& !empty($arMention[1])
				)
				{
					$arMention = $arMention[1];
					$arMention = array_unique($arMention);
				}
			}*/

			if (
				(
					!empty($arUsers)
					|| !empty($arMention)
				)
				&& CModule::IncludeModule("im")
			)
			{
				$serverName = (CMain::IsHTTPS() ? "https" : "http")."://".((defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0) ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));

				$strIMMessageTitle = str_replace(Array("\r\n", "\n"), " ", $arMeeting['TITLE']);

				if (CModule::IncludeModule("blog"))
				{
					$strIMMessageTitle = trim(blogTextParser::killAllTags($strIMMessageTitle));
				}
				$strIMMessageTitle = TruncateText($strIMMessageTitle, 100);
				$strIMMessageTitleOut = TruncateText($strIMMessageTitle, 255);

				$strLogEntryCrmURL = CComponentEngine::MakePathFromTemplate(
					SITE_DIR."services/meeting/meeting/#meeting_id#/",
					array(
						"meeting_id" => $meetingId
					)
				);

				$strIMTopicMessageTitle = "";
				$strIMTopicMessageTitleOut = "";
				$strLogTopicEntryCrmURL = "";

				if(isTopicComment) {

					$rsMeetingItem = CMeetingItem::GetList(array(), array('ID' => $id));

					if($arMeetingItem = $rsMeetingItem->fetch()) {

						$strIMTopicMessageTitle = str_replace(Array("\r\n", "\n"), " ", $arMeetingItem['TITLE']);

						if (CModule::IncludeModule("blog"))
						{
							$strIMTopicMessageTitle = trim(blogTextParser::killAllTags($strIMTopicMessageTitle));
						}
						$strIMTopicMessageTitle = TruncateText($strIMTopicMessageTitle, 100);
						$strIMTopicMessageTitleOut = TruncateText($strIMTopicMessageTitle, 255);

						$strLogTopicEntryCrmURL = CComponentEngine::MakePathFromTemplate(
							SITE_DIR."services/meeting/item/#topic_id#/?from=#meeting_id#",
							array(
								"topic_id" => $topicId,
								"meeting_id" => $meetingId
							)
						);
					}
				}

				$genderSuffix = "";
				$dbUser = CUser::GetByID($currentUser);

				if($arUser = $dbUser->Fetch())
				{
					switch ($arUser["PERSONAL_GENDER"])
					{
						case "M":
							$genderSuffix = "_M";
							break;
						case "F":
							$genderSuffix = "_F";
							break;
						default:
							$genderSuffix = "";
					}
				}

				if (!empty($arUsers))
				{
					foreach($arUsers as $val)
					{
						$arMessageFields = array(
							"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
							"TO_USER_ID" => "",
							"FROM_USER_ID" => $currentUser,
							"NOTIFY_TYPE" => IM_NOTIFY_FROM,
							"NOTIFY_MODULE" => "forum",
							"NOTIFY_EVENT" => "post"
						);

						if(isTopicComment)
							$message = "Added comment to topic " . "<a href=\"".$strLogTopicEntryCrmURL."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($strIMTopicMessageTitle)."</a> from meeting " . "<a href=\"".$strLogEntryCrmURL."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($strIMMessageTitle)."</a>";
						else
							$message = "Added comment to meeting " . "<a href=\"".$strLogEntryCrmURL."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($strIMMessageTitle)."</a>";

						$arMessageFields["TO_USER_ID"] = $val;
						$arMessageFields["NOTIFY_TAG"] = "CRM|POST|".$messageID;
						$arMessageFields["NOTIFY_MESSAGE"] = $message;

						/*$arMessageFields["NOTIFY_MESSAGE_OUT"] = GetMessage(
							"CRM_LF_EVENT_IM_POST".$genderSuffix,
							array(
								"#title#" => htmlspecialcharsbx($strIMMessageTitleOut)
							)
						)." (".$serverName.$strLogEntryCrmURL.")";*/

						
						CIMNotify::Add($arMessageFields);
					}
				}
			}
		}
		//exit();
	}
}

class mod_topic_comment {

	function OnAfterCommentTopicAdd($arFields) {

		echo '<pre>';
			print_r($arFields);
		echo '</pre>';
		exit();
	}
}
?>