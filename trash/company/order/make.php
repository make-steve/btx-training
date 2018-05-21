<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<script>
function bxGoTo(url)
{
	BX.ajax.insertToNode('/company/order/proxy.php?'+url, BX('bx-container-order'));
}
</script>
<?
CUtil::InitJSCore(array('window', 'ajax'));
CAjax::Init();
?>
<div id="bx-container-order">
<?
if($_REQUEST["AJAX_CALL"] == "Y")
	$APPLICATION->RestartBuffer();

$extPath = "/buy_tmp/order24.php";
$productID = $_REQUEST["product"];
include($_SERVER["DOCUMENT_ROOT"]."/company/order/proxy.php");
if($_REQUEST["AJAX_CALL"] == "Y")
	die();

?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>