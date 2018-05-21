<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (count($arResult["ITEMS"]) < 1)
	return;


$todayEnd = mktime(23, 59, 59, date("m"), date("d"), date("Y"));

$arEvents = Array();
foreach($arResult["ITEMS"] as $i => $arItem)
{
	$dateFrom = MakeTimeStamp($arItem["DATE_FROM"], getTSFormat());
	if ($dateFrom < $todayEnd)
		$arEvents[] = $arItem;
}

$arResult["ITEMS"] = $arEvents;

?>