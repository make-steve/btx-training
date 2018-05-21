<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Резервирование переговорных");
?>
<p><?$APPLICATION->IncludeComponent(
	"bitrix:intranet.event_calendar",
	".default",
	Array(
		"IBLOCK_TYPE" => "events", 
		"IBLOCK_ID" => "44", 
		"INIT_DATE" => "-Показывать текущую дату-", 
		"WEEK_HOLIDAYS" => array(0=>"5",1=>"6",), 
		"YEAR_HOLIDAYS" => "1.01,7.01,23.02,8.03", 
		"LOAD_MODE" => "ajax", 
		"EVENT_LIST_MODE" => "N", 
		"USERS_IBLOCK_ID" => "#CALENDAR_USERS_IBLOCK_ID#", 
		"PATH_TO_USER" => "/company/personal/user/#user_id#/", 
		"PATH_TO_USER_CALENDAR" => "/company/personal/user/#user_id#/calendar/",
		"WORK_TIME_START" => "9", 
		"WORK_TIME_END" => "19", 
		"ALLOW_SUPERPOSE" => "N", 
		"RESERVE_MEETING_READONLY_MODE" => "Y",
		"REINVITE_PARAMS_LIST" => array(
			0 => "from",
			1 => "to",
			2 => "location",
		),
		"ALLOW_RES_MEETING" => "N",
		"ALLOW_VIDEO_MEETING" => "N",	
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600" 
	)
);?></p>

<p>Для резервирования переговорной: найдите время, когда она свободна, свяжитесь с менеджером для бронирования, который подтвердит вашу бронь и внесет ее в расписание занятости переговорных.</p>

<p><a href="/services/index.php">Резервирование переговорных с помощью таблицы</a><br /></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>