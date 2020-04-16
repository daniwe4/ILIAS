<?php

/* Copyright (c) 2019 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types = 1);

namespace CaT\Plugins\CourseMember\SignatureList\ConfigurableList;

class IliasAvailableFields implements AvailableFields
{
    public function __construct(
        \ilExportFieldsInfo $efi,
        \ilUserDefinedFields $udf,
        \ilPrivacySettings $ps,
        \ilLanguage $lng
    ) {
        $this->efi = clone $efi;
        $efi->sortExportFields();
        $this->udf = $udf;
        $this->ps = $ps;
        $this->lng = $lng;
    }

    public function getStandardFields() : array
    {
        $return = [];
        $return['name'] = $this->lng->txt('name');
        $return['login'] = $this->lng->txt('login');
        foreach ($this->efi->getExportableFields() as $key) {
            if ($key === 'username' ||
                $key === 'firstname' ||
                $key === 'lastname'
            ) {
                continue;
            }
            $return[$key] = $this->lng->txt($key);
        }
        return $return;
    }

    public function getLpFields() : array
    {
        $return = [];
        if ($this->ps->enabledCourseAccessTimes()) {
            $return['access'] = $this->lng->txt('last_access');
        }
        $return['status'] = $this->lng->txt('crs_status');
        $return['passed'] = $this->lng->txt('crs_passed');
        return $return;
    }

    public function getUdfFields() : array
    {
        $return = [];
        $exportable = $this->udf->getCourseExportableFields();
        foreach ($exportable as $field_id => $udf_data) {
            $return['udf_' . $field_id] = $udf_data['field_name'];
        }
        return $return;
    }

    public function getRoles() : array
    {
        return [
            'role_adm' => $this->lng->txt('event_tbl_admin'),
            'role_tut' => $this->lng->txt('event_tbl_tutor'),
            'role_mem' => $this->lng->txt('event_tbl_member'),
            'request' => $this->lng->txt('event_user_selection_include_requests'),
            'waiting' => $this->lng->txt('event_user_selection_include_waiting_list')
        ];
    }
}
