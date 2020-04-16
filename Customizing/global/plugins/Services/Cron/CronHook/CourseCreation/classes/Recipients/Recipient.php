<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseCreation\Recipients;

class Recipient
{
    /**
     * @var int
     */
    protected $user_id;
    /**
     * @var string
     */
    protected $login;

    public function __construct(int $user_id, string $login)
    {
        $this->user_id = $user_id;
        $this->login = $login;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function getLogin() : string
    {
        return $this->login;
    }
}
