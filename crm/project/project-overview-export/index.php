<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/publica/crm/deal/index.php");
$APPLICATION->SetTitle("Project Overview Export");
$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/main.interface.filter/templates/tabbed/style.css');

global $USER, $DB;
$isPM = false;

if($USER->isAdmin()) $isPM = true;
?>

<form name="filter_PM_GRID" action="/crm/project/project-overview-export/export.php" method="POST">

    <div class="crm-main-wrap-flat">
        <div id="flt_wrapper_pm_grid" class="bx-filter-wrap">
            <div class="bx-filter-wrap">
                <table class="bx-filter-main-table">
                    <tbody>
                        <tr>
                            <td class="bx-filter-main-table-cell">
                                <div class="bx-filter-tabs-block" id="filter-tabs"><span id="flt_tab_pm_grid_filter_default" class="bx-filter-tab bx-filter-tab-active">Filter</span>

                                    <span class="bx-filter-tabs-block-underlay"></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="bx-filter-main-table-cell">
                                <div id="flt_wrapper_pm_grid-block" class="bx-filter-content">
                                    <div id="flt_wrapper_pm_grid-inner" class="bx-filter-content-inner">
                                        <div class="bx-filter-content-table-wrap">
                                            <table class="bx-filter-content-table">
                                                <tbody>
                                                    <tr class="bx-filter-item-row" id="flt_field_pm_grid_DATE_INSERT" style="">
                                                        <td class="bx-filter-item-left">Hours Log Created:</td>
                                                        <td class="bx-filter-item-center">
                                                            <div class="bx-filter-alignment">
                                                                <div class=" bx-filter-box-sizing">
                                                                    <div class="bx-input-wrap bx-filter-calendar-inp bx-filter-calendar-first bx-filter-date-from" style="">
                                                                        <input type="text" class="bx-input bx-input-date calendarfrom" readonly="readonly" name="DATE_INSERT_from" value=""><span class="bx-calendar-icon calendarfrompicker"></span></div><span class="bx-filter-calendar-separate" style=""></span>
                                                                    <div class="bx-input-wrap bx-filter-calendar-inp bx-filter-calendar-first bx-filter-date-to" style="">
                                                                        <input type="text" class="bx-input bx-input-date calendarto" readonly="readonly" name="DATE_INSERT_to" value=""><span class="bx-calendar-icon calendartopicker"></span></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="bx-filter-item-right">

                                                        </td>
                                                    </tr>
                                                    <tr id="flt_field_delim_pm_grid_DATE_INSERT" style="">
                                                        <td class="delimiter" colspan="3">
                                                            <div class="empty"></div>
                                                        </td>
                                                    </tr>
                                                    <tr class="bx-filter-item-row pmfield" id="flt_field_pm_grid_ORIGINATOR_USER_ID" style="">
                                                        <td class="bx-filter-item-left">Projectleider:</td>
                                                        <td class="bx-filter-item-center">
                                                            <div class="bx-filter-alignment">
                                                                <div class=" bx-filter-box-sizing">
                                                                    <div class="bx-input-wrap">
                                                                        <?php

                                                                        $APPLICATION->IncludeComponent(
                                                                            "bitrix:intranet.user.selector", ".default", array(
                                                                                'MULTIPLE' => 'N',
                                                                                'NAME' => "ORIGINATOR_USER_ID",
                                                                                'INPUT_NAME' => "ORIGINATOR_USER_ID",
                                                                                'POPUP' => 'Y',
                                                                                'SITE_ID' => SITE_ID,
                                                                                "INPUT_VALUE" => $USER->GetID()
                                                                            ), null, array("HIDE_ICONS" => "Y")
                                                                        );

                                                                        ?>
                                                                        
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="bx-filter-item-row memberfield" id="flt_field_pm_grid_USER_ID" style="">
                                                        <td class="bx-filter-item-left"><?php echo "Members:";?></td>
                                                        <td class="bx-filter-item-center">
                                                            <div class="bx-filter-alignment">
                                                                <div class=" bx-filter-box-sizing">
                                                                    <div class="bx-input-wrap">
                                                                        <?php

                                                                        $arParams = array(
                                                                                'MULTIPLE' => "Y",
                                                                                'NAME' => "USER_ID",
                                                                                'INPUT_NAME' => "USER_ID",
                                                                                'POPUP' => 'Y',
                                                                                'SITE_ID' => SITE_ID,
                                                                            );

                                                                        $APPLICATION->IncludeComponent(
                                                                            'bitrix:intranet.user.selector',
                                                                            '',
                                                                            $arParams,
                                                                            null,
                                                                            array('HIDE_ICONS' => 'Y')
                                                                        );

                                                                        ?>
                                                                        
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>

                                                    </tr>
                                                    <tr id="flt_field_delim_pm_grid_USER_ID" style="">
                                                        <td class="delimiter" colspan="3">
                                                            <div class="empty"></div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="bx-filter-bottom-separate" style=""></div>
                                        <div class="bx-filter-bottom">
                                            <input class="exportproject" value="Export" name="set_filter" type="button" style="float: left;">
                                            <p style="float: left;font-weight: bold;color: green;">Note : To open this file use Microsoft Office Excel</p>
                                            <input name="export" value="Y" type="hidden" />
                                            <input name="isPM" value="<?php echo ($isPM ? "Y" : "N");?>" type="hidden" />
                                        </div>
                                    </div>
                                    <div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>


<script type="text/javascript">
$(document).ready(function() 
{
    $( ".calendarfrom" ).datepicker( {
        dateFormat: 'yy-mm-dd',
        onClose: function(dateText, inst) { 
            $(this).css('display', 'none !important');
        }
    });
    $( ".calendarto" ).datepicker( {
        dateFormat: 'yy-mm-dd',
        onClose: function(dateText, inst) { 
            $(this).css('display', 'none !important');
        }
    });

    $( ".calendarfrompicker" ).click(function() {
        $('.calendarfrom').datepicker("show");
    });

    $( ".calendartopicker" ).click(function() {
        $('.calendarto').datepicker("show");
    });

    $( ".exportproject" ).click(function() {
        $('form[name="filter_PM_GRID"]').submit();
    });

    <?php if(!$isPM) :?>
        $( ".pmfield .mli-layout input[type='text']" ).attr('readonly', true);
    <?php endif;?>
});
</script>
<style type="text/css">
    #ui-datepicker-div {
        z-index: 9999999 !important;
    }
    .mli-search-results {
        position: inherit !important;
    }


    <?php if(!$isPM) :?>
    .pmfield .bx-ius-layout .bx-ius-structure-link {
        display: none;
    }
    <?php endif;?>
</style>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>