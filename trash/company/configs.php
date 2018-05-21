<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if (!$GLOBALS['USER']->CanDoOperation('bitrix24_config'))
	die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix24/public/company/configs.php");
$activateError = "";

if ($license_name = COption::GetOptionString("main", "~controller_group_name"))
	$f = preg_match("/(project|team|company|nfr)$/is", $license_name, $matches);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["logo_save"]) && check_bitrix_sessid())
{
	if (isset($_REQUEST["logo_name"]) && strlen($_REQUEST["logo_name"])>0)
	{
		COption::SetOptionString("main", "site_name", $_REQUEST["logo_name"]);
		$iblockID = COption::GetOptionInt("intranet", "iblock_structure");
		$db_up_department = CIBlockSection::GetList(Array(), Array("SECTION_ID"=>0, "IBLOCK_ID"=>$iblockID));
		if ($ar_up_department = $db_up_department->Fetch())
		{
			$up_dep_id = $ar_up_department['ID'];
			if (CIBlockRights::UserHasRightTo($iblockID, $up_dep_id, 'section_edit'))
			{
				$section = new CIBlockSection;
				$res = $section->Update($up_dep_id, array("NAME" => $_REQUEST["logo_name"]));
			}
		}
	}
	if (strlen($_POST["rating_text_like_y"])>0)
		COption::SetOptionString("main", "rating_text_like_y", htmlspecialchars($_POST["rating_text_like_y"]));
	if (strlen($_POST["rating_text_like_n"])>0)
		COption::SetOptionString("main", "rating_text_like_n", htmlspecialchars($_POST["rating_text_like_n"]));	
	if (strlen($_POST["email_from"])>0 && check_email($_POST["email_from"]))
		COption::SetOptionString("main", "email_from", ($_POST["email_from"]));
	else
		$activateError = GetMessage("CONFIG_EMAIL_ERROR");
	if ($license_name && ($matches[0] == "team" || $matches[0] == "company"))
	{
		if (strlen($_POST["logo24"])>0)
			COption::SetOptionString("bitrix24", "logo24show", "Y");
		else
			COption::SetOptionString("bitrix24", "logo24show", "N");
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["site"]) && isset($_POST["coupon"]) && check_bitrix_sessid())
{
	$arFields = Array(
		"COUPON" => $_POST["coupon"],
		"SITE" => $_POST["site"]
	);
	$ht = new CHTTP();
	if($res = $ht->Post("https://www.1c-bitrix.ru/buy_tmp/b24_coupon.php", $arFields))
	{
		if(strpos($res, "OK") === false)
		{
			$activateError = GetMessage("CONFIG_ACTIVATE_ERROR");
		}
		else
		{
			global $APPLICATION;
			LocalRedirect($APPLICATION->GetCurPage());			
		}
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix24/public/company/configs.php");

$APPLICATION->SetTitle(GetMessage("CONFIG_TITLE"));

$UserMaxCount = intval(COption::GetOptionString("main", "PARAM_MAX_USERS"));
$arFilter = Array("ACTIVE" => 'Y',  "!=UF_DEPARTMENT"=>false);
$rsUsers = CUser::GetList($by = 'ID', $order = 'ASC', $arFilter, array("FIELDS" => array("ID")));
$UserCount = $rsUsers->SelectedRowsCount();
if ($UserMaxCount > 0 && $UserCount > $UserMaxCount)
{
	$UserMaxCount = $UserCount;
}

if (CModule::IncludeModule("extranet"))
{
	$arFilter = Array("ACTIVE" => 'Y', "GROUPS_ID" => Array(CExtranet::GetExtranetUserGroupID()),"=UF_DEPARTMENT"=>false);
	$rsUsers = CUser::GetList($by = 'ID', $order = 'ASC', $arFilter, array("FIELDS" => array("ID")));
	$UserExCount = $rsUsers->SelectedRowsCount();
	if ($UserMaxCount > 0 && $UserCount + $UserExCount > $UserMaxCount)
	{
		$UserMaxCount = $UserCount + $UserExCount;
	}
}

$DiscSpaceLimit = COption::GetOptionString("main_size", "~max_size");
$DiscUsage = COption::GetOptionString("main_size", "~cloud");
$DBUsage = COption::GetOptionString("main_size", "~db");


?>
<? if(!empty($activateError)): ?> 
	<div class="content-edit-form-notice-error"><span class="content-edit-form-notice-text"><span class="content-edit-form-notice-icon"></span><?=$activateError?></span></div>
<?elseif(isset($_POST['logo_name'])): ?>
	<div class="content-edit-form-notice-successfully"><span class="content-edit-form-notice-text"><span class="content-edit-form-notice-icon"></span><?=GetMessage('CONFIG_SAVE_SUCCESSFULLY')?></span></div>
<? endif; ?>
<form name="configPostForm" id="configPostForm" method="POST" action="/company/configs.php">
	<table id="content-edit-form-config" class="content-edit-form" cellspacing="0" cellpadding="0">
		<input type="hidden" name="logo_save" value="true" >
		<?=bitrix_sessid_post();?>
		<tr>
			<td class="content-edit-form-header content-edit-form-header-first" colspan="3" >
				<div class="content-edit-form-header-wrap content-edit-form-header-wrap-blue"><?=GetMessage('CONFIG_HEADER_SETTINGS')?></div>
			</td>
		</tr>
		<tr data-field-id="company_name">
			<td class="content-edit-form-field-name"><?=GetMessage('CONFIG_COMPANY_NAME')?></td>
			<td class="content-edit-form-field-input"><input type="text" name="logo_name" value="<?=htmlspecialchars(COption::GetOptionString("main", "site_name", ""));?>"  class="content-edit-form-field-input-text"/></td>
			<td class="content-edit-form-field-error"></td>
		</tr>
		<tr data-field-id="company_name">
			<td class="content-edit-form-field-name"><?=GetMessage('config_rating_label_likeY')?></td>
			<td class="content-edit-form-field-input"><input type="text" name="rating_text_like_y" value="<?=htmlspecialchars(COption::GetOptionString("main", "rating_text_like_y", ""));?>"  class="content-edit-form-field-input-text"/></td>
			<td class="content-edit-form-field-error"></td>
		</tr>
		<tr data-field-id="company_name">
			<td class="content-edit-form-field-name"><?=GetMessage('config_rating_label_likeN')?></td>
			<td class="content-edit-form-field-input"><input type="text" name="rating_text_like_n" value="<?=htmlspecialchars(COption::GetOptionString("main", "rating_text_like_n", ""));?>"  class="content-edit-form-field-input-text"/></td>
			<td class="content-edit-form-field-error"></td>
		</tr>
		<tr data-field-id="company_name">
			<td class="content-edit-form-field-name"><?=GetMessage('CONFIG_EMAIL_FROM')?></td>
			<td class="content-edit-form-field-input"><input type="text" name="email_from" value="<?=htmlspecialchars(COption::GetOptionString("main", "email_from", ""));?>"  class="content-edit-form-field-input-text"/></td>
			<td class="content-edit-form-field-error"></td>
		</tr>
		<?if ($license_name && ($matches[0] == "team" || $matches[0] == "company")):
			$logo24show = COption::GetOptionString("bitrix24", "logo24show", "Y");
		?>
		<tr data-field-id="company_name">
			<td class="content-edit-form-field-name"><?=GetMessage('CONFIG_LOGO_24')?></td>
			<td class="content-edit-form-field-input"><input type="checkbox" name="logo24" <?if ($logo24show == "" || $logo24show == "Y"):?>checked<?endif?> class="content-edit-form-field-input-selector"/></td>
			<td class="content-edit-form-field-error"></td>
		</tr>
		<?endif;?>
		<tr>
			<td class="content-edit-form-field-name"></td>
			<td class="content-edit-form-buttons" colspan="2">
				<a href="#save" onclick="BX.submit(BX('configPostForm')); return false;" onmouseup="BX.removeClass(this,'content-edit-form-button-press')" onmousedown="BX.addClass(this, 'content-edit-form-button-press')" class="content-edit-form-button"><span class="content-edit-form-button-left"></span><span class="content-edit-form-button-text"><?=GetMessage("CONFIG_SAVE")?></span><span class="content-edit-form-button-right"></span></a>
			</td>
		</tr>
		<tr>
			<td class="content-edit-form-header" colspan="3" >
				<div class="content-edit-form-header-wrap">
				<?
				echo GetMessage('CONFIG_LICENSE_NAME');
				if ($license_name)
				{
					echo " ".GetMessage($matches[0]); 
				}
				if($license_till = COption::GetOptionString("main", "~controller_group_till")) echo GetMessage("CONFIG_LICENSE_TILL", array("#LICENSETILL#" => ConvertTimeStamp($license_till)));
				?>
				</div>
			</td>
		</tr>
		<tr>
			<td class="content-edit-form-field-name"><?=GetMessage("CONFIG_USERS_COUNT")?></td>
			<td class="content-edit-form-field-input">
				<div class="content-edit-form-title-license"><?if ($UserMaxCount > 0):?><?=GetMessage("CONFIG_USERS_COUNT_LIMIT")?> <strong><?=$UserMaxCount;?></strong><?else: echo GetMessage("CONFIG_NO_LIMIT"); endif;?></div>
				<?if ($UserMaxCount > 0):?>
				<div class="content-edit-form-chart">
					<span class="content-edit-form-chart-bar-yellow" style="width:<?=round(($UserCount/$UserMaxCount)*100)?>%"></span><span class="content-edit-form-chart-bar-green"  style="width:<?=round(($UserExCount/$UserMaxCount)*100)?>%"></span>
				</div>
				<?endif;?>
				<table class="content-edit-form-chart-info" cellspacing="0">
					<tr>
						<td class="content-edit-form-chart-info-left">
							<div class="content-edit-form-bullet-yellow"></div>
						</td>
						<td class="content-edit-form-chart-info-center"><?=GetMessage("CONFIG_USERS")?></td>
						<td class="content-edit-form-chart-info-right"><strong><?=$UserCount?></strong></td>
					</tr>
					<?if (CModule::IncludeModule("extranet")):?>
					<tr>
					<td class="content-edit-form-chart-info-left">
						<div class="content-edit-form-bullet-green"></div>
					</td>
					<td class="content-edit-form-chart-info-center"><?=GetMessage("CONFIG_EXTRANET_USERS")?></td>
					<td class="content-edit-form-chart-info-right"><strong><?=$UserExCount?></strong></td>
					</tr>
					<?endif?>
					<?if ($UserMaxCount > 0):?>
					<tr>
						<td class="content-edit-form-chart-info-left">
							<div class="content-edit-form-bullet-transp"></div>
						</td>
						<td class="content-edit-form-chart-info-center"><?=GetMessage("CONFIG_USERS_FREE")?></td>
						<td class="content-edit-form-chart-info-right"><strong><?$UserFree = $UserMaxCount - $UserCount; if ($UserExCount>0) $UserFree -= $UserExCount; if ($UserFree>=0) echo $UserFree; else echo "0";?></strong></td>
					</tr>
					<?endif;?>
				</table>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>
		<tr>
			<td class="content-edit-form-field-name content-edit-form-license"><?=GetMessage("CONFIG_DISC_SPACE")?></td>
			<td class="content-edit-form-field-input content-edit-form-license">
				<div class="content-edit-form-title-license"><?=GetMessage("CONFIG_DISC_SPACE_LIMIT")?> <strong><?=CFile::FormatSize($DiscSpaceLimit)?></strong></div>
				<?if ($DiscSpaceLimit > 0):?>
				<div class="content-edit-form-chart">
					<span class="content-edit-form-chart-bar-yellow" style="width:<?=round(($DBUsage/$DiscSpaceLimit)*100)?>%"></span><span class="content-edit-form-chart-bar-green"  style="width:<?=round(($DiscUsage/$DiscSpaceLimit)*100)?>%"></span>
				</div>
				<?endif;?>
				<table class="content-edit-form-chart-info" cellspacing="0">
					<tr>
						<td class="content-edit-form-chart-info-left">
							<div class="content-edit-form-bullet-yellow"></div>
						</td>
						<td class="content-edit-form-chart-info-center"><?=GetMessage("CONFIG_DB_USAGE")?></td>
						<td class="content-edit-form-chart-info-right"><strong><?=CFile::FormatSize($DBUsage)?></strong></td>
					</tr>
					<tr>
						<td class="content-edit-form-chart-info-left">
							<div class="content-edit-form-bullet-green"></div>
						</td>
						<td class="content-edit-form-chart-info-center"><?=GetMessage("CONFIG_DISC_USAGE")?></td>
						<td class="content-edit-form-chart-info-right"><strong><?=CFile::FormatSize($DiscUsage)?></strong></td>
					</tr>
					<?if ($DiscSpaceLimit > 0):?>
					<tr>
						<td class="content-edit-form-chart-info-left">
							<div class="content-edit-form-bullet-transp"></div>
						</td>
						<td class="content-edit-form-chart-info-center"><?=GetMessage("CONFIG_DISC_SPACE_FREE")?></td>
						<td class="content-edit-form-chart-info-right"><strong><?=CFile::FormatSize($DiscSpaceLimit-$DBUsage-$DiscUsage)?></strong></td>
					</tr>
					<?endif;?>
				</table>
			</td>
			<td class="content-edit-form-field-error content-edit-form-license"></td>
		</tr>	
	</table>		
</form>

<form name="ActivateLicenseForm" id="ActivateLicenseForm" method="POST" action="/company/configs.php">
	<table class="content-edit-form" cellspacing="0" cellpadding="0">	
		<?=bitrix_sessid_post();?>
		<input type="hidden" name="site" value="<?=$_SERVER['HTTP_HOST']?>">
		<tr>
			<td class="content-edit-form-header content-edit-form-header-first" colspan="3" >
				<div class="content-edit-form-header-wrap"><?=GetMessage('CONFIG_LICENSE_ACTIVATE')?></div>
			</td>
		</tr>
		<tr data-field-id="company_name">
			<td class="content-edit-form-field-name"><?=GetMessage('CONFIG_COUPON')?></td>
			<td class="content-edit-form-field-input"><input type="text" name="coupon" value=""  class="content-edit-form-field-input-text"/></td>
			<td class="content-edit-form-field-error"></td>
		</tr>
		<tr>
			<td class="content-edit-form-field-name"></td>
			<td class="content-edit-form-field-input content-edit-form-activate-license" colspan="2">
				<a href="#save" onclick="BX.submit(BX('ActivateLicenseForm')); return false;" onmouseup="BX.removeClass(this,'content-edit-form-button-press')" onmousedown="BX.addClass(this, 'content-edit-form-button-press')" class="content-edit-form-button"><span class="content-edit-form-button-left"></span><span class="content-edit-form-button-text"><?=GetMessage("CONFIG_ACTIVATE")?></span><span class="content-edit-form-button-right"></span></a>
				<div class="content-edit-form-act-license-text">
					<?=GetMessage("CONFIG_LICENSE_DESCRIPTION")?>	
				</div>
			</td>
		</tr>
	</table>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>