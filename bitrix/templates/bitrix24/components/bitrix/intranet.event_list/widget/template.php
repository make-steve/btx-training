<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (count($arResult["ITEMS"]) < 1)
	return;

$this->SetViewTarget("sidebar", 100);
?>

<div class="sidebar-widget sidebar-widget-events">
	<div class="sidebar-widget-title"><?=GetMessage("WIDGET_CALENDAR_TITLE")?></div>
	<div class="sidebar-widget-content">

	<?foreach($arResult["ITEMS"] as $i => $arItem):?>
		<div class="sidebar-widget-event<?=($i == 0 ? " sidebar-widget-event-first": "")?>">
			<div class="sidebar-widget-event-date"><?=$arItem["DATE_FROM"]?></div>
			<div class="sidebar-widget-event-icon"></div>
			<div class="sidebar-widget-event-title"><?=$arItem["NAME"]?></div>
			<a href="<?=$arItem["_DETAIL_URL"]?>" class="sidebar-widget-event-details"><?=GetMessage("WIDGET_CALENDAR_DETAILS")?></a>
		</div>
	<?endforeach?>


	</div>
</div>