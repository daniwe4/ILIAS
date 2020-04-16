<?php
namespace CaT\Plugins\Accounting\Data;

/**
 * This is the object for Data.
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class Data
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $pos;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $costtype;

    /**
     * @var \ilDate
     */
    protected $bill_date;

    /**
     * @var string
     */
    protected $nr;

    /**
     * @var \ilDate
     */
    protected $date_relay;

    /**
     * @var string
     */
    protected $issuer;

    /**
     * @var string
     */
    protected $bill_comment;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var int
     */
    protected $vatrate;

    /**
     * @var string
     */
    protected $ct_label;

    /**
     * @var string
     */
    protected $vr_label;

    /**
     * @var string
     */
    protected $ct_value;

    /**
     * @var int
     */
    protected $vr_value;

    public function __construct(
        int $id,
        int $pos,
        int $obj_id = 0,
        int $costtype = 0,
        \ilDate $bill_date = null,
        string $nr = "",
        \ilDate $date_relay = null,
        string $issuer = "",
        string $bill_comment = "",
        float $amount = 0.0,
        int $vatrate = 0,
        string $ct_label = "",
        string $vr_label = "",
        string $ct_value = "",
        int $vr_value = 0
    ) {
        $this->id = $id;
        $this->pos = $pos;
        $this->obj_id = $obj_id;
        $this->costtype = $costtype;
        $this->bill_date = $bill_date;
        $this->nr = $nr;
        $this->date_relay = $date_relay;
        $this->issuer = $issuer;
        $this->bill_comment = $bill_comment;
        $this->amount = $amount;
        $this->vatrate = $vatrate;
        $this->ct_label = $ct_label;
        $this->vr_label = $vr_label;
        $this->ct_value = $ct_value;
        $this->vr_value = $vr_value;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getPos() : int
    {
        return $this->pos;
    }

    public function getObjId() : int
    {
        return (int) $this->obj_id;
    }
    public function getCostType() : int
    {
        return $this->costtype;
    }

    public function withObjId(int $value) : Data
    {
        $clone = clone $this;
        $clone->costtype = $value;
        return $clone;
    }

    /**
     * @return \ilDate | null
     */
    public function getBillDate()
    {
        return $this->bill_date;
    }

    public function withBillDate(\ilDate $value = null) : Data
    {
        $clone = clone $this;
        $clone->bill_date = $value;
        return $clone;
    }

    public function getNr() : string
    {
        return $this->nr;
    }

    public function withNr(string $value) : Data
    {
        $clone = clone $this;
        $clone->nr = $value;
        return $clone;
    }

    /**
     * @return \ilDate | null
     */
    public function getDateRelay()
    {
        return $this->date_relay;
    }

    public function withDateRelay(\ilDate $value = null) : Data
    {
        $clone = clone $this;
        $clone->date_relay = $value;
        return $clone;
    }

    public function getIssuer() : string
    {
        return $this->issuer;
    }

    public function withIssuer(string $value) : Data
    {
        $clone = clone $this;
        $clone->issuer = $value;
        return $clone;
    }

    public function getBillComment() : string
    {
        return $this->bill_comment;
    }

    public function withBillComment(string $value) : Data
    {
        $clone = clone $this;
        $clone->bill_comment = $value;
        return $clone;
    }

    public function getAmount() : float
    {
        return $this->amount;
    }

    public function withAmount(float $value) : Data
    {
        $clone = clone $this;
        $clone->amount = $value;
        return $clone;
    }

    public function getVatrate() : int
    {
        return $this->vatrate;
    }

    public function withVatrate(int $value) : Data
    {
        $clone = clone $this;
        $clone->vatrate = $value;
        return $clone;
    }

    public function getCTLabel() : string
    {
        return $this->ct_label;
    }

    public function withCTLabel(string $value) : Data
    {
        $clone = clone $this;
        $clone->ct_label = $value;
        return $clone;
    }

    public function getVRLabel() : string
    {
        return $this->vr_label;
    }

    public function withVRLabel(string $value) : Data
    {
        $clone = clone $this;
        $clone->vr_label = $value;
        return $clone;
    }

    public function getCTValue() : string
    {
        return $this->ct_value;
    }

    public function withCTValue(string $value) : Data
    {
        $clone = clone $this;
        $clone->ct_value = $value;
        return $clone;
    }

    public function getVRValue() : int
    {
        return $this->vr_value;
    }

    public function withVRValue(int $value) : Data
    {
        $clone = clone $this;
        $clone->vr_value = $value;
        return $clone;
    }

    public function getGross() : float
    {
        return $this->getAmount() * ((100 + $this->getVRValue()) / 100);
    }
}
