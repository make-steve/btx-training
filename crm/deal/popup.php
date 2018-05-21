<div id="popup_content">
    <div class="popup_step1">
        <p class="content_text">Voeg nieuwe offerte toe aan bestaand project
        of start een nieuwe projectkaart</p>
        <div class="button_block project_select_block">
        <button type="button" id="project_select">VOEG TOE AAN BESTAAND PROJECT</button>
        </div>
        <hr>
        <div class="button_block">
            <button type="button" id="project_new">START NIEUW PROJECT</button>
            <div>
                <input type="text" name="project_title" placeholder="Voeg projectnaam toe" value="" class="popup_field">
            </div>
        </div>    
    </div>
    <div class="popup_step2" style="display: none;"> 
        <p class="content_text">Voeg het voorstel toe aan een project</p>
        <div class="project_input_block" style="background-color: #e5f0f6;padding: 15px 20px;">
            <p class="content_text" style="margin: 0;font-size: 12px;font-weight: bold;">VOEG TOE AAN BESTAAND PROJECT</p>
            <div>
                <input type="text" name="project_id" id="project_id_input" placeholder="Voer naam/projectnummer in" value="" class="popup_field">
            </div>
            <br>
            <button type="button" id="project_assign" style="text-align: left;
    padding-left: 20px;
    text-transform: capitalize;
    font-size: 13px;width: 95%;">+ SELECT PROJECT</button>
        </div>
        <br>
        <div class="button_block">
            <button type="button" id="back_step1" style="background-color: #fff;border: 2px solid #e6e9eb;">START NIEUW PROJECT</button>
        </div>  
    </div>
</div>