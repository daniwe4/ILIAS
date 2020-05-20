<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\History;

use PHPUnit\Framework\TestCase;

class UserActivityTest extends TestCase
{
    public function data()
    {
        return [
            [['act_type' => UserActivity::ACT_TYPE_BOOKED,
              'usr_id' => 1,
              'firstname' => 'fn1',
              'lastname' => 'ln1',
              'login' => 'lg1',
              'usr_idd' => '00:01',
              'usr_overnignts' => ['2017-01-01', '2017-01-02'],
              'crs_id' => 2,
              'type' => 'foo',
              'title' => 'foo_name',
              'crs_start' => '2017-01-02',
              'crs_end' => null,
              'crs_idd' => '01:01',
              'prior_night' => true,
              'following_night' => true
             ]
            ],
            [['act_type' => UserActivity::ACT_TYPE_BOOKED_WAITING,
              'usr_id' => 1,
              'firstname' => 'fn1',
              'lastname' => 'ln1',
              'login' => 'lg1',
              'usr_idd' => '00:01',
              'usr_overnignts' => [],
              'crs_id' => 2,
              'type' => 'foo',
              'title' => 'foo_name',
              'crs_start' => null,
              'crs_end' => '2017-01-02',
              'crs_idd' => '01:01',
              'prior_night' => true,
              'following_night' => true
             ]
            ],
            [['act_type' => UserActivity::ACT_TYPE_CANCELLED,
              'usr_id' => 1,
              'firstname' => 'fn1',
              'lastname' => 'ln1',
              'login' => 'lg1',
              'usr_idd' => '20:01',
              'usr_overnignts' => ['2017-01-02'],
              'crs_id' => 2,
              'type' => 'foo',
              'title' => 'foo_name',
              'crs_start' => null,
              'crs_end' => null,
              'crs_idd' => '21:01',
              'prior_night' => true,
              'following_night' => true
             ]
            ]
        ];
    }

    /**
     * @dataProvider data
     */
    public function test_create($data)
    {
        $act = $this->getActivityFromData($data);
        $this->assertInstanceOf('\CaT\Plugins\StatusMails\History\UserActivity', $act);
        $this->assertEquals($data, $this->getDataFromActivity($act));
    }

    protected function getActivityFromData(array $data)
    {
        return new UserActivity(
            $data['act_type'],
            $data['usr_id'],
            $data['crs_id'],
            $data['firstname'],
            $data['lastname'],
            $data['login'],
            $data['type'],
            $data['title'],
            $data['crs_start'] === null ? null : new \DateTime($data['crs_start']),
            $data['crs_end'] === null ? null : new \DateTime($data['crs_end']),
            $data['usr_idd'],
            $data['crs_idd'],
            array_map(function ($dt_string) {
                return new \DateTime($dt_string);
            }, $data['usr_overnignts']),
            $data['prior_night'],
            $data['following_night']
        );
    }

    protected function getDataFromActivity(UserActivity $act)
    {
        return [
            'act_type' => $act->getActivityType(),
            'usr_id' => $act->getUserId(),
            'firstname' => $act->getUserFirstName(),
            'lastname' => $act->getUserLastName(),
            'login' => $act->getUserLogin(),
            'usr_idd' => $act->getUserIDDTime(),
            'usr_overnignts' => array_map(function ($dt_obj) {
                return $dt_obj->format('Y-m-d');
            }, $act->getUserOvernights()),
            'crs_id' => $act->getCourseObjId(),
            'type' => $act->getCourseType(),
            'title' => $act->getCourseTitle(),
            'crs_start' => $act->getCourseStartDate() === null ? null : $act->getCourseStartDate()->format('Y-m-d'),
            'crs_end' => $act->getCourseEndDate() === null ? null : $act->getCourseEndDate()->format('Y-m-d'),
            'crs_idd' => $act->getCourseIDDTime(),
            'prior_night' => $act->getPriorNight(),
            'following_night' => $act->getFollowingNight()
        ];
    }
}
