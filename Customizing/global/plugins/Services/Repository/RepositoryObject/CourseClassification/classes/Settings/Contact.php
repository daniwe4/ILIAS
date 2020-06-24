<?php

namespace CaT\Plugins\CourseClassification\Settings;

/**
 * Meta inforamtion for contact informations of course
 */
class Contact
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $responsibility;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var string
     */
    protected $mail;

    /**
     * @param string 	$name
     * @param string 	$responsibility
     * @param string 	$phone
     * @param string 	$mail
     */
    public function __construct(string $name = "", string $responsibility = "", string  $phone = "", string $mail = "")
    {
        $this->name = $name;
        $this->responsibility = $responsibility;
        $this->phone = $phone;
        $this->mail = $mail;
    }

    /**
     * Get the contact name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the contact responsibility
     *
     * @return string
     */
    public function getResponsibility()
    {
        return $this->responsibility;
    }

    /**
     * Get the contact phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Get the contact mail
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Get a clone of this with name
     *
     * @param string 	$name
     *
     * @return Contact
     */
    public function withName(string $name)
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    /**
     * Get a clone of this with responsibility
     *
     * @param string 	$responsibility
     *
     * @return Contact
     */
    public function withResponsibility(string $responsibility)
    {
        $clone = clone $this;
        $clone->responsibility = $responsibility;
        return $clone;
    }

    /**
     * Get a clone of this with phone
     *
     * @param string 	$phone
     *
     * @return Contact
     */
    public function withPhone(string $phone)
    {
        $clone = clone $this;
        $clone->phone = $phone;
        return $clone;
    }

    /**
     * Get a clone of this with mail
     *
     * @param string 	$mail
     *
     * @return Contact
     */
    public function withMail(string $mail)
    {
        $clone = clone $this;
        $clone->mail = $mail;
        return $clone;
    }
}
