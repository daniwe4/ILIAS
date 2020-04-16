<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "./Services/Form/classes/class.ilFormPropertyGUI.php";
require_once __DIR__ . "/ilTextAreaGUI.php";

use CaT\Plugins\ScaledFeedback\Feedback\Rating\Rating;

class ilRatingGui extends ilFormPropertyGUI
{
    public function __construct(Rating $rating, string $plugin_dir)
    {
        parent::__construct($rating->getDimTitle(), "dim_rating_" . $rating->getDimId());

        $this->rating = $rating;
        $this->dim_title = $rating->getDimTitle();
        $this->plugin_dir = $plugin_dir;

        global $DIC;
        $DIC->ui()->maintemplate()->addCss($this->plugin_dir . "/templates/default/rating.css");
    }

    public function render() : string
    {
        $tpl = new ilTemplate("tpl.rating.html", true, true, $this->plugin_dir);
        $tpl->setVariable("ID", $this->rating->getDimId());
        $tpl->setVariable("POSTVAR", $this->getPostVar() . "[rating]");
        $captions = $this->fillCaptions($this->rating->getCaptions());
        for ($i = 1; $i <= 5; $i++) {
            if ($i == $this->rating->getRating()) {
                $tpl->touchBlock("selected_" . $i);
            }
            $tpl->setVariable(
                "SCALECAPTION_" . (string) ($i),
                $captions[($i - 1)]
            );
        }

        if ($this->rating->getEnableComment()) {
            $textarea = new ilTextAreaGUI("", $this->getPostVar() . "[textarea]");
            $textarea->setRows(6);
            $textarea->setValue($this->rating->getText());
            $tpl->setVariable("TEXT", $textarea->render());
        }

        $byline = $this->rating->getByline();
        if ($byline !== '') {
            $tpl->setVariable("BYLINE", $byline);
        }
        return $tpl->get();
    }

    public function insert(ilTemplate $tpl)
    {
        $html = $this->render();

        $tpl->setCurrentBlock("prop_generic");
        $tpl->setVariable("PROP_GENERIC", $html);
        $tpl->parseCurrentBlock();
    }

    public function setValueByArray(array $post)
    {
        $values = $post[$this->getPostVar()];

        if ($values['textarea'] == null) {
            $values['textarea'] = "";
        }

        $this->rating = $this->rating
            ->withRating((int) $values['rating'])
            ->withText($values['textarea'])
        ;
    }

    public function checkInput() : bool
    {
        $post = $_POST;
        $values = $post[$this->getPostVar()];
        if ($values['rating'] == 0) {
            return false;
        }
        return true;
    }

    /**
     * Fill up (or truncate) captions to exactly five elements.
     */
    private function fillCaptions(array $captions) : array
    {
        $scale_captions = array_fill(0, 5, '');
        $scale_captions = array_replace($scale_captions, $captions);
        return array_slice($scale_captions, 0, 5);
    }
}
