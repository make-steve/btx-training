<?php
class MProject
{
    public function __construct()
    {

    }

    public function getByName($str)
    {
        if (!$str || is_null($str)) {
            return null;
        }

        global $DB;
        // $query = 'select * from m_project where TITLE like "%'.$DB->ForSQL($str).'%" limit 1';
        $query = 'select * from m_project where TITLE like "%'.$DB->ForSQL($str).'%"';
        return $DB->Query($query);
    }

    public function searchID($ID)
    {
        if (!$ID || is_null($ID)) {
            return null;
        }

        global $DB;
        $query = 'select * from m_project where ID like "%'.$DB->ForSQL($ID).'%"';
        return $DB->Query($query);
    }

    public function getByID($ID)
    {
        if (!$ID || is_null($ID)) {
            return null;
        }

        global $DB;
        $query = 'select * from m_project where ID='.$DB->ForSQL($ID);
        return $DB->Query($query);
    }

    public function isNew($ID)
    {
        if (!$ID || is_null($ID)) {
            return null;
        }

        global $DB;

        $query = 'select * from m_project mp join m_deal_project mdp on mdp.PROJECT_ID=mp.ID';
        $query .= ' where mp.ID="'.$DB->ForSQL($ID).'" and mdp.DEAL_ID IS NOT NULL';
        $result = $DB->Query($query);
        if ($row = $result->Fetch()) {
            return false;
            
        } else {
            return true;
        }

        return false;
    }

    public function getMainDeal($ID)
    {
        if (!$ID || is_null($ID)) {
            return null;
        }
        global $DB;

        $rsProject = $this->getByID($ID);
        if ($project = $rsProject->Fetch()) {
            $query = 'select * from b_crm_deal where ID="'.$DB->ForSQL($project['PRIMARY_DEAL']).'" limit 1';
            return $DB->Query($query);
        }
        return null;
    }

    public function company($ID)
    {
        if (!$ID || is_null($ID)) {
            return array();
        }
        global $DB;
        $result = $DB->Query('select * from m_project_company where PROJECT_ID="'.$DB->ForSQL($ID).'" limit 1');
        if ($row = $result->Fetch()) {
            return $row;
        }
        return array();
    }

    public function contact($ID)
    {
        if (!$ID || is_null($ID)) {
            return array();
        }
        global $DB;
        $result = $DB->Query('select * from m_project_contact where PROJECT_ID="'.$DB->ForSQL($ID).'" limit 1');
        if ($row = $result->Fetch()) {
            return $row;
        }
        return array();
    }
}
