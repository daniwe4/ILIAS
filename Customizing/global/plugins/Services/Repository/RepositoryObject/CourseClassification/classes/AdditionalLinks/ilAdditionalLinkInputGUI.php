<?php
//declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\AdditionalLinks;

/**
 * have n lines and handle as one input.
 */
class ilAdditionalLinkInputGUI extends \ilCustomInputGUI
{
    const PLUGIN_PATH = "Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification";
    const HTTP_REGEXP = "/^(https:\/\/)|(http:\/\/)[\w]+/";

    /**
     * @var AdditionalLink[]
     */
    protected $values = [];

    public function __construct(string $title, string $postvar)
    {
        parent::__construct($title, $postvar);
    }

    protected function writeLine(
        \ilGlobalTemplateInterface $tpl,
        string $postvar,
        string $label,
        string $url
    ) {
        $tpl->setCurrentBlock('line');
        $tpl->setVariable("POST_VAR", $postvar);
        $tpl->setVariable("LABEL_VALUE", $label);
        $tpl->setVariable("LABEL_LABEL", $this->lng->txt('title'));
        $tpl->setVariable("URL_VALUE", $url);
        $tpl->setVariable("URL_LABEL", $this->lng->txt('url'));
        $tpl->setVariable("SRC_ADD", \ilGlyphGUI::get(\ilGlyphGUI::ADD));
        $tpl->setVariable("SRC_REMOVE", \ilGlyphGUI::get(\ilGlyphGUI::REMOVE));
        $tpl->parseCurrentBlock();
    }

    /**
     * @inheritdoc
     */
    public function getHtml($a_mode = '') : string
    {
        $tpl = new \ilTemplate("tpl.additional_link_input.html", true, true, self::PLUGIN_PATH);
        $postvar = $this->getPostVar();

        if (count($this->values) === 0) {
            $this->writeLine($tpl, $postvar, '', '');
        } else {
            foreach ($this->values as $link) {
                $this->writeLine($tpl, $postvar, $link->getLabel(), $link->getUrl());
            }
        }

        return $tpl->get();
    }

    /**
     * @return AdditionalLink[]
     */
    public function retrieveValuesFromPost($post) : array
    {
        $postvar = $this->getPostVar();
        $pv_keys = $postvar . '_key';
        $pv_vals = $postvar . '_value';
        $post_keys = $post[$pv_keys];
        $post_vals = $post[$pv_vals];

        $values = [];
        if (!is_null($post_keys) && !is_null($post_vals)) {
            foreach ($post_keys as $index => $value) {
                $k = trim($value);
                $v = trim($post_vals[$index]);
                if ($k == '' && $v == '') {
                    continue;
                }

                if ($v != "" && preg_match(self::HTTP_REGEXP, $v) !== 1) {
                    $v = "https://" . $v;
                }

                $link = new AdditionalLink($k, $v);
                $values[] = $link;
            }
        }
        return $values;
    }

    /**
     * @inheritdoc
     */
    public function checkInput()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setValueByArray($a_values)
    {
        $this->values = $this->retrieveValuesFromPost($a_values);
    }
}
