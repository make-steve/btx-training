<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Зарплата и отпуск");?>
<?$APPLICATION->IncludeComponent("bitrix:payroll.1c", ".default", array(
	"ORG_LIST" => array(
		0 => "Моя компания",
	),
	"PR_TIMEOUT" => "25",
	"PR_NAMESPACE" => "http://www.1c-bitrix.ru",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"PR_URL_0" => "",
	"PR_PORT_0" => "80",
	"PR_LOGIN_0" => "",
	"PR_PASSWORD_0" => ""
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>