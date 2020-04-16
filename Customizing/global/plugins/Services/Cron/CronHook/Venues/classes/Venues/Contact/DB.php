<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Contact;

/**
 * Inteface for contact configuration DB
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Create a contact configuration
     *
     * @param int 	$id
     * @param string 	$internal_contact
     * @param string 	$contact
     * @param string 	$phone
     * @param string 	$fax
     * @param string 	$email
     *
     * @return Contact
     */
    public function create(
        int $id,
        string $internal_contact = "",
        string $contact = "",
        string $phone = "",
        string $fax = "",
        string $email = ""
    ) : Contact;

    public function update(Contact $contact);
    public function delete(int $id);
}
