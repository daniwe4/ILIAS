<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\Provider;

/**
 * Interface for provider database handle
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
interface DB
{

    /**
     * Install provider needed object like tables or sequences
     */
    public function install();

    /**
     * Create a new provider. Object and DB
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
     *
     * @return \CaT\Plugins\TrainingProvider\Provider\Provider
     */
    public function create($name, $rating = 0.0, $info = "", $address1 = "", $country = "", $address2 = "", $postcode = "", $city = "", $homepage = "", $internal_contact = "", $contact = "", $phone = "", $fax = "", $email = "", $general_agreement = "", $terms = "", $valuta = "");

    /**
     * Get a provider object
     *
     * @param int 			$id
     *
     * @return \CaT\Plugins\TrainingProvider\Provider\Provider
     */
    public function select($id);

    /**
     * Update a provider
     *
     * @param \CaT\Plugins\TrainingProvider\Provider\Provider 		$provider
     */
    public function update(\CaT\Plugins\TrainingProvider\Provider\Provider $provider);

    /**
     * Delete a provider
     *
     * @param int 		$id
     */
    public function delete($id);

    /**
     * Check the name of the new provider is existing
     *
     * @param string 	$new_provider_name
     *
     * @return bool
     */
    public function providerNameExist($new_provider_name);
}
