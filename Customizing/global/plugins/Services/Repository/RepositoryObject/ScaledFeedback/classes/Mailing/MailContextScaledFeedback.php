<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Mailing;

require_once './Services/TMS/Mailing/classes/class.ilTMSMailContextEnte.php';

class MailContextScaledFeedback extends \ilTMSMailContextEnte
{
    protected static $PLACEHOLDERS = array(
        'FEEDBACK_URL' => 'placeholder_desc_scaled_feedback'
    );

    /**
     * @inheritdoc
     */
    public function placeholderIds()
    {
        return array_keys(self::$PLACEHOLDERS);
    }

    /**
     * @inheritdoc
     */
    public function placeholderDescriptionForId(string $placeholder_id) : string
    {
        $desc = self::$PLACEHOLDERS[$placeholder_id];
        $plug = \ilPluginAdmin::getPluginObjectById("xfbk");
        return $plug->txt($desc);
    }

    /**
     * @inheritdoc
     */
    public function valueFor(string $placeholder_id, array $contexts = array()) : ?string
    {
        if (!in_array($placeholder_id, $this->placeholderIds())) {
            return null;
        }
        return $this->resolveValueFor($placeholder_id, $contexts);
    }

    /**
     * @param string $id
     * @param MailContexts[] $contexts
     * @return $string | null
     */
    protected function resolveValueFor($id, array $contexts)
    {
        //$this->owner holds array of owners, it's a shared provider
        if ($id === 'FEEDBACK_URL') {
            require_once __DIR__ . '/../Feedback/class.ilFeedbackGUI.php';
            require_once './Services/Link/classes/class.ilLink.php';
            $buf = [];
            $template = '<a href="%s">%s: %s</a>';

            foreach ($this->owner as $xfbk_obj) {
                if ($xfbk_obj->getSettings()->getOnline()) {
                    $url = \ilLink::_getStaticLink($xfbk_obj->getRefId(), 'xfbk', true);

                    $buf[] = sprintf(
                        $template,
                        $url,
                        $xfbk_obj->getTitle(),
                        $url
                    );
                }
            }
            return implode('<br>', $buf);
        }

        return null;
    }
}
