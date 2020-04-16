<?php

namespace CaT\Plugins\EduTracking\Purposes\WBD;

class IliasWBDUserDataProvider implements WBDUserDataProvider
{
    /**
     * @inheritdoc
     */
    public function getUserInformation($user_id)
    {
        if (\ilObject::_lookupType($user_id) !== 'usr') {
            return ['','','','',''];
        }
        $user = \ilObjectFactory::getInstanceByObjId($user_id);
        return [
            $user->getUTitle(),
            $user->getFirstname(),
            $user->getLastname(),
            $user->getPhoneOffice(),
            $user->getEmail()
        ];
    }
}
