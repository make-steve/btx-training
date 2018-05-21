<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/deal/index.php");
$APPLICATION->SetTitle('Company Exact Import');

if (isset($_POST['import_submit'])) {
    require_once('import.php');
}
?>
<style>
.upload-btn-wrapper {
position: relative;
overflow: hidden;
display: inline-block;
float: left;
margin-right: 15px;
}

.btn {
display: inline-block;
text-align: center;
padding-left: 44px;
padding-right: 44px;
padding-top: 3px;
padding-bottom: 3px;
cursor: pointer;
color: #0b66c3;
font-size: 13px;
font-weight: normal;
line-height: 34px;
vertical-align: middle;
background-color: #ffffff;
border: 1px solid #c6cdd3;
border-radius: 2px;
-webkit-transition: background 200ms;
-moz-transition: background 200ms;
-ms-transition: background 200ms;
-o-transition: background 200ms;
transition: background 200ms;
}
.btn:hover {
    background-color: #f5f5f5;
}
.upload-btn-wrapper input[type=file] {
font-size: 100px;
position: absolute;
left: 0;
top: 0;
opacity: 0;
cursor: pointer;
}
.btn_action {
    background-color: #bbed21;
    border: none;
    padding: 10px;
    font-weight: bold;
    /*color: #535c69;*/
    color: #0b66c3;
    cursor: pointer;
    margin-top: 4px;
}
.import_success {
    margin-left: 50px;
    font-style: italic;
    font-weight: 500;
    color: #2067b0;
}
.main-grid-more-btn.submit_btn {
    background-color: #bbed21 !important;
}
</style>
<div class="import_block">
<form method="post" action="<?php echo POST_FORM_ACTION_URI;?>" enctype="multipart/form-data">
    <div class="upload-btn-wrapper">
    <button class="btn">Upload Company File</button>
    <input type="file" name="importfile" />
    </div>
    <button type="submit" name="submit" class="btn_action">Submit</button>
    <input type="hidden" name="import_submit" value="Y">
</form>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>