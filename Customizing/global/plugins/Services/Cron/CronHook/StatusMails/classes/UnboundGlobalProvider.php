<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \CaT\Ente\ILIAS\ilProviderDB;
use \ILIAS\TMS\Mailing;

/**
 * Provide Components in the context of root.
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
class UnboundGlobalProvider extends SeparatedUnboundProvider
{
    /**
     * Create an UnboundGlobalProvider in context of refId=1 (root)
     * @return void
     */
    public static function createGlobalProvider()
    {
        $provider_db = self::getProviderDB();
        $provider = self::getGlobalProvider();

        if (!$provider) {
            $dummy_obj = new \ilObject();
            $dummy_obj->setId(1);
            $provider_db->createSeparatedUnboundProvider(
                $dummy_obj,
                "root",
                UnboundGlobalProvider::class,
                'Customizing/global/plugins/Services/Cron/CronHook/StatusMails/classes/UnboundGlobalProvider.php'
            );
        }
    }

    private static function getProviderDB() : ilProviderDB
    {
        global $DIC;
        return $DIC["ente.provider_db"];
    }

    /**
     * get the unbound provider from glolbal scope (refid=1)
     * @return UnboundProvider | null
     */
    public static function getGlobalProvider()
    {
        $provider_db = self::getProviderDB();

        $dummy_obj = new \ilObject();
        $dummy_obj->setId(1);
        $dummy_obj->setRefId(1);

        $providers = $provider_db->unboundProvidersOf($dummy_obj);

        foreach ($providers as $provider) {
            if (get_class($provider) === UnboundGlobalProvider::class) {
                return $provider;
            }
        }
        return null;
    }

    /**
     * Delete the global unbound provider
     * @return \CaT\Ente\ILIAS\ilProviderDB
     */
    public static function deleteGlobalProvider()
    {
        $provider_db = self::getProviderDB();
        $provider = self::getGlobalProvider();
        if ($provider) {
            $dummy_obj = new \ilObject();
            $dummy_obj->setId(1);
            $dummy_obj->setRefId(1);
            $provider_db->delete($provider, $dummy_obj);
        }
    }

    /**
     * @inheritdoc
     */
    public function componentTypes()
    {
        return [Mailing\MailContext::class];
    }

    /**
     * @inheritdoc
     */
    public function buildComponentsOf($component_type, Entity $entity)
    {
        if ($component_type === Mailing\MailContext::class) {
            $plugin = \ilPluginAdmin::getPluginObjectById('statusmails');
            return $plugin->getContextsForGlobalProvider($entity, $this->owner());
        }
        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }
}
