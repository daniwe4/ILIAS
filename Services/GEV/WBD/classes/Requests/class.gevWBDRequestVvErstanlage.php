<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* implementation of GEV WBD Request for Service VvErstanlage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
require_once("Services/GEV/WBD/classes/Requests/trait.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessVvErstanlage.php");
require_once("Services/GEV/WBD/classes/Error/class.gevWBDError.php");

class gevWBDRequestVvErstanlage extends WBDRequestVvErstanlage {
	use gevWBDRequest;

	protected $error_group;

	protected function __construct($data) {
		$this->error_group = gevWBDError::ERROR_GROUP_USER;

		$this->defineValuesToTranslate();
		$dic_errors = $this->translate($data["user_id"], $data["row_id"]);

		$this->address_type 		= new WBDData("AdressTyp", $this->translate_value["AdressTyp"]);
		$this->title 				= new WBDData("AnredeSchluessel", $this->translate_value["AnredeSchluessel"]);
		$this->wbd_agent_status 	= new WBDData("VermittlerStatus", $this->translate_value["VermittlerStatus"]);
		$this->wbd_type 			= new WBDData("TpKennzeichen", $this->translate_value["TpKennzeichen"]);

		$this->address_info 		= new WBDData("AdressBemerkung", $data["address_info"]);
		$this->auth_email 			= new WBDData("AuthentifizierungsEmail", $data["email"]);
		$this->auth_mobile_phone_nr = new WBDData("AuthentifizierungsTelefonnummer", $data["mobile_phone_nr"]);
		$this->info_via_mail 		= new WBDData("BenachrichtigungPerEmail", $data["info_via_mail"]);
		$this->send_data 			= new WBDData("DatenuebermittlungsKennzeichen", $data["send_data"]);
		$this->data_secure 			= new WBDData("DatenschutzKennzeichen", $data["data_secure"]);
		$normal_email = ($data['wbd_email'] != '') ? $data['wbd_email'] : $data['email'];
		$this->email 				= new WBDData("Emailadresse", $normal_email);
		$this->birthday 			= new WBDData("Geburtsdatum", $data["birthday"]);
		$this->house_number			= new WBDData("Hausnummer", $data["house_number"]);
		$this->internal_agent_id 	= new WBDData("InterneVermittlerId", $data["user_id"]);
		$this->country 				= new WBDData("IsoLaendercode", $data["country"]);
		$this->lastname 			= new WBDData("Name", $data["lastname"]);
		$this->mobile_phone_nr 		= new WBDData("Mobilfunknummer", $data["mobile_phone_nr"]);
		$this->city 				= new WBDData("Ort", $data["city"]);
		$this->zipcode 				= new WBDData("Postleitzahl", $data["zipcode"]);
		$this->street 				= new WBDData("Strasse", $data["street"]);
		$this->phone_nr 			= new WBDData("Telefonnummer", ($data["phone_nr"] != "") ? $data["phone_nr"] : $data["mobile_phone_nr"]);
		$this->degree 				= new WBDData("Titel", $data["degree"]);
		$this->okz 					= new WBDData("VermittlungsTaetigkeit",$data["okz"]);
		$this->firstname 			= new WBDData("VorName", $data["VorName"]);

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
		$this->next_wbd_action = $data["next_wbd_action"];

		$check_errors = $this->checkData();
		$errors = $check_errors + $dic_errors;

		if(!empty($errors)) {
			throw new myLogicException("gevWBDRequestVvErstanlage::__construct:checkData failed", 0, null, $errors);
		}
	}

	public static function getInstance(array $data) {
		$data = self::polishInternalData($data);

		try {
			return new gevWBDRequestVvErstanlage($data);
		} catch(myLogicException $e) {
			return $e->options();
		}
	}

	/**
	* checked all given data
	*
	* @return array
	*/
	protected function checkData() {
		$result = $this->checkSzenarios();
		return $result;
	}

	/**
	* creates the success object VvErstanlage
	*
	* @throws LogicException
	* 
	* @return boolean
	*/
	public function createWBDSuccess($response) {
		$this->wbd_success = new gevWBDSuccessVvErstanlage($response, (int)$this->row_id, $this->next_wbd_action);
	}

	/**
	* gets the user_id
	*
	* @return integer
	*/
	public function userId() {
		return $this->user_id;
	}

	/**
	* gets the row_id
	*
	* @return integer
	*/
	public function rowId() {
		return $this->row_id;
	}

	/**
	* gets a new WBD Error
	*
	* @return integer
	*/
	public function createWBDError($message) {
		$reason = $this->parseReason($message);
		$this->wbd_error = self::createError($reason, $this->error_group, $this->user_id, $this->row_id);
	}

	protected function defineValuesToTranslate() {
		$this->translate_value = array("AdressTyp" => array("field" => "address_type", "group" => gevWBDDictionary::SEARCH_IN_ADDRESS_TYPE)
							   , "AnredeSchluessel" => array("field" => "gender", "group" => gevWBDDictionary::SEARCH_IN_GENDER)
							   , "VermittlerStatus" => array("field" => "wbd_agent_status", "group" => gevWBDDictionary::SEARCH_IN_AGENT_STATUS)
							   , "TpKennzeichen" => array("field" => "wbd_type", "group" => gevWBDDictionary::SEARCH_IN_WBD_TYPE)
			);
	}
}