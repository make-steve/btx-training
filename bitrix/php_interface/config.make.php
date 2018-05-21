<?php
/**
 * contains all site referenced constant values and custom field IDs
 */

/**
 * custom fields
 */


// project 
define("CUF_D_KOSTEN_DERDEN", 'UF_CRM_1412332756'); // Kosten derden
define("CUF_D_DATUM_OPDRACHT", 'UF_CRM_1413808339'); // Datum in opdracht
define("CUF_D_IN_OPDRACHT", 'UF_CRM_1415611536'); // In opdracht
define("CUF_D_ACTIE_DATUM", 'UF_CRM_1416911779'); // Actie datum:
define("CUF_D_MARKTSEGMENT", 'UF_CRM_1446554736'); // Marktsegment
define("CUF_D_PROJECT_NUM", 'UF_CRM_1451905527'); // Projectnummer
define("CUF_D_KIND_NUM", 'UF_CRM_1461673755'); // Kindnummer
define("CUF_D_DEBTOR_NUM", 'UF_CRM_1461673774'); // Debiteurennummer
define("CUF_D_KOSTEN_LIFT", 'UF_CRM_1475155427'); // Kosten derden LIFT
define("CUF_D_TECHNOLOGY", 'UF_CRM_1493724413'); // Technologie
define("CUF_D_PROJECT_NAME", 'UF_CRM_1511431371'); // Projectnaam
define("CUF_D_KLANT_NAME", 'UF_CRM_1511431411'); // Klant Naam
define("CUF_D_PROJECT_ID", 'UF_CRM_1511431462'); // project ID
define("CUF_D_KOSTEN_UREN_IF", 'UF_CRM_1511431510'); // Kosten IF
define("CUF_D_ACQUISITIE", 'UF_CRM_1511431537'); // Aquisitie Kosten
// define("CUF_D_RENDEMENT", 'UF_CRM_1511431560'); // inkomsten (due to recent namechange); rendement is now field OPPORTUNITY
define("CUF_D_INKOMSTEN", 'UF_CRM_1511431560'); // inkomsten (due to recent namechange); rendement is now field OPPORTUNITY


// invoice
define("CUF_I_KOSTEN_DERDEN", 'UF_CRM_56D69365EB3E7'); // Kosten derden
define("CUF_I_DATUM_OPDRACHT", 'UF_CRM_56D6936617769'); // Datum in opdracht
define("CUF_I_IN_OPDRACHT", 'UF_CRM_56D69366257F0'); // In opdracht
define("CUF_I_ACTIE_OPDRACHT", 'UF_CRM_56D69366320F9'); // Actie datum:
define("CUF_I_MARKTSEGMENT", 'UF_CRM_56D693663EF21'); // Marktsegment
define("CUF_I_KIND1", 'UF_CRM_56D693664E1A2'); // KIND1


// company
define('CUF_CM_OMZET', 'UF_CRM_1415864752'); // Omzet gemiddeld
define('CUF_CM_NEWFIELD', 'UF_CRM_1415865817'); // New field 1
define('CUF_CM_IF_KLANTNR', 'UF_CRM_1421058059'); // IF Klantnr.
define('CUF_CM_HOW_CUSTOMER', 'UF_CRM_1470063180'); // Hoe heeft de klant ons gevonden? via:
define('CUF_CM_KLANTWAARDERING', 'UF_CRM_1474961849'); // Klantwaardering
define('CUF_CM_VESTIGING', 'UF_CRM_1475157926'); // Vestiging (indien van toepassing)
define('CUF_CM_BEZOEKADRES', 'UF_CRM_1511437015'); // Bezoekadres
define('CUF_CM_POSTCODE', 'UF_CRM_1511437033'); // postcode
define('CUF_CM_PLAATS', 'UF_CRM_1511437053'); // plaats
define('CUF_CM_FACTUUR_BEDRIJF', 'UF_CRM_1511437080'); // Factuurbedrijf
define('CUF_CM_FACTUUR_ADRES', 'UF_CRM_1511437095'); // Factuuradres
define('CUF_CM_TAV', 'UF_CRM_1511437111'); // T.a.v.
define('CUF_CM_CLIENTID_EXACT', 'UF_CRM_1511437130'); // Client ID Exact


// contact
define('CUF_CN_TECHNOLOGY', 'UF_CRM_1511437958'); // Technologie
define('CUF_CN_PRODUCTGROUP', 'UF_CRM_1511437970'); // productgroep
define('CUF_CN_POSTADRES', 'UF_CRM_1511437982'); // postadres
define('CUF_CN_POSTCODE', 'UF_CRM_1511437995'); // postcode
define('CUF_CN_PLAATS', 'UF_CRM_1511438007'); // plaats


// tasks
define('CUF_T_LOCKED', 'UF_AUTO_713286985141'); // locked
define('CUF_T_INKOMSTEN', 'UF_AUTO_276127427586'); // inkomsten
define('CUF_T_KOSTEN_DERDEN', 'UF_AUTO_834260910111'); // kosten derden
define('CUF_T_KOSTEN_LIFT', 'UF_AUTO_347562553598'); // kosten lift
define('CUF_T_KOSTEN_UREN_IF', 'UF_AUTO_775103888970'); // kosten uren if
define('CUF_T_KOSTEN_UREN_BREAKDOWN_ID', 'UF_AUTO_371316967810'); // Kosten uren breakdown ID
define('CUF_T_KOSTEN_UREN_BREAKDOWN', 'UF_AUTO_371316967810_DROPDOWN'); // Kosten uren breakdown
define('CUF_T_RENDEMENT', 'UF_AUTO_197833829978'); // rendement
define('CUF_T_TECHNOLOGY', 'UF_AUTO_371316967807'); // technologie
define('CUF_T_PRODUCTGROUP', 'UF_AUTO_371316967808'); // productgroep
define('CUF_T_FACTUUR_MOMENTEN', 'UF_AUTO_371316967809'); // factuur momenteen
define('CUF_T_REF_CLIENT', 'UF_AUTO_192582141226'); // referentie klatn
define('CUF_T_ADDTL_INFO', 'UF_AUTO_326988963328'); // additional info
define('CUF_T_COMPANY', 'UF_AUTO_192582141227'); // task company
define('CUF_T_WBSO', 'UF_AUTO_364252479940'); // task wbo
define('CUF_T_TYPE', 'UF_AUTO_741430793433'); // task type
define('CUF_T_COST_ACQUISITION', 'UF_AUTO_132407273723 '); // acquisition cost


// event template id
define("CLIENT_MISSING_ID_TEMPLATE", 205);
