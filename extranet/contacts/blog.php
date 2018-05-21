<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Blogs");
?>
<?
$APPLICATION->IncludeComponent("bitrix:extranet.blog.new_posts.list", ".default", Array(
	"MESSAGE_PER_PAGE"	=> "25",
	"DATE_TIME_FORMAT"	=> $GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATETIME),
	"PATH_TO_BLOG"		=> "/extranet/contacts/personal/user/#user_id#/blog/",
	"PATH_TO_POST"		=> "/extranet/contacts/personal/user/#user_id#/blog/#post_id#/",
	"PATH_TO_USER"		=> "/extranet/contacts/personal/user/#user_id#/",
	"PATH_TO_GROUP_BLOG_POST"	=> "/extranet/workgroups/group/#group_id#/blog/#post_id#/",
	"PATH_TO_SMILE"		=> "/bitrix/images/blog/smile/",
	"PATH_TO_SONET_USER_PROFILE" => "/extranet/contacts/personal/user/#user_id#/",
	"PATH_TO_MESSAGES_CHAT" => "/extranet/contacts/personal/messages/chat/#user_id#/",
	"PATH_TO_VIDEO_CALL" => "/extranet/contacts/personal/video/#user_id#/",
	"PATH_TO_CONPANY_DEPARTMENT" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"BLOG_VAR"		=> "blog",
	"POST_VAR"		=> "post_id",
	"USER_VAR"		=> "user_id",
	"PAGE_VAR"		=> "page",
	"CACHE_TYPE"		=> "A",
	"CACHE_TIME"		=> "3600",
	"SET_TITLE" 		=> "Y",
	)
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>