<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * User-related placeholder-values, this time for the currently logged in user.
 */
class ilTMSMailContextTargetUser extends ilTMSMailContextUser
{
    protected static $PLACEHOLDER = array(
        'TARGET_USER_SALUTATION' => 'placeholder_desc_target_user_salutation',
        'TARGET_USER_FIRST_NAME' => 'placeholder_desc_target_user_firstName',
        'TARGET_USER_LAST_NAME' => 'placeholder_desc_target_user_lastName',
        'TARGET_USER_LOGIN' => 'placeholder_desc_target_user_login',
        'TARGET_USER_EMAIL' => 'placeholder_desc_target_user_email',
        'TARGET_USER_TITLE' => 'placeholder_desc_target_user_title'
    );

    /**
     * @inheritdoc
     */
    public function valueFor(string $placeholder_id, array $contexts = array()) : ?string
    {
        switch ($placeholder_id) {
            case 'TARGET_USER_SALUTATION':
                return $this->salutation();
            case 'TARGET_USER_FIRST_NAME':
                return $this->firstName();
            case 'TARGET_USER_LAST_NAME':
                return $this->lastName();
            case 'TARGET_USER_LOGIN':
                return $this->login();
            case 'TARGET_USER_EMAIL':
                return $this->email();
            case 'TARGET_USER_TITLE':
                return $this->title();
            default:
                return null;
        }
    }
}
