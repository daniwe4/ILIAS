<?php

namespace CaT\Plugins\Webinar\Settings;

/**
 * This is the object for additional settings.
 */
class Webinar
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var string
     */
    protected $vc_type;

    /**
     * @var ilDateTime | null
     */
    protected $beginning;

    /**
     * @var ilDateTime | null
     */
    protected $ending;

    /**
     * @var string | null
     */
    protected $admission;

    /**
     * @var string | null
     */
    protected $url;

    /**
     * @var bool
     */
    protected $online;

    /**
     * @var int
     */
    protected $lp_mode;

    /**
     * @var bool
     */
    protected $finished;

    /**
     * @param int 	$obj_id
     * @param string 	$vc_type
     * @param \ilDateTime | null 	$beginning
     * @param \ilDateTime | null 	$ending
     * @param string | null 	$admission
     * @param string | null 	$url
     * @param bool 	$online
     * @param int 	$lp_mode
     * @param bool 	$finished
     */
    public function __construct(
        $obj_id,
        $vc_type,
        \ilDateTime $beginning = null,
        \ilDateTime $ending = null,
        $admission = null,
        $url = null,
        $online = false,
        $lp_mode = 0,
        $finished = false
    ) {
        assert('is_int($obj_id)');
        assert('is_string($vc_type)');
        assert('is_string($admission) | is_null($url)');
        assert('is_string($url) | is_null($url)');
        assert('is_bool($online) | is_null($url)');
        assert('is_int($lp_mode)');
        assert('is_bool($finished)');

        $this->obj_id = $obj_id;
        $this->vc_type = $vc_type;
        $this->beginning = $beginning;
        $this->ending = $ending;
        $this->admission = $admission;
        $this->url = $url;
        $this->online = $online;
        $this->lp_mode = $lp_mode;
        $this->finished = $finished;
    }

    /**
     * Get the obj id
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Get type of vc
     *
     * @return string
     */
    public function getVCType()
    {
        return $this->vc_type;
    }

    /**
     * Get beginn date
     *
     * @return \ilDateTime
     */
    public function getBeginning()
    {
        return $this->beginning;
    }

    /**
     * Get end date
     *
     * @return \ilDateTime
     */
    public function getEnding()
    {
        return $this->ending;
    }

    /**
     * Get the admission
     *
     * @return string
     */
    public function getAdmission()
    {
        return $this->admission;
    }

    /**
     * Get the url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the online state
     *
     * @return bool
     */
    public function getOnline()
    {
        return $this->online;
    }

    /**
     * Get the lp mode
     *
     * @return int
     */
    public function getLPMode()
    {
        return $this->lp_mode;
    }

    /**
     * Return the webinar is finished
     *
     * @return bool
     */
    public function isFinished()
    {
        return $this->finished;
    }

    /**
     * Get clone with vc type
     *
     * @param string $vc_type
     *
     * @return Webinar
     */
    public function withVCType(string $vc_type)
    {
        $clone = clone $this;
        $clone->vc_type = $vc_type;
        return $clone;
    }

    /**
     * Get clone with beginning
     *
     * @param \ilDateTime 	$beginning
     *
     * @return Webinar
     */
    public function withBeginning(\ilDateTime $beginning = null)
    {
        $clone = clone $this;
        $clone->beginning = $beginning;
        return $clone;
    }

    /**
     * Get clone with ending
     *
     * @param \ilDateTime 	$ending
     *
     * @return Webinar
     */
    public function withEnding(\ilDateTime $ending = null)
    {
        $clone = clone $this;
        $clone->ending = $ending;
        return $clone;
    }

    /**
     * Get clone with admission
     *
     * @param string 	$admission
     *
     * @return Webinar
     */
    public function withAdmission($admission)
    {
        assert('is_string($admission) | is_null($admission)');
        $clone = clone $this;
        $clone->admission = $admission;
        return $clone;
    }

    /**
     * Get clone with url
     *
     * @param string 	$url
     *
     * @return Webinar
     */
    public function withUrl($url)
    {
        assert('is_string($url) | is_null($url)');
        $clone = clone $this;
        $clone->url = $url;
        return $clone;
    }

    /**
     * Get clone with online
     *
     * @param bool 	$online
     *
     * @return Webinar
     */
    public function withOnline($online)
    {
        assert('is_bool($online)');
        $clone = clone $this;
        $clone->online = $online;
        return $clone;
    }

    /**
     * Get clone with lp mode
     *
     * @param int 	$lp_mode
     *
     * @return Webinar
     */
    public function withLPMode($lp_mode)
    {
        assert('is_int($lp_mode)');
        $clone = clone $this;
        $clone->lp_mode = $lp_mode;
        return $clone;
    }

    /**
     * Get clone with finished
     *
     * @param bool 	$finished
     *
     * @return Webinar
     */
    public function withFinished($finished)
    {
        assert('is_bool($finished)');
        $clone = clone $this;
        $clone->finished = $finished;
        return $clone;
    }
}
