<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "./Services/Form/classes/class.ilFormPropertyGUI.php";
require_once __DIR__ . "/ilTextAreaGUI.php";

class ilRatingTextualGui extends ilFormPropertyGUI
{
    /**
     * @var string
     */
    protected $dim_title;

    /**
     * @var string
     */
    protected $byline;

    /**
     * @var int
     */
    protected $dim_id;

    /**
     * @var string
     */
    protected $text;


    public function __construct(string $title, string $byline, int $dim_id, string $plugin_dir)
    {
        parent::__construct($title, "dim_rating_" . $dim_id);

        $this->dim_title = $title;
        $this->byline = $byline;
        $this->plugin_dir = $plugin_dir;
    }

    public function render() : string
    {
        $tpl = new ilTemplate("tpl.rating_textual.html", true, true, $this->plugin_dir);

        $textarea = new ilTextAreaGUI("", $this->getPostVar() . "[textarea]");
        $textarea->setRows(6);
        $textarea->setValue($this->text);
        $tpl->setVariable("TEXT", $textarea->render());

        $byline = $this->byline;
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
        $this->text = $values['textarea'];
    }

    public function checkInput() : bool
    {
        $post = $_POST;
        $values = $post[$this->getPostVar()];
        if ($values['textarea'] == "") {
            return false;
        }
        return true;
    }
}
