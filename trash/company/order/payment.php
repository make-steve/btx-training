<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
$APPLICATION->RestartBuffer();
$extPath = "/personal/order/payment.php";
include($_SERVER["DOCUMENT_ROOT"]."/company/order/proxy.php");
die();
?>