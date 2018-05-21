<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
if (function_exists('mb_internal_encoding'))
	mb_internal_encoding('ISO-8859-1');

$offset = intval($_REQUEST['offset']);
if ($_REQUEST['file'])
{
	if (!($f = fopen($_SERVER['DOCUMENT_ROOT'].$_REQUEST['file'], 'rb')))
		die('Cannot read '.htmlspecialchars($_REQUEST['file']));
	fseek($f, $offset);

	$str = '';
	$open = $close = 0;
	while(false !== ($l = fgets($f)))
	{
		$open += substr_count($l, '{');
		$close += substr_count($l, '}');

		$str .= $l;

		if ($open > 0 && $close >= $open)
			break;
	}
	fclose($f);
	$str = highlight_string("<?"."php \n//	$_REQUEST[name]\n//	$_REQUEST[file]:$_REQUEST[line]\n\n".$str,1);
	if ($_REQUEST['highlight'])
		$str = str_replace($_REQUEST['highlight'],'<span style="background:#FFFF00">'.$_REQUEST['highlight'].'</span>',$str);
	die($str);
}

$dbtype = 'mysql';
$path = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules';
if (!($dir = opendir($path)))
	die('Cannot read '.$path);

$arMod = array();
while(false !== ($file = readdir($dir)))
{
	if ($file == '.' || $file == '..' || !is_dir($path.'/'.$file))
		continue;

	$arMod[] = $file;
}

sort($arMod);
echo '<select onchange="document.location=\'?module=\'+this.value">';
foreach($arMod as $mod)
	echo '<option value="'.$mod.'" '.($mod == $_REQUEST['module'] ? 'selected' : '').'>'.$mod.'</option>';
echo '</select>';

if ($module = $_REQUEST['module'])
{
	$arRes = array();
	$arEvt = array();

	$path = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module.'/include.php';
	$arRes = ParseFile($path, $arEvt);

	$path = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module.'/tools.php';
	if (file_exists($path))
	{
		if (false !== ($ar = ParseFile($path, $arEvt)))
			$arRes = array_merge($arRes, $ar);
	}
	
	foreach(array('general',$dbtype) as $folder)
	{
		$path = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module.'/classes/'.$folder;

		if (!file_exists($path))
			$path = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module.'/'.$folder;

		if (!file_exists($path))
			continue;

		if (!($dir = opendir($path)))
			die('Cannot read '.$path);

		while(false !== ($file = readdir($dir)))
		{
			if ($file == '.' || $file == '..' || is_dir($path.'/'.$file) || end(explode('.',$file)) != 'php')
				continue;

			if (!is_array($ar = ParseFile($path.'/'.$file, $arEvt)))
				continue;

			$arRes = array_merge($arRes, $ar);
		}
	}
	

	if (count($arEvt))
	{
		ksort($arEvt);
		echo '<h2>The '.htmlspecialchars($module).' module events</h2>';
		echo '<table border=1 cellpadding=4 cellspacing=0>';
			echo 
			'<tr align=center bgcolor="#CCCCCC">'.
				"<td><b>Events</td>".
				"<td><b>Method called</td>".
			'</tr>';

		foreach($arEvt as $evt => $func)
		{
			$ar = $arRes[$func];
			$link = "?file=$ar[FILE]&offset=$ar[OFFSET]&name=$func&line=$ar[LINE]&highlight=".$evt;
			echo 
			'<tr>'.
				"<td valign=top class=code><a href='$link' target=_blank>$evt</td>".
				"<td valign=top class=code>$func</td>".
			'</tr>';
		}
		echo '</table>';
	}

	if (count($arRes))
	{
		ksort($arRes);

		echo '<h2>List of the '.htmlspecialchars($module).' module methods and functions </h2>';
		echo '<table border=1 cellpadding=4 cellspacing=0>';
			echo 
			'<tr align=center bgcolor="#CCCCCC">'.
				"<td><b>Method</td>".
			'</tr>';

		foreach($arRes as $func => $ar)
		{
			$link = "?file=$ar[FILE]&offset=$ar[OFFSET]&name=$func&line=$ar[LINE]";
			if ($c = strpos($func, "::"))
				$func = '<span class=class>'.substr($func,0,$c).'</span>::<a href="'.$link.'" target=_blank><span class=method>'.substr($func,$c+2).'</span></a>';
			else
				$func = '<a href="'.$link.'" target=_blank><span class=method>'.$func.'</span></a>';

			$args = preg_replace('#(\$[a-z0-9_]+)#i','<span class=var>\\1</span>',htmlspecialchars($ar['ARGS']));
			echo 
			'<tr>'.
				"<td valign=top class=code>$func"." ($args)</td>".
			'</tr>';
		}
		echo '</table>';
	}
}

function ParseFile($file, &$arEvt)
{
	$f = fopen($file, 'rb');
	if ($f === false)
		return false;
	$arRes = array();

	$len = strlen($_SERVER['DOCUMENT_ROOT']);
	$i = 0;
	$offset = 0;
	$curClass = '';
	$curFunc = '';
	$js = false;
	while(false !== ($l = fgets($f)))
	{
		$i++;
		if (preg_match('#<script#i',$l))
			$js = true;
		if (preg_match('#</script#i',$l))
			$js = false;

		if (!$js)
		{
			if (preg_match('#^\s?class ([a-z0-9_]+)#i', $l, $regs))
			{
				$curClass = preg_replace('#^CAll#i','C',$regs[1]);
				$open = $close = 0;
			}
			elseif (preg_match('#function ([a-z0-9_]+) ?\((.*)\)#i', $l, $regs))
			{
				$curFunc = $func = ($curClass ? $curClass.'::' : '').$regs[1];
				$args = $regs[2];
				$arRes[$func] = array(
					'FILE' => substr($file,$len),
					'LINE' => $i, 
					'OFFSET' => $offset,
					'ARGS' => $args
				);
			}
			elseif (preg_match('#GetModuleEvents\([^,]+,["\' ]*([\$a-z0-9_]+)#i', $l, $regs))
			{
				$event = $regs[1];
				$arEvt[$event] = $curFunc;
			}

			if ($curClass)
			{
				$open += substr_count($l, '{');
				$close += substr_count($l, '}');
			}

			if ($open > 0 && $close >= $open)
				$curClass = '';
		}
		$offset += strlen($l);
	}
	fclose($f);
	return $arRes;
}
?>
<style>
	div {
		border:1px solid #CCC;
		margin:2px;
	}

	td {
		font-family:Verdana,Tahoma,Arial;
	}

	.code {
		font-family:Courier;
	}

	.class {
		color:#993;
	}

	.method {
		color:#66F;
	}

	.var {
		color:#363;
	}
</style>
