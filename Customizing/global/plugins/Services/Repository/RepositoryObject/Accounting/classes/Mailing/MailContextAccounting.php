<?php
namespace CaT\Plugins\Accounting\Mailing;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextEnte.php');

/**
 * Provide placeholders in the context of accounting
 *
 * @author Stefan Hecken <stefan.heckenn@concepts-and-training.de>
 */
class MailContextAccounting extends \ilTMSMailContextEnte
{
    protected static $PLACEHOLDERS = array(
        'ACCOUNTING_FEE' => 'placeholder_desc_accounting_fee',
        'CANCELLATION_FEE' => 'placeholder_desc_cancellation_fee'
    );

    /**
     * @inheritdoc
     */
    public function placeholderIds()
    {
        return array_keys(self::$PLACEHOLDERS);
    }

    /**
     * @inheritdoc
     */
    public function placeholderDescriptionForId(string $placeholder_id) : string
    {
        $desc = self::$PLACEHOLDERS[$placeholder_id];
        $obj = \ilPluginAdmin::getPluginObjectById("xacc");
        return $obj->txt($desc);
    }

    /**
     * @inheritdoc
     */
    public function valueFor(string $placeholder_id, array $contexts = array()) : ?string
    {
        if (!in_array($placeholder_id, $this->placeholderIds())) {
            return null;
        }
        return $this->resolveValueFor($placeholder_id, $contexts);
    }

    /**
     * @param string $id
     * @param MailContexts[] $contexts
     * @return $string | null
     */
    protected function resolveValueFor($id, $contexts)
    {
        $actions = $this->owner()->getObjectActions();
        switch ($id) {
            case 'ACCOUNTING_FEE':
                $fee = $actions->getFeeActions()->select()->getFee();
                if (is_null($fee)) {
                    $fee = "-";
                } else {
                    $fee = number_format($fee, 2, ",", ".");
                }
                return $fee;
            case 'CANCELLATION_FEE':
                $plugin = $this->owner()->getPluginObject();
                $crs_id = $this->owner()->getParentCourse()->getId();
                $user_id = $this->getUserId($contexts);
                $cancellation_fee = "-";

                if ($user_id == 0) {
                    return $cancellation_fee;
                }

                $cancellation_fee = $plugin->getCancellationFeeFor($crs_id, $user_id);

                if (is_null($cancellation_fee)) {
                    return "-";
                }

                return number_format($cancellation_fee, 2, ",", ".") . ' €';
            default:
                return 'NOT RESOLVED: ' . $id;
        }
    }

    protected function getUserId(array $contexts) : int
    {
        $user_id = 0;
        foreach ($contexts as $context) {
            if (get_class($context) === 'ilTMSMailContextUser') {
                $user_id = (int) $context->getUsrId();
            }
        }

        return $user_id;
    }
}
