<?
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!CModule::IncludeModule("bitrix24"))
	return "No Bitrix24 module";

$path = $_SERVER["QUERY_STRING"];

$proxy = new CBitrix24OrderProxy();
$proxy->userDomain = "ezhik.bitrix24.ru";
$proxy->userEmail = $USER->GetEmail();
$proxy->userName = $USER->GetFullName();
$proxy->scheme = "https";
$proxy->port = 443;
$proxy->server = "www.bitrixsoft.com";
if(LANGUAGE_ID == "ru")
	$proxy->server = "www.1c-bitrix.ru";
elseif(LANGUAGE_ID == "de")
	$proxy->server = "www.bitrix.de";

if (!$proxy->IsInitialized())
	die(CBitrix24OrderProxy::getError("SPR301"));

$arPath = parse_url($path);

$request = array(
	"METHOD" => $_SERVER["REQUEST_METHOD"],
	"PATH" => $path,
	"HEADERS" => array(),
	"BODY" => array()
);

if(!empty($extPath))
{
	$arPath["path"] = $extPath;
	$request["PATH"] = $path = $arPath["path"];
	if(!empty($_GET))
	{
		foreach ($_GET as $key => $val)
			$request["BODY"][$key] = $val;
	}
}
$arAvailableUrls = array(
	"/buy_tmp/order24.php",
	"/personal/order/payment.php",
	"/personal/order/index.php",
	"/personal/order/detail/",
);
$bAvaible = false;
foreach($arAvailableUrls as $val)
{
	if(strpos($arPath["path"], $val) === 0)
		$bAvaible = true;
}
if (!$bAvaible)
	die(CBitrix24OrderProxy::getError("SPR302"));


if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	foreach ($_POST as $key => $val)
		$request["BODY"][$key] = $val;
}

$APPLICATION->SetTitle(GetMessage("B24SP_TITLE"));
if($arPath["path"] == "/buy_tmp/order24.php")
	$APPLICATION->SetTitle(GetMessage("B24SP_TITLE_ORDER"));


if($arPath["path"] == "/buy_tmp/order24.php" && IntVal($request["BODY"]["ORDER_ID"]) <= 0)
{
	$request["BODY"]["product"] = $productID;
}
$request["BODY"]["frmb24"] = "Y";

$proxy->Connect();
$response = $proxy->Send($request);
$proxy->Disconnect();

if ($response == null)
	die(CBitrix24OrderProxy::getError("SPR303"));

$body = $response["BODY"];

if (isset($response["CONTENT"]["ENCODING"]) && (in_array($response["CONTENT"]["TYPE"], array("text/xml", "application/xml", "text/html"))))
{
	$utf8Encoding = (strtoupper($response["CONTENT"]["ENCODING"]) == "UTF-8");
	if (!$utf8Encoding && defined("BX_UTF"))
		$body = CharsetConverter::ConvertCharset($body, $response["CONTENT"]["ENCODING"], "UTF-8");
	elseif ($utf8Encoding && !defined("BX_UTF"))
		$body = CharsetConverter::ConvertCharset($body, "UTF-8", SITE_CHARSET);
}
if($arPath["path"] == "/personal/order/payment.php")
{
	if(strpos($body, "<html") !== false && strpos($body, "<title>".GetMessage("B24SP_PAYMENT_TITLE")."</title>") === false)
		die(CBitrix24OrderProxy::getError("SPR304"));
}
elseif(strpos($body, "<html") !== false)
	die(CBitrix24OrderProxy::getError("SPR305"));

if(strpos($body, "ERROR_EXTERNAL_B24") !== false) //error on auth and order
	die(CBitrix24OrderProxy::getError("SPR306"));

$body = preg_replace(
	array(
		"/\<script\stype\=\"text\/javascript\"\ssrc=\"\/bitrix\/js\/main\/core\/core.js(.+?)\><\/script([^>]*)\>/is",
		"/\<script\stype\=\"text\/javascript\"\ssrc=\"\/bitrix\/js\/main\/core\/core_fx.js(.+?)\><\/script([^>]*)\>/is",
		"/\<script\stype\=\"text\/javascript\"\ssrc=\"\/bitrix\/js\/main\/core\/core_ajax.js(.+?)\><\/script([^>]*)\>/is",
		"/\<script\stype\=\"text\/javascript\"\ssrc=\"\/bitrix\/js\/main\/core\/core_window.js(.+?)\><\/script([^>]*)\>/is",
		"/\<script\stype\=\"text\/javascript\"\ssrc=\"\/bitrix\/js\/main\/session.js(.+?)\><\/script([^>]*)\>/is",
		"/\<script\stype\=\"text\/javascript\"\ssrc=\"\/bitrix\/js\/main\/ajax.js(.+?)\><\/script([^>]*)\>/is",
		"/\<link\shref=\"\/bitrix\/js\/main\/core\/css\/core.css(.+?)\>/is",
		"/\<link\shref=\"\/bitrix\/js\/main\/core\/css\/core_window.css(.+?)\>/is",
		"/\<link\shref=\"\/bitrix\/templates\/\.default\/ajax\/ajax.css(.+?)\>/is",
		'/[\001\002]/',
		'/\<script\stype\=\"text\/javascript\"\>/i', 
		'/\<\/script\>/i',
		'/\001([^\001\002]*)bxSession\.mess\.messSessExpired([^\002]+)\002/is', 
		'/\001/is', 
		'/\002/is',
		),
	array(
		'', '', '', '', '', '', '', '', '',
		'', 
		"\001",
		"\002",
		'',
		'<script type="text/javascript">', 
		'</script>'
),
	$body
);


$body = str_replace("'content_url':'/license.php", "'content_url':'https://www.1c-bitrix.ru/license.php", $body);
$body = preg_replace(
	"#(\"|')(/bitrix/([a-z0-9_.-]+/)*([a-z0-9_.-]+\.(css|js)))#i",
	"\"https://www.1c-bitrix.ru$2",
	$body
);
$body = preg_replace(
	"#(\"|')(/images/([a-z0-9_.-]+/)*([a-z0-9_.-]+\.(gif|png|jpg)))#i",
	"\"https://www.1c-bitrix.ru$2",
	$body
);
$body = preg_replace_callback(
	"#(<a\s[^>]*?)(href\s*=\s*(\"|'))(?!http\:\/\/www\.bitrix24\.ru|javascript|\/personal\/order\/payment\.php|\/personal\/payment\.php|mailto\:)(.+?)(\"|')#i",
	Array("CBitrix24OrderProxy", "b24checkhref"),
	$body
);

if($arPath["path"] == "/buy_tmp/order24.php")
	$body = str_replace("window.top.location.href='/buy_tmp/order24.php", "window.top.location.href='/company/order/make.php", $body);

$body = str_replace(Array("/personal/order/payment.php", "/personal/payment.php"), "/company/order/payment.php", $body);

echo $body;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");