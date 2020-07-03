<?php
declare(strict_types=1);

namespace CaT\Plugins\OnlineSeminar\VC\CSN;

use CaT\Plugins\OnlineSeminar\VC\VCSettings;

/**
 * Settings for CSN VC
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Settings implements VCSettings
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var string
     */
    protected $pin;

    /**
     * @var int
     */
    protected $minutes_required;

    /**
     * @var bool
     */
    protected $upload_required;

    /**
     * @param int 	$obj_id
     * @param string 	$phone
     * @param string 	$pin
     * @param int 	$minutes_required
     * @param bool 	$upload_required
     */
    public function __construct(
        int $obj_id,
        ?string $phone,
        ?string $pin,
        ?int $minutes_required,
        bool $upload_required = false
    ) {
        $this->obj_id = $obj_id;
        $this->phone = $phone;
        $this->pin = $pin;
        $this->minutes_required = $minutes_required;
        $this->upload_required = $upload_required;
    }

    /**
     * @inheritdoc
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @inheritdoc
     */
    public function withValuesOf(VCSettings $settings) : VCSettings
    {
        if (!is_a($settings, get_class($this))) {
            throw new \Exception("You can only apply settings of the same type.", 1);
        }
        $clone = clone $this;
        $clone->phone = $settings->getPhone();
        $clone->pin = $settings->getPin();
        $clone->minutes_required = $settings->getMinutesRequired();
        $clone->upload_required = $settings->isUploadRequired();
        return $clone;
    }

    /**
     * Get the phone number
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Get the pin to the vc
     *
     * @return string
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * Get the minutes user has minimum to stay in the vc
     *
     * @return int
     */
    public function getMinutesRequired()
    {
        return $this->minutes_required;
    }

    /**
     * Return a fileupload is required
     *
     * @return bool
     */
    public function isUploadRequired()
    {
        return $this->upload_required;
    }

    /**
     * Get clone of this with new phone
     *
     * @param string 	$phone
     *
     * @return Settings
     */
    public function withPhone(?string $phone)
    {
        $clone = clone $this;
        $clone->phone = $phone;
        return $clone;
    }

    /**
     * Get clone of this with new pin
     *
     * @param string 	$pin
     *
     * @return Settings
     */
    public function withPin(?string $pin)
    {
        $clone = clone $this;
        $clone->pin = $pin;
        return $clone;
    }

    /**
     * Get clone of this with new minutes required
     *
     * @param int 	$minutes_required
     *
     * @return Settings
     */
    public function withMinutesRequired(?int $minutes_required)
    {
        $clone = clone $this;
        $clone->minutes_required = $minutes_required;
        return $clone;
    }

    /**
     * Get clone with upload required
     *
     * @param bool 	$upload_required
     *
     * @return Settings
     */
    public function withIsUploadRequired(bool $upload_required)
    {
        $clone = clone $this;
        $clone->upload_required = $upload_required;
        return $clone;
    }
}
