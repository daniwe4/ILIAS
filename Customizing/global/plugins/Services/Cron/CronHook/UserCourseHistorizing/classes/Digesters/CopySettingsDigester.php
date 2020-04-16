<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class CopySettingsDigester implements Digester
{
    public function digest(array $payload)
    {
        $crs = $payload['parent'];
        switch ($payload['type']) {
            case 'createCopySettings':
            case 'updateCopySettings':
                $is_template = true;
                break;
            case 'deleteCopySettings':
                $is_template = false;
                $sub_items = $crs->getSubItems();

                if (!is_array($sub_items)) {
                    break;
                }

                if (array_key_exists('xcps', $sub_items) && is_array($sub_items['xcps'])) {
                    $xcps = $sub_items['xcps'];
                    $del_ref_id = $payload['ref_id'];
                    $xcps = array_filter(
                        $xcps,
                        function ($item) use ($del_ref_id) {
                            return $item["child"] != $del_ref_id;
                        }
                    );

                    if (count($xcps) > 0) {
                        $is_template = true;
                    }
                }
                break;
        }
        return ['is_template' => $is_template,
                'crs_id' => (int) $crs->getId()];
    }
}
