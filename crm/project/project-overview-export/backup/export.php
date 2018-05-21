<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


if(isset($_POST["export"]))
{
	global $APPLICATION;

	// File Name For Download
	$fileName = "sample.xls";

	$APPLICATION->RestartBuffer();
	header("Content-Disposition: attachment; filename=\"$fileName\"");
	header("Content-Type: application/vnd.ms-excel");
	Header('Content-Type: application/octet-stream');
	Header('Content-Transfer-Encoding: binary');

	if (defined('BX_UTF') && BX_UTF)
		echo chr(239).chr(187).chr(191);

	?>
		<!DOCTYPE html>
		<html>
		<head><meta http-equiv="Content-type" content="text/html;charset=<?echo LANG_CHARSET?>" /></head>
		<body>
			<table cellpadding='0' cellspacing='0'>
				<thead><tr><th>Name</th></tr></thead>
			</table>
		</body>
		</html>
	<?

	
	
}
?>