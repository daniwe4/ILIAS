<?php

namespace CaT\Plugins\Webinar\VC\CSN;

use CaT\Plugins\Webinar\VC\VCSettings;

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
        $obj_id,
        $phone,
        $pin,
        $minutes_required,
        $upload_required = false
    ) {
        assert('is_int($obj_id)');
        assert('is_string($phone) || is_null($phone)');
        assert('is_string($pin) || is_null($pin)');
        assert('is_int($minutes_required) || is_null($minutes_required)');
        assert('is_bool($upload_required)');

        $this->obj_id = $obj_id;
        $this->phone = $phone;
        $this->pin = $pin;
        $this->minutes_required = $minutes_required;
        $this->upload_required = $upload_required;
    }

    /**
     * @inheritdoc
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * @inheritdoc
     */
    public function withValuesOf(VCSettings $settings)
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
     * @return this
     */
    public function withPhone($phone)
    {
        assert('is_string($phone) || is_null($phone)');
        $clone = clone $this;
        $clone->phone = $phone;
        return $clone;
    }

    /**
     * Get clone of this with new pin
     *
     * @param string 	$pin
     *
     * @return this
     */
    public function withPin($pin)
    {
        assert('is_string($pin) || is_null($pin)');
        $clone = clone $this;
        $clone->pin = $pin;
        return $clone;
    }

    /**
     * Get clone of this with new minutes required
     *
     * @param int 	$minutes_required
     *
     * @return this
     */
    public function withMinutesRequired($minutes_required)
    {
        assert('is_int($minutes_required) || is_null($minutes_required)');
        $clone = clone $this;
        $clone->minutes_required = $minutes_required;
        return $clone;
    }

    /**
     * Get clone with upoad required
     *
     * @param bool 	$upload_required
     *
     * @return Webinar
     */
    public function withIsUploadRequired($upload_required)
    {
        assert('is_bool($upload_required)');
        $clone = clone $this;
        $clone->upload_required = $upload_required;
        return $clone;
    }
}
