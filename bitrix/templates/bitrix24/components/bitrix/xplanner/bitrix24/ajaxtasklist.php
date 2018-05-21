<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(isset($_POST) && !empty($_POST))
{
	global $USER, $DB;

	$arTaskList = array();
	$arTotalTaskUniqueHours = array();
	$totalTaskHours = 0;

	$datetimeStart = strtotime($_POST["date_start"]);
	$arData["DATE_START"] = date('Y-m-d', $datetimeStart) . " 00:00:00";

	$datetimeSelect = strtotime($_POST["date_selected"]);
	$arData["DATE_SELECT"] = date('Y-m-d', $datetimeSelect) . " 00:00:00";

	$datetimeEnd = strtotime($_POST["date_end"]);
	$arData["DATE_END"] = date('Y-m-d', $datetimeEnd) . " 00:00:00";

	$getUserTaskList = "SELECT * FROM `m_timetable_log` where `USER_ID` = " . $USER->GetID() . " AND (`DATE_INSERT` between '" . $DB->ForSQL($arData["DATE_START"]) . "' and '" . $DB->ForSQL($arData["DATE_END"]) . "') and XML_ID IS NOT NULL ORDER BY XML_ID ASC, `DATE_INSERT` ASC;";

	$resTaskList = $DB->Query($getUserTaskList);
	while($arTasks = $resTaskList->Fetch()) {

		$dateExplodeSpace = explode(" ", $arTasks["DATE_INSERT"]);
		$dateExplodeDash = explode("-", $dateExplodeSpace[0]);
		$date = $dateExplodeDash[2] . "-" . $dateExplodeDash[1];

		$arTaskList[$date][] = $arTasks;

		$arTotalTaskUniqueHours[$date . "-" . $arTasks["XML_ID"]] += number_format(floatval($arTasks["HOURS_LOG"]), 2);

		$totalTaskHours += number_format(floatval($arTasks["HOURS_LOG"]), 2);
	}
	
}
ob_start();
?>
<table class="tm-timetable-logs" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th style="width: 9%;">Datum</th>			
			<th style="width: 16%;">Deal / Taak nr.</th>			
			<th style="width: 12%;">Realisatie</th>			
			<th style="width: 37%;">Notitie</th>
			<th style="width: 12%;">Dag totaal</th>
		</tr>
	</thead>

	<tbody>
		<?php

			$countTR = 0;

			if(!empty($arTaskList))
			{
				ksort($arTaskList);
				$hasViewTotal = array();
				
				foreach ($arTaskList as $dateKey => $dateVals) 
				{
					$countVal = 0;
					$trGetTotalHours = "";
					foreach ($dateVals as $key => $taskInfo) 
					{
						$getDataDateEx = explode(" ", $taskInfo["DATE_INSERT"]);
						$getDataDate = $getDataDateEx[0];

						if(($countTR%2) == 0)
							$bgcolor = "#F7F9FA";
						else
							$bgcolor = "#FFF";

						$hourFormat = str_replace(".", ",", $taskInfo["HOURS_LOG"]);
						$totalHoursFormat = str_replace(".", ",", $arTotalTaskUniqueHours[$dateKey . "-" . $taskInfo["XML_ID"]]);

						$trDate = $dateKey;
					
						if($countVal != 0) {
							$trDate = "&nbsp;";
						}
						if(in_array($dateKey . "-" . $taskInfo["XML_ID"], $hasViewTotal)
							&& $countVal != 0) 
						{
							$trTotalHours = "&nbsp;";
						} 
						else
						{
							$hasViewTotal[] = $dateKey . "-" . $taskInfo["XML_ID"];
							$trGetTotalHours = $arTotalTaskUniqueHours[$dateKey . "-" . $taskInfo["XML_ID"]];
							$trTotalHours = $totalHoursFormat;
						}


						?>
							<tr class="task-list-id-<?php echo $taskInfo["ID"]?>" id="task-list-id" rel="<?php echo $taskInfo["ID"]?>" bgcolor="<?php echo $bgcolor?>">
								<td style="width: 9%;"><?php echo $trDate?></td>
								<td style="width: 16%;"><?php echo $taskInfo["XML_ID"]?></td>
								<td style="width: 12%;">
									<?php if($getDataDate == date('Y-m-d')):?>
										<div title="Edit" alt="Edit" class="crm-lead-header-inner-edit-btn-popup" id="edit-<?php echo $taskInfo["ID"]?>" rel="<?php echo $taskInfo["ID"]?>"></div>
										<div title="Save" alt="Save" class="crm-lead-header-inner-check-btn-popup" id="check-<?php echo $taskInfo["ID"]?>" rel="<?php echo $taskInfo["ID"]?>"></div>
									<?php endif;?>
									<div id="hourstext-<?php echo $taskInfo["ID"]?>" class="hourstext" style="float: left"><?php echo $hourFormat?></div>
									<?php if($getDataDate == date('Y-m-d')):?>
										<div id="hoursinput-<?php echo $taskInfo["ID"]?>" class="hoursinput" style="float: left; display: none"><input type="text" class="hoursinputtext" id="hoursinputtext-<?php echo $taskInfo["ID"]?>" rel="<?php echo $taskInfo["ID"]?>" value="<?php echo $hourFormat?>"/></div>
									<?php endif;?>
								</td>
								<td style="width: 37%;">
									<div id="commenttext-<?php echo $taskInfo["ID"]?>" class="commenttext" style="float: left"><?php echo $taskInfo["COMMENTS"]?></div>
									<?php if($getDataDate == date('Y-m-d')):?>
										<div id="commentinput-<?php echo $taskInfo["ID"]?>" class="commentinput" style="float: left; display: none"><textarea rows="3" class="commentinputtext" id="commentinputtext-<?php echo $taskInfo["ID"]?>"><?php echo $taskInfo["COMMENTS"]?></textarea></div>
									<?php endif;?>
								</td>
								<td style="width: 12%; text-align: right;"><b><?php echo $trTotalHours?></b></td>
							</tr>
						<?
						$countVal++;
						$countTR++;
					}
				}
			}
			else
			{
				?>
					<tr bgcolor="#F7F9FA">
						<td style="width: 20.5%;">&nbsp;</td>
						<td style="width: 21%;">&nbsp;</td>
						<td style="width: 16%;">&nbsp;</td>
						<td style="width: 39%;">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				<?
			}

		?>
	</tbody>

<?php
	if(!empty($arTaskList))
	{
		if(($countTR%2) == 0)
			$bgcolor = "#F7F9FA";
		else
			$bgcolor = "#FFF";
		?>
			<tfoot>
				<tr bgcolor="<?php echo $bgcolor?>">
					<td colspan="4" style="border-top: 1px solid #d9d9d9;"><b>Totaal week</b></td>
					<td style="border-top: 1px solid #d9d9d9; text-align: right;"><b><?php echo $totalTaskHours?></b></td>
				</tr>
			</tfoot>
		<?
	}
?>
</table>

<?php
	if(empty($arTaskList))
	{
		?>
			<style type="text/css">
				.tm-dealtask-list { /*height: 75px !important;*/ overflow: hidden; }
			</style>
		<?
	}
?>

<style type="text/css">
	.hoursinput { display: none; }
	.hoursinputtext { width: 40px; }
	.crm-lead-header-inner-check-btn-popup { display: none; }
	.commentinput {margin:5px 0; padding:0px; width: 100%;}
</style>

<script type="text/javascript">

	var getPencil = document.getElementsByClassName('crm-lead-header-inner-edit-btn-popup');

	var editPencil = function() {
	    var attribute = this.getAttribute("rel");
	    document.getElementById("hourstext-" + attribute).style.display = 'none';
	    document.getElementById("hoursinput-" + attribute).style.display = 'block';
	    document.getElementById("commenttext-" + attribute).style.display = 'none';
	    document.getElementById("commentinput-" + attribute).style.display = 'block';
	    document.getElementById("edit-" + attribute).style.display = 'none';
	    document.getElementById("check-" + attribute).style.display = 'block';
	};

	for (var i = 0; i < getPencil.length; i++) {
	    getPencil[i].addEventListener('click', editPencil, false);
	}

	var getCheck = document.getElementsByClassName('crm-lead-header-inner-check-btn-popup');

	var applyCheck = function() { executeSave(this); };

	for (var i = 0; i < getPencil.length; i++) {
	    getCheck[i].addEventListener('click', applyCheck, false);
	}


	var hoursinputted = document.getElementsByClassName('hoursinputtext');

	var inputHours = function(e) {

		if(e.keyCode == 13)
		{
			if(this.value != "" && this.value != " " && typeof this.value !== "undefined")
				executeSave(this);
			else
				alert("Please follow format hours (2,50)");
		}
		
	    var checkNumber = this.value;
		var tempVal = "";
		var hasComma = false;

		for (var i = 0; i < 5; i++) 
		{
			if(!isNaN(checkNumber[i]) || checkNumber[i] == ",")
			{
				if((checkNumber[i] == ",") && hasComma == false) {
					hasComma = true;
					tempVal += checkNumber[i];
				}
				if(!isNaN(checkNumber[i]))
					tempVal += checkNumber[i];
			}
		}
		this.value = tempVal;
	};

	for (var i = 0; i < hoursinputted.length; i++) {
	    hoursinputted[i].addEventListener('keyup', inputHours, false);
	}

	function executeSave(element)
	{
		var attribute = element.getAttribute("rel");
	    document.getElementById("hourstext-" + attribute).style.display = 'block';
	    document.getElementById("hoursinput-" + attribute).style.display = 'none';
	    document.getElementById("commenttext-" + attribute).style.display = 'block';
	    document.getElementById("commentinput-" + attribute).style.display = 'none';
	    document.getElementById("edit-" + attribute).style.display = 'block';
	    document.getElementById("check-" + attribute).style.display = 'none';

	    var data = [];
	    var dealtaskid = attribute;
	    var hourslog = document.getElementById("hoursinputtext-" + attribute).value;
	    var commentlog = document.getElementById("commentinputtext-" + attribute).value;

	    data["dealtaskid"] = dealtaskid;
	    data["hourslog"] = hourslog;
	    data["commentlog"] = commentlog;

	    if(typeof hourslog === "undefined")
	    	alert("Please fill in necessary data");
	    else
	    {
	    	var query_data = {
				'method': 'POST',
				'url': '/bitrix/templates/bitrix24/components/bitrix/planner/bitrix24/actions.php',
				'data':  BX.ajax.prepareData(data),
				'onsuccess': function(data) {
					
					var getCurrentDateSelected = document.getElementById("tm-popup-date").value;
					var aDate = getCurrentDateSelected.split("/");
					var today = new Date(aDate[1] + '/' + aDate[0] + '/' + aDate[2]);
					var firstday = today.GetFirstDayOfWeek();
					var lastday = today.GetLastDayOfWeek();

					var datadates = [];
					datadates["start"] = firstday;
					datadates["selected"] = today;
					datadates["end"] = lastday;

					redrawList(datadates);

				},
				'onfailure': function(type, e) {
					
				}
			};

			BX.ajax(query_data);
	    }
	}


	function redrawList(datadates)
	{
		var innerAjaxHtml = "";
		var timelist = document.getElementById("tm-dealtask-list");
		var timelistAjax = document.getElementById("tm-dealtask-ajax-loader");
		var timelistContainer = document.getElementById("tm-dealtask-list-container");

		var data = [];
		data["date_start"] = datadates["start"].getDate() + "-" + (datadates["start"].getMonth() + 1) + "-" + datadates["start"].getFullYear();
		data["date_selected"] = datadates["selected"].getDate() + "-" + (datadates["selected"].getMonth() + 1) + "-" + datadates["selected"].getFullYear();
		data["date_end"] = datadates["end"].getDate() + "-" + (datadates["end"].getMonth() + 1) + "-" + datadates["end"].getFullYear();

		timelistAjax.style.display = "block";
		timelistContainer.style.display = "none";

		var query_data = {
			'method': 'POST',
			'url': '/bitrix/templates/bitrix24/components/bitrix/planner/bitrix24/ajaxtasklist.php',
			'data':  BX.ajax.prepareData(data),
			'onsuccess': function(data) {
				timelistAjax.style.display = "none";
				timelistContainer.innerHTML = data;
				timelistContainer.style.display = "block";
			},
		};

		BX.ajax(query_data);
	}

</script>

<?php
$content = ob_get_contents();
ob_get_clean();

echo $content;
?>