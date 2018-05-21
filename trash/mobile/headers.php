<?
define("BX_SKIP_SESSION_EXPAND", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
define('BX_PULL_MOBILE', true);
define('BX_PULL_SKIP_LS', true);
define('BX_PULL_SKIP_WEBSOCKET', true);
if (!defined('BX_DONT_SKIP_PULL_INIT'))
	define("BX_PULL_SKIP_INIT", true);
?>