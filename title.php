<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
?>HI admin, on this page you can check if every project manager send his takenlijst for invoice. Please fill in the date till when you want to invoice. Then hit the button CHECK and the system will show what project manager did not send his takenlijst in just yet.<br>
<br>
<br>
SELECT DATE:&nbsp; 1 november 2017 &lt;&lt;----- datepicker<br>
<br>
[CHECK BUTTON]<br>
<br>
<br>
-----------------------------------------<br>
<br>
The following project managers didn't send their taken just yet:<br>
<br>
Sander Buijs: His last takenlijst was send 1 Januari<br>
Nadya: send last takenlijst septemer 30<br>
<br>
<br>
[Generate XML &amp; XLS FILE FOR EXACT IMPORT]<br>
<br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>