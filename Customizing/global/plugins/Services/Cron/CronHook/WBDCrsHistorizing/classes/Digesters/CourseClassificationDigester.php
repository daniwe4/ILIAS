<?php declare(strict_types = 1);

namespace CaT\Plugins\WBDCrsHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/classes/class.ilObjCourseClassification.php';

class CourseClassificationDigester implements Digester
{
    public function digest(array $payload)
    {
        return $this->getCCDataByRefId($payload['cc_ref_id']);
    }

    public function getCCDataByRefId(int $ref_id)
    {
        $cc_obj = new \ilObjCourseClassification($ref_id);
        $contact = $cc_obj->getCourseClassification()->getContact();
        $firstname = '';
        $lastname = $contact->getName();
        $phone = $contact->getPhone();
        $mail = $contact->getMail();
        $title = '';
        $return = [
            'contact_title_xccl' => $title
            ,'contact_firstname_xccl' => $firstname
            ,'contact_lastname_xccl' => $lastname
            ,'contact_email_xccl' => $mail
            ,'contact_phone_xccl' => $phone
        ];
        return $return;
    }
}
