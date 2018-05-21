<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Двунаправленная интеграция с Microsoft Outlook");
?>
<script type="text/javascript" src="/bitrix/templates/learning/js/imgshw.js"></script>
 На сайте экстранета реализована не просто интеграция, а <b>двунаправленная интеграция с Microsoft Outlook</b>. Это значит, что можно не просто импортировать данные с сайта в популярную почтовую программу. Сайт и MS Outlook сами между собой договорятся и внесенные изменения в персональные календарях, контактях сотрудников и ваших задачах в одной программе автоматически отобразятся в другой!
<br />

<br />

<table border="0" cellspacing="1" cellpadding="1" width="100%">
  <tbody>
    <tr><td valign="top"><img hspace="10" src="/extranet/images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#my_kalendar" >синхронизация персональных календарей</a>;
        <br />
      <img hspace="10" src="/extranet/images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#company_calendar" >синхронизация календарей компании; </a>
        <br />
      <img hspace="10" src="/extranet/images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#useful" >синхронизация личных контактов</a>;
        <br />
      <img hspace="10" src="/extranet/images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#kalendars" >экспорт нескольких календарей; </a>
        <br />
      <img hspace="10" src="/extranet/images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#kalendars" >отображение календарей на одной сетке в MS Outlook.</a>
        <br />
      <img hspace="10" src="/extranet/images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#useful1" >синхронизация личных задач</a>;
        <br />
      </td><td>
        <br />
      </td><td valign="top">
       	<b>Подключись к Outlook прямо сейчас!</b>
<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default", array(
	"AREA_FILE_SHOW" => "file",
	"PATH" => "/extranet/help/outlook_inc.php",
	"EDIT_TEMPLATE" => ""
	),
	false
);?>

        <br />
      </td></tr>
  </tbody>
</table>

<h2>В чем прелесть двусторонней синхронизации? </h2>
Такая синхронизация позволяет держать в актуальном состоянии данные на сайте экстранета и MS Outlook одновременно, например, личные контакты. Попробуйте сделать это прямо сейчас самостоятельно и вы прочувствуйте, насколько удобнее стало работать. Чем именно? Допустим, находясь на борту самолета, вы продумали и запланировали множество всяческих встреч, задач и мероприятий для компании. Теперь воспользуйтесь этой самой возможностью <b>двусторонней синхронизации календарей с MS Outlook</b> - и ваши наработки отразятся в календаре на сайте экстранета! При этом <b>никакого ручного дублирования информации</b>, а способы работы - привычные и &laquo;старые&raquo;. Быстро, удобно и очень эффективно! <a name="useful"></a>
<h2>Начните с синхронизации пользователей</h2>

<p>Чтобы ставить кому-то задачи (и получать от кого-то задачи) надо этих &quot;кого-то&quot; добавить в ваш MS Outlook. Это сделать очень просто: на странице <b>Поиск контакта</b> или <b>Сотрудники</b> сайта нажмите на кнопку <b>Outlook</b> и все произойдет автоматически, вам только надо будет соглашаться в паре всплывающих окон. Ну и немного подождать, если контактов (или сотрудников в компании) много, пока все данные загрузятся...</p>
После этого список ваших сотрудников появится в календаре:
<div align="center">
  <br />

  <table style="BORDER-COLLAPSE: collapse" border="0" cellspacing="1" cellpadding="1">
    <tbody>
      <tr><td>
          <div align="center"><a href="javascript:ShowImg('/extranet/images/docs/main.png', 1039, 705, 'Добавленные сотрудники')"><img title="Добавленные сотрудники" border="0" alt="Добавленные сотрудники" src="/extranet/images/docs/main_sm.png" width="500" height="339" /> </a>
            <br />
          </div>

          <div align="center"><i>Добавленные сотрудники</i>
            <br />
          </div>
        </td></tr>
    </tbody>
  </table>
</div>

<p>Вот только удаление контактов или сотрудников из MS Outlook не приведет к удалению их с сайта: это привилегия Администратора сайта.</p>

<h2><a name="my_kalendar"></a>Синхронизация календарей с MS Outlook</h2>
Вы можете синхронизировать с календарями MS Outlook <b>любые календари</b> на сайте: свои персональные, календари других пользователей сайта или общие календари компании. Попробуйте сделать это прямо сейчас! Перейти на страницу с одним из календарей, выбрать из меню действий <b>«Соединить с Outlook»</b> и запустить процесс синхронизации!
<br />

<br />

<div align="center"><img border="0" src="/extranet/images/docs/go_to.png" width="302" height="244" />
  <br />
<i>Соединить с Outlook!</i></div>

<br />
Не обращайте особого внимания на сообщения MS Outlook и просто соглашайтесь с ними, поскольку они в большинстве своем информационного характера. К примеру, появится на экране вопрос-предупреждение: <b>«Подключить к Outlook папку &quot;SharePoint&quot; Календарь?»</b> - смело жмите кнопку <b>«Да»</b>. Почему? Да потому что интеграция эта выполнена в полном соответствии со спецификацией корпорации Microsoft и ни каких проблем быть не может!
<br />

<br />

<div align="center"><img src="/extranet/images/docs/step1.png" width="482" height="202" />
  <br />
</div>

<br />
В принципе, можно не спешить, а нажать в этом окне <b>«Дополнительно...»</b>, где слегка описать календарь.
<br />

<div align="center">
  <br />

  <table style="BORDER-COLLAPSE: collapse" border="0" cellspacing="1" cellpadding="1">
    <tbody>
      <tr><td>
          <div align="center"><a href="javascript:ShowImg('/extranet/images/docs/step2.png', 518, 448, 'Описание календаря')"><img title="Описание календаря" border="0" alt="Описание календаря" src="/extranet/images/docs/step2_sm.png" width="299" height="259" /> </a>
            <br />
          </div>

          <div align="center"><i>Описание календаря</i>
            <br />
          </div>
        </td></tr>
    </tbody>
  </table>
</div>

<br />
<a name="company_calendar"></a>Что в результате? В вашем Outlook'е появится новый, <b>уже заполненный календарь</b>, на сетке которого отражены все события! Чем это удобно? Допустим, вы долгое время отсутствовали, были в командировке, и за это время в рамках сайта запланировано множество всяких мероприятий. Не отставайте от жизни - подключите и синхронизируйте нужные календари со своим Outlook'ом, - и всегда будете в гуще событий.
<br />

<div align="center">
  <br />

  <table style="BORDER-COLLAPSE: collapse" border="0" cellspacing="1" cellpadding="1">
    <tbody>
      <tr><td>
          <div align="center"><a href="javascript:ShowImg('/extranet/images/docs/calendar1.png', 550, 434, 'Календарь добавился в Outlook!')"><img title="Календарь добавился в Outlook!" border="0" alt="Календарь добавился в Outlook!" src="/extranet/images/docs/calendar1_sm.png" width="300" height="237" /> </a>
            <br />
          </div>

          <div align="center"><i>Календарь добавился в Outlook!</i>
            <br />
          </div>
        </td></tr>
    </tbody>
  </table>
</div>

<br />
<a name="kalendars"></a>Теперь <b>подключите</b> точно так же, один за другим, <b>все нужные вам календари</b> на сайте. Отобразите эти календари на одной сетке, и календарь в вашем Outlook'е будет выглядеть в точности так, как календарь на сайте экстранета!
<br />

<br />

<div align="center">
  <table style="BORDER-COLLAPSE: collapse" border="0" cellspacing="1" cellpadding="1">
    <tbody>
      <tr><td>
          <div align="center"><a href="javascript:ShowImg('/extranet/images/docs/calendar2.png', 550, 434, 'Экспортированные календари в MS Outlook')"><img title="Экспортированные календари в MS Outlook" border="0" alt="Экспортированные календари в MS Outlook" src="/extranet/images/docs/calendar2_sm.png" width="300" height="237" /> </a>
            <br />
          </div>

          <div align="center"><i>Календари в MS Outlook на одной сетке</i>
            <br />
          </div>
        </td></tr>
    </tbody>
  </table>
</div>

<br />

<h2>Как все работает? Добавим событие!</h2>
Как работает двусторонняя интеграция? <b>Добавьте новое событие в календарь MS Outlook, и то это событие автоматически появится в календаре на сайте экстранета!</b> Сделайте это сейчас:
<br />

<ul>
  <li>в сетке календаря выберите день события;
    <br />
  </li>

  <li>двойным щелчком откройте окно добавления нового события; </li>

  <li>заполните поля Тема, Начало, Конец и Описание; </li>

  <li>нажмите кнопку Сохранить и закрыть; </li>

  <li>новое событие <b>добавится в календарь </b>MS Outlook. </li>
</ul>

<div align="center">
  <table cellspacing="1" cellpadding="1" border="0" style="border-collapse: collapse;"> 
    <tbody> 
      <tr><td> 
          <div align="center"><a href="javascript:ShowImg('/extranet/images/docs/event.png', 600, 378, 'Экспортированные календари в MS Outlook')"><img width="300" height="189" border="0" src="/extranet/images/docs/event_sm.png" alt="Экспортированные календари в MS Outlook" title="Экспортированные календари в MS Outlook" /> </a> 
            <br />
           </div>
         
          <div align="center"><i>Календари в MS Outlook на одной сетке</i> 
            <br />
           </div>
         </td></tr>
     </tbody>
   </table>
</div>

<p class="a1"><a name="sinhr"></a>Для синхронизации с календарем сайта вам ничего делать не нужно - это произойдет автоматически, в соответствии с настроенной частотой проверки почты Outlook'ом. <b>Ход синхронизации</b> будет отображаться в нижнем правом углу программы.</p>

<p class="a1" align="center"><img src="/extranet/images/docs/status.png" width="370" height="26" /></p>
А вот то же самое событие <b>в календаре на сайте экстранета</b>!
<br />

<br />

<div align="center"><img src="/extranet/images/docs/event_portal.png" width="477" height="294" /></div>

<br />
Точно так же вы сможете <b>изменять и удалять события</b> - как календарях MS Outlook, так и в календарях на сайте экстранета. И изменения <b>двусторонне синхронизируются</b>! Теперь вы уже в курсе, что это значит: сразу же произойдет автоматическая синхронизация и событие изменится или удалится - в MS Outlook или на сайте.
<br />

<br />
<a name="useful1"></a>
<h2>Синхронизация личных задач</h2>
Теперь, не вдаваясь в подробности, попробуем синхронизировать задачи. Напомним, что и эта синхронизация произойдет без вашего участия.
<br />

<br />

<div align="center">
  <div align="center"><a href="javascript:ShowImg('/extranet/images/docs/tasks.png', 700, 442, 'Синхронизация личных задач')"><img title="Синхронизация личных задач" border="0" alt="Синхронизация личных задач" src="/extranet/images/docs/tasks_sm.png" width="400" height="252" /> </a>
    <br />
  </div>

  <div align="center"><i>Синхронизация личных задач</i>
    <br />
  </div>
</div>

<br />
<b>Сделайте все по инструкции:</b>
<br />

<ul>
  <li>синхронизируйте пользователей (кнопка Outlook на странице Поиск контактов или Сотрудники); </li>

  <li>введите запрошенный пароль пароль по AD (пароль доступа к сайту экстранета); </li>

  <li>список пользователей добавится в MS Outlook автоматически; </li>

  <li>поставьте задачу любому из пользователей средствами MS Outlook; </li>

  <li>MS Outlook свяжется с сайтом  и синхронизирует задачи. </li>
</ul>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
