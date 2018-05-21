 <? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
 <? $APPLICATION-> IncludeComponent("bitrix:main.feedback", "support",
 Array (
 "USE_CAPTCHA" => "Y", 
 "OK_TEXT" => "Thank you, your message is accepted.", 
 "EMAIL_TO" => "st.dremin@ya.ru", 
 "REQUIRED_FIELDS" => array (), 
 "EVENT_MESSAGE_ID" => array ()
 ),
 false);
 if ($_SERVER["REQUEST_METHOD"] == "POST")
 {
 echo '<pre>';
 print_r ($_POST);
 echo '</pre>';
 }?> 