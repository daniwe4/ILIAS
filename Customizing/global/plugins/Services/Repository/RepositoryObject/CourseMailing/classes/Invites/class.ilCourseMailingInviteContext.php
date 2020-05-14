<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * Handles course mail placeholders
 */
class ilCourseMailingInviteContext extends ilMailTemplateContext
{
    use ilHandlerObjectHelper;

    const ID = 'xcml_context_invites';
    const REPOSITORY_REF_ID = 1;

    /**
     * @var Closure | null
     */
    protected $txt;

    /**
     * @var ilCourseMailingPlugin
     */
    protected $pl_object;

    /**
     * @var int
     */
    protected $entity_ref_id = self::REPOSITORY_REF_ID;

    /**
     * @return string
     */
    public function getId() : string
    {
        return self::ID;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->txt('xcml_context_invites_title');
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->txt('xcml_context_invites_info');
    }

    /**
     * Return an array of placeholders
     * @return array
     */
    public function getSpecificPlaceholders() : array
    {
        $placeholders = array();

        $placeholders['reject_invite_link'] = array(
            'placeholder' => 'REJECT_INVITE_LINK',
            'label' => $this->txt('reject_invite_link')
        );

        foreach ($this->getContexts() as $context) {
            foreach ($context->placeholderIds() as $placeholder_id) {
                $id = get_class($context) . $placeholder_id;
                $placeholders[$id] = array(
                    'placeholder' => $placeholder_id,
                    'label' => $context->placeholderDescriptionForId($placeholder_id)
                );
            }
        }

        return $placeholders;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveSpecificPlaceholder(
        $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        $html_markup = false
    ) : string {
        if (is_null($recipient)) {
            return '';
        }

        if ($placeholder_id == 'reject_invite_link') {
            if (ilPluginAdmin::isPluginActive('xcml')) {
                $pl = $this->getPluginObject();
                $obj_id = (int) $context_parameters['obj_id'];
                $usr_id = (int) $recipient->getId();
                return $pl->getRejectLinkFor($usr_id, $obj_id);
            }
            return '';
        }

        $cur_usr_id = (int) $context_parameters['usr_id'];
        $crs_ref_id = (int) $context_parameters['crs_ref_id'];
        //Yes this is realy sketchy, but without there is no option to get the correct relpacements
        $this->entity_ref_id = $crs_ref_id;

        $contexts = $this->getContexts($cur_usr_id, (int) $recipient->getId(), $crs_ref_id);
        return $this->replacePlaceholder($placeholder_id, $contexts) ?? '';
    }

    protected function replacePlaceholder(string $placeholder_id, array $contexts)
    {
        foreach ($contexts as $context) {
            $context_title = get_class($context);
            if ($this->placeholderStartsWith($context_title, $placeholder_id)) {
                $clean_placeholder_id = str_replace($context_title, "", $placeholder_id);
                return $context->valueFor($clean_placeholder_id);
            }
        }

        return '';
    }

    protected function getContexts(
        int $current_usr_id = 0,
        int $target_usr_id = 0,
        int $crs_ref_id = 0
    ) {
        $ret = [
            new \ilTMSMailContextILIAS(),
            new \ilTMSMailContextUser($target_usr_id),
            new \ilTMSMailContextCourse($crs_ref_id),
            new \ilTMSMailContextCurrentUser(),
            new \ilTMSMailContextTargetUser($target_usr_id)
        ];

        $ret = array_merge(
            $ret,
            $this->getGloballyProvidedMailContexts()
        );

        if (ilPluginAdmin::isPluginActive('xcml')) {
            $ret[] = new CaT\Plugins\CourseMailing\AutomaticMails\ilMailContextReminder();
        }

        return $ret;
    }

    /**
     * Get all mailing contexts from Ente
     *
     * @return MailContext[]
     */
    protected function getGloballyProvidedMailContexts()
    {
        return $this->getComponentsOfType(ILIAS\TMS\Mailing\MailContext::class);
    }

    /**
     * @inheritdoc
     */
    protected function getEntityRefId()
    {
        return $this->entity_ref_id;
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    protected function txt(string $code) : string
    {
        if (ilPluginAdmin::isPluginActive('xcml')) {
            if (is_null($this->txt)) {
                $pl = $this->getPluginObject();
                $this->txt = $pl->txtClosure();
            }

            return call_user_func($this->txt, $code);
        }
        return $this->getLanguage()->txt($code);
    }

    protected function getPluginObject() : ilCourseMailingPlugin
    {
        if (is_null($this->pl_object)) {
            $this->pl_object = ilPluginAdmin::getPluginObjectById('xcml');
        }
        return $this->pl_object;
    }

    protected function placeholderStartsWith($search, $value) : bool
    {
        $len = strlen($search);
        return (substr($value, 0, $len) === $search);
    }

    /**
     * @inheritDoc
     */
    public function resolvePlaceholder(
        $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        $html_markup = false
    ) : string {
        if ($recipient !== null) {
            $this->initLanguage($recipient);
        }

        $old_lang = ilDatePresentation::getLanguage();
        ilDatePresentation::setLanguage($this->getLanguage());

        $resolved = '';

        switch (true) {
            case ('mail_salutation' == $placeholder_id && $recipient !== null):
                $placeholder_id = "ilTMSMailContextUser" . strtoupper($placeholder_id);
                $resolved = $this->resolveSpecificPlaceholder(
                    $placeholder_id,
                    $context_parameters,
                    $recipient,
                    $html_markup
                );
                break;

            case ('first_name' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getFirstname();
                break;

            case ('last_name' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getLastname();
                break;

            case ('login' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getLogin();
                break;

            case ('title' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getUTitle();
                break;

            case 'ilias_url' == $placeholder_id:
                $resolved = ILIAS_HTTP_PATH . '/login.php?client_id=' . CLIENT_ID;
                break;

            case 'client_name' == $placeholder_id:
                $resolved = CLIENT_NAME;
                break;

            case !in_array($placeholder_id, array_keys(self::getGenericPlaceholders())):
                $resolved = $this->resolveSpecificPlaceholder(
                    $placeholder_id,
                    $context_parameters,
                    $recipient,
                    $html_markup
                );
                break;
        }

        ilDatePresentation::setLanguage($old_lang);

        return $resolved;
    }
}
