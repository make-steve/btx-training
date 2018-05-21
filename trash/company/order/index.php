<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<script>
function bxGoTo(url)
{
	BX.ajax.insertToNode('/company/order/proxy.php?'+url, BX('bx-container-order'));
}
</script>
<div id="bx-container-order">
<?
$extPath = "/personal/order/index.php";
include($_SERVER["DOCUMENT_ROOT"]."/company/order/proxy.php");
?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>