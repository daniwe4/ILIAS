<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * User-related placeholder-values
 */
class ilTMSMailContextUser implements Mailing\MailContext
{
    protected static $PLACEHOLDER = array(
        'MAIL_SALUTATION' => 'placeholder_desc_user_salutation',
        'FIRST_NAME' => 'placeholder_desc_user_firstName',
        'LAST_NAME' => 'placeholder_desc_user_lastName',
        'LOGIN' => 'placeholder_desc_user_login',
        'TITLE' => 'placeholder_desc_user_title'
    );

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var ilObjUser
     */
    protected $usr;

    /**
     * @var ilLanguage
     */
    protected $g_lang;


    public function __construct(int $usr_id)
    {
        global $DIC;
        $this->g_lang = $DIC->language();
        $this->g_lang->loadLanguageModule("tms");
        $this->usr_id = $usr_id;
    }

    /**
     * @inheritdoc
     */
    public function valueFor($placeholder_id, $contexts = array())
    {
        switch ($placeholder_id) {
            case 'MAIL_SALUTATION':
                return $this->salutation();
            case 'FIRST_NAME':
                return $this->firstName();
            case 'LAST_NAME':
                return $this->lastName();
            case 'LOGIN':
                return $this->login();
            case 'TITLE':
                return $this->title();
            default:
                return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function placeholderIds()
    {
        return array_keys(static::$PLACEHOLDER);
    }

    /**
     * @inheritdoc
     */
    public function placeholderDescriptionForId($placeholder_id)
    {
        return $this->g_lang->txt(static::$PLACEHOLDER[$placeholder_id]);
    }

    /**
     * @return int
     */
    public function getUsrId()
    {
        return $this->usr_id;
    }

    /**
     * @return ilObjUser
     */
    protected function getUser()
    {
        if (!$this->usr) {
            $this->usr = new \ilObjUser($this->usr_id);
        }
        return $this->usr;
    }

    /**
     * @return string
     */
    protected function salutation()
    {
        $salutation = 'salutation';
        $gender = $this->getUser()->getGender();
        if ($gender === 'm') {
            $salutation = 'salutation_m';
        }
        if ($gender === 'f') {
            $salutation = 'salutation_f';
        }
        return $this->g_lang->txt($salutation);
    }

    /**
     * @return string
     */
    protected function firstName()
    {
        return $this->getUser()->getFirstname();
    }

    /**
     * @return string
     */
    protected function lastName()
    {
        return $this->getUser()->getLastname();
    }

    /**
     * @return string
     */
    protected function login()
    {
        return $this->getUser()->getLogin();
    }

    /**
     * @return string
     */
    protected function email()
    {
        return $this->getUser()->getEmail();
    }

    /**
     * @return string
     */
    protected function title()
    {
        $user_title = $this->getUser()->getUTitle();
        if (trim($user_title) !== '') {
            $user_title = ' ' . $user_title;
        }
        return $user_title;
    }
}
