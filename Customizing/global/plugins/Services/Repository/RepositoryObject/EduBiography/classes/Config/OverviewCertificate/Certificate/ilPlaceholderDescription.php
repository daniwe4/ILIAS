<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;

class ilPlaceholderDescription implements \ilCertificatePlaceholderDescription
{
    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var string[]
     */
    protected $placeholders;

    protected $default_placeholder;

    public function __construct(
        \Closure $txt,
        \ilDefaultPlaceholderDescription $default_placeholder
    ) {
        $this->txt = $txt;
        $this->default_placeholder = $default_placeholder;
    }

    /**
     * @inheritDoc
     */
    public function getPlaceholderDescriptions()
    {
        if (is_null($this->placeholders)) {
            $this->placeholders = array_merge(
                $this->default_placeholder->getPlaceholderDescriptions(),
                $this->getOverviewPlaceholders()
            );
        }

        return $this->placeholders;
    }

    /**
     * @inheritDoc
     */
    public function createPlaceholderHtmlDescription() : string
    {
        $template = new \ilTemplate('tpl.default_description.html', true, true, 'Services/Certificate');
        $template->setVariable("PLACEHOLDER_INTRODUCTION", $this->txt("placeholder"));

        $template->setCurrentBlock("items");
        foreach ($this->getPlaceholderDescriptions() as $id => $caption) {
            $template->setVariable("ID", strtoupper($id));
            $template->setVariable("TXT", $caption);
            $template->parseCurrentBlock();
        }

        return $template->get();
    }

    protected function getOverviewPlaceholders()
    {
        return [
            "total_idd_value" => $this->txt("total_idd_value"),
            "period_start" => $this->txt("period_start"),
            "period_end" => $this->txt("period_end")
        ];
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
