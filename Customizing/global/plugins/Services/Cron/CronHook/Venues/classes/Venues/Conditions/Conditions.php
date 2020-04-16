<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Conditions;

/**
 * Venue configuration entries for conditions settings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Conditions
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Are there general ageements with venue
     *
     * @var bool
     */
    protected $general_agreement = false;

    /**
     * Further informations according to general agreements or informations
     *
     * @var string
     */
    protected $terms = "";

    /**
     * Name of valuta venue uses
     *
     * @var string
     */
    protected $valuta = "";

    public function __construct(
        int $id,
        bool $general_agreement = false,
        string $terms = "",
        string  $valuta = ""
    ) {
        $this->id = $id;
        $this->general_agreement = $general_agreement;
        $this->terms = $terms;
        $this->valuta = $valuta;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getGeneralAgreement() : bool
    {
        return $this->general_agreement;
    }

    public function getTerms() : string
    {
        return $this->terms;
    }

    public function getValuta() : string
    {
        return $this->valuta;
    }
}
