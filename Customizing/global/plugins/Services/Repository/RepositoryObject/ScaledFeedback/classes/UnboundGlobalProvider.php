<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Mailing;
use \CaT\Plugins\ScaledFeedback\Mailing\MailContextScaledFeedback;

/**
 * Provide mailing-context for ScaledFeedback.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class UnboundGlobalProvider extends SeparatedUnboundProvider
{

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
            return [new MailContextScaledFeedback($entity, $this->owner())];
        }
        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }

    /**
     * Create an UnboundGlobalProvider in context of refId=1 (root)
     * @return void
     */
    public static function createGlobalProvider()
    {
        $provider_db = self::getDB();
        $provider = self::getGlobalProvider();

        if (!$provider) {
            $dummy_obj = new \ilObject();
            $dummy_obj->setId(1);
            $provider_db->createSeparatedUnboundProvider(
                $dummy_obj,
                "root",
                UnboundGlobalProvider::class,
                'Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/classes/UnboundGlobalProvider.php'
            );
        }
    }

    /**
     * get the ente-provider-db
     * @return \CaT\Ente\ILIAS\ilProviderDB
     */
    private static function getDB()
    {
        global $DIC;
        return $DIC["ente.provider_db"];
    }

    /**
     * Delete the global unbound provider
     * @return void
     */
    public static function deleteGlobalProvider()
    {
        $provider_db = self::getDB();
        $provider = self::getGlobalProvider();
        if ($provider) {
            $dummy_obj = new \ilObject();
            $dummy_obj->setId(1);
            $dummy_obj->setRefId(1);
            $provider_db->delete($provider, $dummy_obj);
        }
    }

    /**
     * get the unbound provider from glolbal scope (refid=1)
     * @return UnboundProvider | null
     */
    public static function getGlobalProvider()
    {
        $provider_db = self::getDB();
        $cmp_type = 'ILIAS\TMS\Mailing\MailContext';

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
}
