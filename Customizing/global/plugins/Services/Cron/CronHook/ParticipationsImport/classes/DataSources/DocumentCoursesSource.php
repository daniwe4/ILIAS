<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataSources;

use CaT\Plugins\ParticipationsImport\Data\DataExtractor;
use CaT\Plugins\ParticipationsImport\Filesystem\Locator as Locator;

class DocumentCoursesSource implements CoursesSource
{
    const COL_EXTERN_CRS_ID = 'extern_crs_id';
    const COL_CRS_TITLE = 'title';
    const COL_CRS_BEGIN_DATE = 'begin_date';
    const COL_CRS_END_DATE = 'end_date';
    const COL_CRS_TYPE = 'crs_type';
    const COL_IDD = 'idd';
    const COL_CRS_PROVIDER = 'crs_provider';
    const COL_CRS_VENUE = 'crs_venue';

    public function __construct(
        ConfigStorage $cs,
        DataExtractor $extractor,
        Locator $locator
    ) {
        $this->cs = $cs;
        $this->extractor = $extractor;
        $this->locator = $locator;
    }

    public function getCourses() : \Generator
    {
        $config = $this->cs->loadCurrentConfig();
        $mapping = $this->getMapping($config);
        $content = $this->extractor->extractContent(
            $this->locator->getCurrentFilePath(),
            $this->getMapping($config)
        );
        while ($row = array_shift($content)) {
            yield $this->courseByRow($row, $config);
        }
    }

    protected function courseByRow(array $row, Config $config) : Course
    {
        $row = $this->postprocessRow($row, $config);
        return new Course(
            $row[self::COL_CRS_TITLE],
            $row[self::COL_EXTERN_CRS_ID],
            $row[self::COL_CRS_TYPE],
            $row[self::COL_CRS_BEGIN_DATE],
            $row[self::COL_CRS_END_DATE],
            $row[self::COL_IDD],
            $row[self::COL_CRS_PROVIDER],
            $row[self::COL_CRS_VENUE]
        );
    }

    protected function postprocessRow(array $row, Config $config) : array
    {
        $return = [];
        $return[self::COL_EXTERN_CRS_ID] = $row[self::COL_EXTERN_CRS_ID];

        if (!array_key_exists(self::COL_CRS_TYPE, $row) || trim($row[self::COL_CRS_TYPE]) === '') {
            $return[self::COL_CRS_TYPE] = $config->crsTypeDefault();
        } else {
            $return[self::COL_CRS_TYPE] = $row[self::COL_CRS_TYPE];
        }
        if (!array_key_exists(self::COL_CRS_TITLE, $row) || trim($row[self::COL_CRS_TITLE]) === '') {
            $return[self::COL_CRS_TITLE] = $config->crsTitleDefault();
        } else {
            $return[self::COL_CRS_TITLE] = $row[self::COL_CRS_TITLE];
        }
        if (!array_key_exists(self::COL_IDD, $row) || trim((string) $row[self::COL_IDD]) === '') {
            $return[self::COL_IDD] = $config->crsIddDefault();
        } else {
            $return[self::COL_IDD] = (int) $row[self::COL_IDD];
        }


        if (!array_key_exists(self::COL_CRS_BEGIN_DATE, $row) ||
            trim($row[self::COL_CRS_BEGIN_DATE]) === '') {
            if (!array_key_exists(self::COL_CRS_END_DATE, $row) ||
                trim($row[self::COL_CRS_END_DATE]) === '') {
                $return[self::COL_CRS_BEGIN_DATE] = null;
            } else {
                $return[self::COL_CRS_BEGIN_DATE] = \DateTime::createFromFormat(
                    'Y-m-d',
                    trim((string) $row[self::COL_CRS_END_DATE])
                );
            }
        } else {
            $return[self::COL_CRS_BEGIN_DATE] = \DateTime::createFromFormat(
                'Y-m-d',
                trim((string) $row[self::COL_CRS_BEGIN_DATE])
            );
        }
        if (!array_key_exists(self::COL_CRS_END_DATE, $row) ||
            trim($row[self::COL_CRS_END_DATE]) === '') {
            if (!array_key_exists(self::COL_CRS_BEGIN_DATE, $row) ||
                trim($row[self::COL_CRS_BEGIN_DATE]) === '') {
                $return[self::COL_CRS_END_DATE] = null;
            } else {
                $return[self::COL_CRS_END_DATE] = \DateTime::createFromFormat(
                    'Y-m-d',
                    trim((string) $row[self::COL_CRS_BEGIN_DATE])
                );
            }
        } else {
            $return[self::COL_CRS_END_DATE] = \DateTime::createFromFormat(
                'Y-m-d',
                trim((string) $row[self::COL_CRS_END_DATE])
            );
        }
        if (!array_key_exists(self::COL_CRS_PROVIDER, $row) || trim((string) $row[self::COL_CRS_PROVIDER]) === '') {
            $return[self::COL_CRS_PROVIDER] = $config->crsProviderDefault();
        } else {
            $return[self::COL_CRS_PROVIDER] = (string) $row[self::COL_CRS_PROVIDER];
        }
        if (!array_key_exists(self::COL_CRS_VENUE, $row) || trim((string) $row[self::COL_CRS_VENUE]) === '') {
            $return[self::COL_CRS_VENUE] = $config->crsVenueDefault();
        } else {
            $return[self::COL_CRS_VENUE] = (string) $row[self::COL_CRS_VENUE];
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
        if ($config->crsTitleColTitle() !== '') {
            $mapping[$config->crsTitleColTitle()] = self::COL_CRS_TITLE;
        }
        if ($config->crsTypeColTitle() !== '') {
            $mapping[$config->crsTypeColTitle()] = self::COL_CRS_TYPE;
        }
        if ($config->crsBeginDateColTitle() !== '') {
            $mapping[$config->crsBeginDateColTitle()] = self::COL_CRS_BEGIN_DATE;
        }
        if ($config->crsEndDateColTitle() !== '') {
            $mapping[$config->crsEndDateColTitle()] = self::COL_CRS_END_DATE;
        }
        if ($config->crsIddColTitle() !== '') {
            $mapping[$config->crsIddColTitle()] = self::COL_IDD;
        }
        if ($config->crsProviderColTitle() !== '') {
            $mapping[$config->crsProviderColTitle()] = self::COL_CRS_PROVIDER;
        }
        if ($config->crsVenueColTitle() !== '') {
            $mapping[$config->crsVenueColTitle()] = self::COL_CRS_VENUE;
        }
        return $mapping;
    }
}
