<?php

class mod_deal {

	private $id;
	private $dealUf = array(
		'REVENUE' => 'UF_CRM_1505972753',
		'TECHNOLOGY' => 'UF_CRM_1493724413',
		'ACQUISITION_COSTS' => 'UF_CRM_1505972722'
	);
	private $dealUFValue;
	private $_USER_FIELD_MANAGER;

	function __construct($dealId) {
		
		global $USER_FIELD_MANAGER;

		$this->id = $dealId;
		$this->_USER_FIELD_MANAGER = $USER_FIELD_MANAGER;
	}

	function getDealInfo() {

		$id = $this->id;
		$rsDeal = CCrmDeal::GetByID(intval($id));
		$this->dealUFValue = $this->_USER_FIELD_MANAGER->GetUserFields("CRM_DEAL", $id, $langID);
		return $rsDeal;
	}

	function getDealUfValues($uid) {

		$ufid = $this->dealUf[$uid];
        $arUfFields = $this->dealUFValue;

        return ($ufid != "") ? $arUfFields[$ufid] : $arUfFields;
	}

	function getResponsible($uid) {

		global $USER;

		$arUserResult = array();
		$rsUser = $USER->GetByID($uid);
		if($arUser = $rsUser->fetch()) {

			$arUserResult = array(
				'ID' => $arUser['ID'],
				'NAME' => $arUser['NAME'],
				'LAST_NAME' => $arUser['LAST_NAME'],
				'SECOND_NAME' => $arUser['SECOND_NAME'],
				'LOGIN' => $arUser['LOGIN'],
				'WORK_POSITION' => $arUser['WORK_POSITION'],
				'PERSONAL_PHOTO' => $arUser['PERSONAL_PHOTO'],
				'PERSONAL_GENDER' => $arUser['PERSONAL_GENDER'],
				'IS_EXTRANET_USER' => '',
				'IS_EMAIL_USER' => '',
				'IS_NETWORK_USER' => '',
			);
		}

		return $arUserResult;
	}

    public function isClosed()
    {
        $deal = $this->getDealInfo();
        return 'y'==strtolower($deal['CLOSED']);
    }
}
