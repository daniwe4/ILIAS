<?php
declare(strict_types=1);

namespace CaT\Plugins\OnlineSeminar\VC\Generic;

use CaT\Plugins\OnlineSeminar\VC\VCSettings;

/**
 * Settings for Generic VC
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
    protected $password;

    /**
     * @var string
     */
    protected $tutor_login;

    /**
     * @var string
     */
    protected $tutor_password;

    /**
     * @var int
     */
    protected $minutes_required;

    /**
     * @param int 	$obj_id
     * @param string | null	$password
     * @param string | null	$tutor_login
     * @param string | null	$tutor_password
     * @param int | null	$minutes_required
     */
    public function __construct(
        int $obj_id,
        ?string $password = null,
        ?string $tutor_login = null,
        ?string $tutor_password = null,
        ?int $minutes_required = null
    ) {
        $this->obj_id = $obj_id;
        $this->password = $password;
        $this->tutor_login = $tutor_login;
        $this->tutor_password = $tutor_password;
        $this->minutes_required = $minutes_required;
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
        $clone->password = $settings->getPassword();
        $clone->tutor_login = $settings->getTutorLogin();
        $clone->tutor_password = $settings->getTutorPassword();
        $clone->minutes_required = $settings->getMinutesRequired();
        return $clone;
    }

    /**
     * Get the password to the vc
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get the tutor login
     *
     * @return int
     */
    public function getTutorLogin()
    {
        return $this->tutor_login;
    }

    /**
     * Get the minutes user has minimum to stay in the vc
     *
     * @return int
     */
    public function getTutorPassword()
    {
        return $this->tutor_password;
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
     * Get clone of this with password
     *
     * @param string 	$password
     *
     * @return Settings
     */
    public function withPassword(?string $password)
    {
        $clone = clone $this;
        $clone->password = $password;
        return $clone;
    }

    /**
     * Get clone of this with tutor login
     *
     * @param int 	$tutor_login
     *
     * @return Settings
     */
    public function withTutorLogin(?string $tutor_login)
    {
        $clone = clone $this;
        $clone->tutor_login = $tutor_login;
        return $clone;
    }

    /**
     * Get clone of this with tutor password
     *
     * @param int 	$tutor_password
     *
     * @return Settings
     */
    public function withTutorPassword(?string $tutor_password)
    {
        $clone = clone $this;
        $clone->tutor_password = $tutor_password;
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
}
