<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\Mailing;

use \CaT\Plugins\StatusMails\History\UserActivity;

/**
 * Context for status-mails.
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
class ilMailContextStatus extends DynamicContext
{
    protected static $PLACEHOLDER = array(
        'EMPLOYEES_BOOKED' => 'placeholder_statusmails_booked'
        ,
        'EMPLOYEES_WAITING' => 'placeholder_statusmails_waiting'
        ,
        'EMPLOYEES_CANCELLED' => 'placeholder_statusmails_canceled'
        ,
        'EMPLOYEES_COMPLETED' => 'placeholder_statusmails_completed'
        ,
        'EMPLOYEES_FAILED' => 'placeholder_statusmails_failed'
        ,
        'EMPLOYEES_CANCELLED_WAITING' => 'placeholder_statusmails_waiting_canceled'
        ,
        'EMPLOYEES_APPROVAL_PENDING' => 'placeholder_statusmails_approval_pending'
        ,
        'EMPLOYEES_APPROVAL_APPROVED' => 'placeholder_statusmails_approval_approved'
    );

    /**
     * @inheritdoc
     */
    public function valueFor(string $placeholder_id, array $contexts = []) : ?string
    {
        switch ($placeholder_id) {
            case 'EMPLOYEES_BOOKED':
                $data = array_filter($this->getData(), function ($act) {
                    return $act->getActivityType() === UserActivity::ACT_TYPE_BOOKED;
                });
                return $this->fillBlock($data);

            case 'EMPLOYEES_WAITING':
                $data = array_filter($this->getData(), function ($act) {
                    return $act->getActivityType() === UserActivity::ACT_TYPE_BOOKED_WAITING;
                });
                return $this->fillBlock($data);

            case 'EMPLOYEES_CANCELLED':
                $data = array_filter($this->getData(), function ($act) {
                    return $act->getActivityType() === UserActivity::ACT_TYPE_CANCELLED;
                });
                return $this->fillBlock($data);

            case 'EMPLOYEES_COMPLETED':
                $data = array_filter($this->getData(), function ($act) {
                    return $act->getActivityType() === UserActivity::ACT_TYPE_COMPLETED;
                });
                return $this->fillBlock($data);

            case 'EMPLOYEES_FAILED':
                $data = array_filter($this->getData(), function ($act) {
                    return $act->getActivityType() === UserActivity::ACT_TYPE_FAILED;
                });
                return $this->fillBlock($data);

            case 'EMPLOYEES_CANCELLED_WAITING':
                $data = array_filter($this->getData(), function ($act) {
                    return $act->getActivityType() === UserActivity::ACT_TYPE_CANCELLED_WAITING;
                });
                return $this->fillBlock($data);

            case 'EMPLOYEES_APPROVAL_PENDING':
                $data = array_filter($this->getData(), function ($act) {
                    return $act->getActivityType() === UserActivity::ACT_TYPE_REQUEST_PENDING;
                });
                return $this->fillBlock($data);

            case 'EMPLOYEES_APPROVAL_APPROVED':
                $data = array_filter($this->getData(), function ($act) {
                    return $act->getActivityType() === UserActivity::ACT_TYPE_REQUEST_APPROVED;
                });
                return $this->fillBlock($data);

            default:
                return null;
        }
    }
}
