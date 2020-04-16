<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataSources;

use CaT\Plugins\ParticipationsImport\Filesystem\Locator;
use CaT\Plugins\ParticipationsImport\Data\DataExtractor;

class DocumentParticipationsSource implements ParticipationsSource
{
    const COL_EXTERN_CRS_ID = 'extern_crs_id';
    const COL_EXTERN_USR_ID = 'extern_usr_id';
    const COL_PARTICIPATION_STATUS = 'participation_status';
    const COL_BOOKING_STATUS = 'booking_status';
    const COL_BOOKING_DATE = 'booking_date';
    const COL_PARTICIPATION_DATE = 'participation_date';
    const COL_IDD = 'idd';

    public function __construct(
        ConfigStorage $cs,
        DataExtractor $extractor,
        Locator $locator
    ) {
        $this->cs = $cs;
        $this->extractor = $extractor;
        $this->locator = $locator;
    }

    public function getParticipations() : \Generator
    {
        $config = $this->cs->loadCurrentConfig();
        $content = $this->extractor->extractContent(
            $this->locator->getCurrentFilePath(),
            $this->getMapping($config)
        );
        while ($row = array_shift($content)) {
            yield $this->participationByRow($row, $config);
        }
    }

    protected function participationByRow(array $row, Config $config) : Participation
    {
        $row = $this->postprocessRow($row, $config);
        return new Participation(
            $row[self::COL_EXTERN_CRS_ID],
            $row[self::COL_EXTERN_USR_ID],
            $row[self::COL_BOOKING_STATUS],
            $row[self::COL_PARTICIPATION_STATUS],
            $row[self::COL_BOOKING_DATE],
            $row[self::COL_PARTICIPATION_DATE],
            $row[self::COL_IDD]
        );
    }

    protected function postprocessRow(array $row, Config $config) : array
    {
        $return = [];
        $return[self::COL_EXTERN_CRS_ID] = (string) $row[self::COL_EXTERN_CRS_ID];
        $return[self::COL_EXTERN_USR_ID] = (string) $row[self::COL_EXTERN_USR_ID];
        if (!array_key_exists(self::COL_PARTICIPATION_STATUS, $row) ||
            trim($row[self::COL_PARTICIPATION_STATUS]) === '') {
            $return[self::COL_PARTICIPATION_STATUS] = $config->participationStatusDefault();
        } else {
            $return[self::COL_PARTICIPATION_STATUS] = $row[self::COL_PARTICIPATION_STATUS];
        }
        if (!array_key_exists(self::COL_BOOKING_STATUS, $row) ||
            trim($row[self::COL_BOOKING_STATUS]) === '') {
            $return[self::COL_BOOKING_STATUS] = $config->bookingStatusDefault();
        } else {
            $return[self::COL_BOOKING_STATUS] = $row[self::COL_BOOKING_STATUS];
        }
        if (!array_key_exists(self::COL_IDD, $row) || trim((string) $row[self::COL_IDD]) === '') {
            $return[self::COL_IDD] = $config->participationIddDefault();
        } else {
            $return[self::COL_IDD] = (int) $row[self::COL_IDD];
        }

        $booking_date_swap = array_key_exists(self::COL_BOOKING_DATE, $row) ? trim($row[self::COL_BOOKING_DATE]) : null;
        $participation_date_swap = array_key_exists(self::COL_PARTICIPATION_DATE, $row) ? trim($row[self::COL_PARTICIPATION_DATE]) : null;

        if (!$booking_date_swap) {
            if (!$participation_date_swap) {
                $return[self::COL_BOOKING_DATE] = null;
            } else {
                $return[self::COL_BOOKING_DATE] = \DateTime::createFromFormat(
                    'Y-m-d',
                    $participation_date_swap
                );
            }
        } else {
            $return[self::COL_BOOKING_DATE] = \DateTime::createFromFormat(
                'Y-m-d',
                $booking_date_swap
            );
        }
        if (!$participation_date_swap) {
            if (!$booking_date_swap) {
                $return[self::COL_PARTICIPATION_DATE] = null;
            } else {
                $return[self::COL_PARTICIPATION_DATE] = \DateTime::createFromFormat(
                    'Y-m-d',
                    $booking_date_swap
                );
            }
        } else {
            $return[self::COL_PARTICIPATION_DATE] = \DateTime::createFromFormat(
                'Y-m-d',
                $participation_date_swap
            );
        }
        return $return;
    }

    protected function getMapping(Config $config) : array
    {
        $mapping = [];
        if ($config->externCrsIdColTitle() !== '') {
            $mapping[$config->externCrsIdColTitle()] = self::COL_EXTERN_CRS_ID;
        } else {
            throw new InvalidConfigException('extern crs_id column undefined');
        }
        if ($config->externUsrIdColTitle() !== '') {
            $mapping[$config->externUsrIdColTitle()] = self::COL_EXTERN_USR_ID;
        } else {
            throw new InvalidConfigException('extern usr_id column undefined');
        }
        if ($config->participationStatusColTitle() !== '') {
            $mapping[$config->participationStatusColTitle()] = self::COL_PARTICIPATION_STATUS;
        }
        if ($config->bookingStatusColTitle() !== '') {
            $mapping[$config->bookingStatusColTitle()] = self::COL_BOOKING_STATUS;
        }
        if ($config->bookingDateColTitle() !== '') {
            $mapping[$config->bookingDateColTitle()] = self::COL_BOOKING_DATE;
        }
        if ($config->participationDateColTitle() !== '') {
            $mapping[$config->participationDateColTitle()] = self::COL_PARTICIPATION_DATE;
        }
        if ($config->participationIddColTitle() !== '') {
            $mapping[$config->participationIddColTitle()] = self::COL_IDD;
        }
        return $mapping;
    }
}
