<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilActions
{
    const F_NAME = "name";
    const F_RATING = "rating";
    const F_INFO = "info";
    const F_ADDRESS1 = "address1";
    const F_COUNTRY = "country";
    const F_ADDRESS2 = "address2";
    const F_POSTCODE = "postcode";
    const F_CITY = "city";
    const F_HOMEPAGE = "homepage";
    const F_INTERNAL_CONTACT = "internalContact";
    const F_CONTACT = "contact";
    const F_PHONE = "phone";
    const F_FAX = "fax";
    const F_EMAIL = "email";
    const F_GENERAL_AGREEMENT = "generalAgreement";
    const F_TERMS = "terms";
    const F_VALUTA = "valuta";
    const F_TAGS = "tags";

    const F_TAGS_TAGS = "tags";

    const F_TRAINER_FIRSTNAME = "firstname";
    const F_TRAINER_LASTNAME = "lastname";
    const F_TRAINER_EMAIL = "email";
    const F_TRAINER_PHONE = "phone";
    const F_TRAINER_FEE = "fee";
    const F_TRAINER_ACTIVE = "active";
    const F_TRAINER_PROVIDER = "provider";
    const F_TRAINER_TITLE = "trainer_title";
    const F_TRAINER_SALUTATION = "salutation";
    const F_TRAINER_MOBILE_NUMBER = "mobile_number";
    const F_TRAINER_EXTRA_INFOS = "extra_infos";
    const TRAINER_FIRSTNAME_LENGTH = 2;
    const TRAINER_LASTNAME_LENGTH = 2;

    const MAIL_REGEXP = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    const HTTP_REGEXP = "/^(https:\/\/)|(http:\/\/)[\w]+/";

    const NAME_LENGTH = 5;
    const ADDRESS1_LENGTH = 4;
    const CITY_LENGTH = 2;

    /**
     * @var \CaT\Plugins\TrainingProvider\Provider\DB
     */
    protected $provider_db;

    /**
     * @var \CaT\Plugins\TrainingProvider\Trainer\DB
     */
    protected $trainer_db;

    /**
     * @var \CaT\Plugins\TrainingProvider\Tags\DB
     */
    protected $tags_db;

    /**
     * @var \CaT\Plugins\TrainingProvider\ProviderAssignment\DB
     */
    protected $assign_db;

    public function __construct(
        Provider\DB $provider_db,
        Trainer\DB $trainer_db,
        Tags\DB $tags_db,
        ProviderAssignment\DB $assign_db,
        $app_event_handler
    ) {
        $this->provider_db = $provider_db;
        $this->trainer_db = $trainer_db;
        $this->tags_db = $tags_db;
        $this->assign_db = $assign_db;
        $this->app_event_handler = $app_event_handler;
    }

    /**********
     * Provider
     *
     * TODO:
     * Move this to ilActions just for provider
     *********/
    /**
     * Create a new provider from post values
     *
     * @param string 		$name
     * @param float 		$rating
     * @param string 		$info
     * @param string 		$address1
     * @param string 		$country
     * @param string 		$address2
     * @param string 		$postcode
     * @param string 		$city
     * @param string 		$homepage
     * @param string 		$internal_contact
     * @param string 		$contact
     * @param string 		$phone
     * @param string 		$fax
     * @param string 		$email
     * @param string 		$general_agreement
     * @param string 		$terms
     * @param string 		$valuta
     * @param int[] 		$tags
     */
    public function createProvider($name, $rating, $info, $address1, $country, $address2, $postcode, $city, $homepage, $internal_contact, $contact, $phone, $fax, $email, $general_agreement, $terms, $valuta, $tags)
    {
        $provider = $this->provider_db->create($name, $rating, $info, $address1, $country, $address2, $postcode, $city, $homepage, $internal_contact, $contact, $phone, $fax, $email, $general_agreement, $terms, $valuta, $tags);

        $this->tags_db->allocateTags($provider->getId(), $provider->getTags());
    }

    /**
     * Get raw data for every provider
     *
     * @param int[] | [] 	$filtered_tags
     *
     * @return array<mixed[]>
     */
    public function getProviderOverviewData(array $filtered_tags = array())
    {
        return $this->provider_db->getProviderOverviewData($filtered_tags);
    }

    /**
     * Get values for provider
     *
     * @param int 		$provider_id
     *
     * @return mixed[]
     */
    public function getProviderValues($provider_id)
    {
        $provider = $this->provider_db->select($provider_id);

        $values = array();

        $values[self::F_NAME] = $provider->getName();
        $values[self::F_RATING] = $provider->getRating();
        $values[self::F_GENERAL_AGREEMENT] = $provider->getGeneralAgreement();
        $values[self::F_INFO] = $provider->getInfo();
        $values[self::F_ADDRESS1] = $provider->getAddress1();
        $values[self::F_COUNTRY] = $provider->getCountry();
        $values[self::F_ADDRESS2] = $provider->getAddress2();
        $values[self::F_POSTCODE] = $provider->getPostcode();
        $values[self::F_CITY] = $provider->getCity();
        $values[self::F_HOMEPAGE] = $provider->getHomepage();
        $values[self::F_INTERNAL_CONTACT] = $provider->getInternalContact();
        $values[self::F_CONTACT] = $provider->getContact();
        $values[self::F_PHONE] = $provider->getPhone();
        $values[self::F_FAX] = $provider->getFax();
        $values[self::F_EMAIL] = $provider->getEmail();
        $values[self::F_TERMS] = $provider->getTerms();
        $values[self::F_VALUTA] = $provider->getValuta();
        $values[self::F_TAGS] = $provider->getTags();

        return $values;
    }

    /**
     * Get the provider object for id
     *
     * @param int 	$provider_id
     *
     * @return \CaT\Plugins\TrainingProvider\Provider\Provider
     */
    public function getProvider($provider_id)
    {
        assert('is_int($provider_id)');
        return $this->provider_db->select($provider_id);
    }

    /**
     * Get all provider
     *
     * @param string 	$order_column
     * @param string 	$order_direction
     *
     * @return Provider\Provider[]
     */
    public function getAllProviders($order_column, $order_direction)
    {
        return $this->provider_db->getAllProviders($order_column, $order_direction);
    }

    /**
     * Update the edited provider
     *
     * @param int 		$provider_id
     * @param string[]	$post
     */
    public function updateProvider($provider_id, $post)
    {
        $name = $post[self::F_NAME];

        if (isset($post[self::F_RATING]) || trim($post[self::F_RATING]) != "") {
            $rating = floatval(trim($post[self::F_RATING]));
        } else {
            $rating = 0.0;
        }

        if (isset($post[self::F_GENERAL_AGREEMENT]) && $post[self::F_GENERAL_AGREEMENT] == "1") {
            $general_agreement = true;
        } else {
            $general_agreement = false;
        }

        $info = trim($post[self::F_INFO]);
        $address1 = trim($post[self::F_ADDRESS1]);
        $country = trim($post[self::F_COUNTRY]);
        $address2 = trim($post[self::F_ADDRESS2]);
        $postcode = trim($post[self::F_POSTCODE]);
        $city = trim($post[self::F_CITY]);
        $homepage = trim($post[self::F_HOMEPAGE]);
        $internal_contact = trim($post[self::F_INTERNAL_CONTACT]);
        $contact = trim($post[self::F_CONTACT]);
        $phone = trim($post[self::F_PHONE]);
        $fax = trim($post[self::F_FAX]);
        $email = trim($post[self::F_EMAIL]);
        $terms = trim($post[self::F_TERMS]);
        $valuta = trim($post[self::F_VALUTA]);
        $tags = $post[self::F_TAGS];

        $provider = new Provider\Provider($provider_id, $name, $rating, $info, $address1, $country, $address2, $postcode, $city, $homepage, $internal_contact, $contact, $phone, $fax, $email, $general_agreement, $terms, $valuta, array(), $tags);

        $this->provider_db->update($provider);
        $this->tags_db->deleteAllocationByProviderId($provider_id);
        $this->tags_db->allocateTags($provider_id, $provider->getTags());
    }

    /**
     * Remove provider
     *
     * @param int 		$id
     */
    public function removeProvider($id)
    {
        $this->provider_db->delete($id);
    }

    /**
     * Get provider options
     *
     * @return array<int, mixed>
     */
    public function getProviderOptions()
    {
        return $this->provider_db->getProviderOptions();
    }

    /**
     * Get Provider name is existing
     *
     * @param string 	$new_provider_name
     *
     * @return bool
     */
    public function providerNameExist($new_provider_name)
    {
        assert('is_string($new_provider_name)');
        return $this->provider_db->providerNameExist($new_provider_name);
    }

    /**
     * Get the current name of provider
     *
     * @param int 		$id
     *
     * @return string 	$current_name
     */
    public function getCurrentProviderName($id)
    {
        assert('is_int($id)');
        return $this->provider_db->getCurrentProviderName($id);
    }

    /**********
     * Tags
     *
     * TODO:
     * Move this to ilActions just for tags
     *********/
    /**
     * Get available tags raw
     *
     * @return mixed[]
     */
    public function getTagsRaw()
    {
        return $this->tags_db->getTagsRaw();
    }

    /**
     * Save or updates the tags
     *
     * @param array<mixed[]>
     */
    public function saveTags(array $tags)
    {
        foreach ($tags as $key => $tag) {
            if (($tag["name"] === null || $tag["name"] == "") && ($tag["color"] === null || $tag["color"] == "")) {
                continue;
            }

            if (isset($tag["id"]) && $tag["id"] != "" && $tag["id"] !== null) {
                $this->updateTag($tag);
            } else {
                $tags[$key]["id"] = $this->saveTag($tag);
            }
        }

        $exist_tags = $this->getTagsRaw();
        $exist_tags = array_map(function ($tag) {
            return $tag["id"];
        }, $exist_tags);

        $tags = array_map(function ($tag) {
            return $tag["id"];
        }, $tags);

        $deleted_tags = array_diff($exist_tags, $tags);

        foreach ($deleted_tags as $key => $tag_id) {
            $this->tags_db->deallocation($tag_id);
            $this->tags_db->delete($tag_id);
        }
    }

    /**
     * Save a new tag
     *
     * @param mixed[] 		$tag
     */
    protected function saveTag($tag)
    {
        $tag = $this->tags_db->create($tag["name"], $tag["color"]);
        return $tag->getId();
    }

    /**
     * Update existing tag
     *
     * @param mixed[]		$tag
     */
    protected function updateTag($tag)
    {
        $tag = new Tags\Tag((int) $tag["id"], $tag["name"], $tag["color"]);
        $this->tags_db->update($tag);
    }

    /**
     * Delete all tag allocations by provider
     *
     * @param int 		$provider_id
     */
    public function deallocateTagsByProviderId($provider_id)
    {
        $this->tags_db->deleteAllocationByProviderId($provider_id);
    }

    /**
     * Get all assigned tags raw
     *
     * @return string[]
     */
    public function getAssignedTagsRaw()
    {
        return $this->tags_db->getAssignedTagsRaw();
    }

    /**********
     * Trainer
     *
     * TODO:
     * Move this to ilActions just for trainer
     *********/
    /**
     * Create new trainer from post values
     *
     * @param string 			$title
     * @param string 			$salutation
     * @param string 			$firstname
     * @param string 			$lastname
     * @param null | int		$provider_id
     * @param string 			$email
     * @param string 			$phone
     * @param string 			$mobile_number
     * @param null | float		$fee
     * @param null | string		$extra_infos
     * @param bool 				$active
     */
    public function createTrainer($title, $salutation, $firstname, $lastname, $provider, $email, $phone, $mobile_number, $fee, $extra_infos, $active)
    {
        $this->trainer_db->create($title, $salutation, $firstname, $lastname, $provider, $email, $phone, $mobile_number, $fee, $extra_infos, $active);
    }

    /**
     * Get all trainer in raw format
     *
     * @param int | null	$provider_id
     * @param int[] | null	$active_filter
     *
     * @return array<mixed[]>
     */
    public function getTrainersRaw($provider_id, array $active_filter = array())
    {
        return $this->trainer_db->getTrainersRaw($provider_id, $active_filter);
    }

    /**
     * Remove trainer
     *
     * @param int 		$id
     */
    public function removeTrainer($id)
    {
        assert('is_int($id)');
        $this->trainer_db->delete($id);
    }

    /**
     * Get trainer values
     *
     * @param int 		$id
     *
     * @return mixed[]
     */
    public function getTrainerValues($id)
    {
        $trainer = $this->trainer_db->select($id);

        $values = array();

        $values[self::F_TRAINER_TITLE] = $trainer->getTitle();
        $values[self::F_TRAINER_SALUTATION] = $trainer->getSalutation();
        $values[self::F_TRAINER_FIRSTNAME] = $trainer->getFirstname();
        $values[self::F_TRAINER_LASTNAME] = $trainer->getLastname();
        $values[self::F_TRAINER_PROVIDER] = $trainer->getProviderId();
        $values[self::F_TRAINER_PHONE] = $trainer->getPhone();
        $values[self::F_TRAINER_MOBILE_NUMBER] = $trainer->getMobileNumber();
        $values[self::F_TRAINER_EMAIL] = $trainer->getEmail();
        $values[self::F_TRAINER_ACTIVE] = $trainer->getActive();
        $values[self::F_TRAINER_FEE] = $trainer->getFee();
        $values[self::F_TRAINER_EXTRA_INFOS] = $trainer->getExtraInfos();

        return $values;
    }

    /**
     * TODO:
     * Split this function. Actions on $_POST should be done in GUI
     *
     * Updates a trainer
     *
     * @param int 		$id
     * @param string[] 	$post
     */
    public function updateTrainer($id, $post)
    {
        if (isset($post[self::F_TRAINER_FEE]) || trim($post[self::F_TRAINER_FEE]) != "") {
            $fee = trim($post[self::F_TRAINER_FEE]);
            $pos = strrpos($fee, ",");
            if ($pos !== false) {
                $fee = substr_replace($fee, ".", $pos, strlen(","));
                $fee = (float) $fee;
            }
        } else {
            $fee = 0.0;
        }

        $title = trim($post[self::F_TRAINER_TITLE]);
        $salutation = trim($post[self::F_TRAINER_SALUTATION]);
        $firstname = trim($post[self::F_TRAINER_FIRSTNAME]);
        $lastname = trim($post[self::F_TRAINER_LASTNAME]);
        $email = trim($post[self::F_TRAINER_EMAIL]);
        $phone = trim($post[self::F_TRAINER_PHONE]);
        $mobile_number = trim($post[self::F_TRAINER_MOBILE_NUMBER]);
        $active = (bool) trim($post[self::F_TRAINER_ACTIVE]);
        $provider = (int) trim($post[self::F_TRAINER_PROVIDER]);
        $extra_infos = trim($post[self::F_TRAINER_EXTRA_INFOS]);

        $trainer = new Trainer\Trainer($id, $title, $salutation, $firstname, $lastname, $provider, $email, $phone, $mobile_number, $fee, $extra_infos, $active);
        $this->trainer_db->update($trainer);
    }

    /**
     * Removes all assigned trainer
     *
     * @param int 		$provider_id
     */
    public function removeTrainerByProviderId($id)
    {
        $this->trainer_db->deleteByProvider($id);
    }

    /**********
     * Assignments
     *********/
    /**
     * Create a new assignment
     *
     * @param int 		$crs_id
     * @param int 		$provider_id
     */
    public function createListProviderAssignment($crs_id, $provider_id)
    {
        $assignment = $this->assign_db->createListProviderAssignment($crs_id, $provider_id);
    }

    /**
     * Create a new assignment
     *
     * @param int 		$crs_id
     * @param string 	$text
     */
    public function createCustomProviderAssignment($crs_id, $text)
    {
        $assignment = $this->assign_db->createCustomProviderAssignment($crs_id, $text);
    }

    /**
     * update an exisiting assignment
     *
     * @param ProviderAssignment 		$provider_assignment
     */
    public function updateAssignment(ProviderAssignment\ProviderAssignment $provider_assignment)
    {
        $assignment = $this->assign_db->update($provider_assignment);
    }

    /**
     * Remove all provider assignments for course
     *
     * @param int 	$crs_id
     */
    public function removeAssignment($crs_id)
    {
        $this->assign_db->delete($crs_id);
    }

    /**
     * get assignment for course id
     *
     * @param int 	$crs_id
     *
     * @return ProviderAssignment\ProviderAssignment | false
     */
    public function getAssignment($crs_id)
    {
        return $this->assign_db->select($crs_id);
    }

    /**
     * Return all crs obj ids where venue is used
     *
     * @param int 	$id
     *
     * @return int[]
     */
    public function getAffectedCrsObjIds($id)
    {
        return $this->assign_db->getAffectedCrsObjIds($id);
    }

    /**
     * Throws an event after venue is updated or deleted
     *
     * @param int[] 	$crs_ids
     *
     * @return void
     */
    public function throwEvent($crs_ids)
    {
        $e = array();
        $e["crs_obj_ids"] = $crs_ids;
        $this->app_event_handler->raise("Plugin/TrainingProvider", "updateTrainingProviderStatic", $e);
    }
}
