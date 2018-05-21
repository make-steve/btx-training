<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$aMenuLinks = Array(

	Array(
		"Переговорные", 
		"/services/index.php", 
		Array("/services/res_c.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('MeetingRoomBookingSystem')" 
	),
	Array(
		"Собрания и планерки", 
		"/services/meeting/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Meeting')" 
	),
	Array(
		"Есть Идея?", 
		"/services/idea/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Idea')" 
	),  
	Array(
		"Списки", 
		"/services/lists/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Lists')" 
	),
	Array(
		"Бизнес-процессы", 
		"/services/bp/", 
		Array(), 
		Array(), 
		""
	),
	Array(
		"Электронные заявки", 
		"/services/requests/", 
		Array(), 
		Array(), 
		(!IsModuleInstalled("form"))?"false":"CBXFeatures::IsFeatureEnabled('Requests')" 
	),
	Array(
		"Обучение", 
		"/services/learning/", 
		Array("/services/course.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Learning')" 
	),
	Array(
		"База знаний (wiki)", 
		"/services/wiki/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Wiki')" 
	),
	Array(
		"Вопросы и ответы", 
		"/services/faq/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Опросы", 
		"/services/votes.php", 
		Array("/services/vote_new.php", "/services/vote_result.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Vote')" 
	),
	Array(
		"Техническая поддержка", 
		"/services/support.php?show_wizard=Y", 
		Array("/services/support.php"), 
		Array(), 
		(!IsModuleInstalled("support"))?"false":"CBXFeatures::IsFeatureEnabled('Support')"
	),
	Array(
		"Каталог ссылок", 
		"/services/links.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('WebLink')" 
	),
	Array(
		"Подписка", 
		"/services/subscr_edit.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Subscribe')" 
	),
	Array(
		"Журнал изменений", 
		"/services/event_list.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('EventList')" 
	),
	Array(
		"Зарплата и отпуск", 
		"/services/salary/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Salary')" 
	),
	Array(
		"Доска объявлений", 
		"/services/board/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Board')" 
	),	
);
?>