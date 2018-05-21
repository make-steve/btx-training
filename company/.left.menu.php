<?
$aMenuLinks = Array(
	Array(
		"Find Employee", 
		"/company/index.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Telephone Directory", 
		"/company/telephones.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Company Structure", 
		"/company/vis_structure.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Staff Changes", 
		"/company/events.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('StaffChanges')" 
	),
	Array(
		"Absence Chart", 
		"/company/absence.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('StaffAbsence')" 
	),
	Array(
		"Time Tracker", 
		"/company/timeman.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('timeman')" 
	),
	Array(
		"Reporting", 
		"/company/work_report.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('timeman')" 
	),
	Array(
		"Efficiency Report", 
		"/company/report.php", 
		Array(), 
		Array(), 
		"IsModuleInstalled('tasks')" 
	),
	Array(
		"Honored Employees", 
		"/company/leaders.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Birthdays", 
		"/company/birthdays.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Shared Photos", 
		"/company/gallery/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Gallery')" 
	),
	Array(
		"My Requests", 
		"/company/personal/processes/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Lists')" 
	)
);
?>