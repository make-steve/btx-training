<?
define('BX_DONT_SKIP_PULL_INIT', true);
require($_SERVER["DOCUMENT_ROOT"] . "/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
//viewport rewrite
CMobile::getInstance()->setLargeScreenSupport(false);
CMobile::getInstance()->setScreenCategory("NORMAL");
$frame = \Bitrix\Main\Page\Frame::getInstance();
$frame->setEnable();
$frame->setUseAppCache();
\Bitrix\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("api_version", CMobile::getApiVersion());
\Bitrix\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("platform", CMobile::getPlatform());
\Bitrix\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("version", "v4");
\Bitrix\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("user", $USER->GetID());

$frame->startDynamicWithID("menu");
$APPLICATION->IncludeComponent("bitrix:mobile.menu", "flat", array(), false, Array("HIDE_ICONS" => "Y"));
$frame->finishDynamicWithID("menu");
$APPLICATION->IncludeComponent("bitrix:mobile.im.messenger", "", array(), false, Array("HIDE_ICONS" => "Y"));
// PUSH Module Event
?>
	<script type="text/javascript">
		BX.addCustomEvent("onPullExtendWatch", function (data)
		{
			BX.PULL.extendWatch(data.id);
		});
		BX.addCustomEvent("thisPageWillDie", function (data)
		{
			BX.PULL.clearWatch(data.page_id);
		});
		BX.addCustomEvent("onPullEvent", function (module_id, command, params)
		{
			app.onCustomEvent('onPull', {'module_id': module_id, 'command': command, 'params': params});
		});
		app.enableSliderMenu(true);
		app.getToken();
		BX.addCustomEvent("onFrameDataReceived", function (data)
			{
				var blocks = data.dynamicBlocks;

				for (var i = 0; i < blocks.length; i++)
				{
					el = BX("bxdynamic_" + blocks[i].ID);
					if (el)
					{
						el.innerHTML = blocks[i].CONTENT;
						BX.frameCache.processData(blocks[i].CONTENT);
					}

				}

			}
		);
	</script>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php") ?>