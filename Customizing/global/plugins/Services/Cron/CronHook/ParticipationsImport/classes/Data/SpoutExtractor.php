<?php

namespace CaT\Plugins\ParticipationsImport\Data;

use Box\Spout\Reader\AbstractReader;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

abstract class SpoutExtractor implements DataExtractor
{
    abstract protected function getRowIterator(string $path_to_file) : \Iterator;

    /**
     * @inheritdoc
     */
    public function extractContent(string $path_to_file, array $field_conversion, bool $no_header = false) : array
    {
        $it = $this->getRowIterator($path_to_file);


        $header = $it->current();
        $assignments = [];
        if (!$no_header) {
            foreach ($field_conversion as $current => $desired) {
                $location = array_search($current, $header);
                if ($location === false) {
                    throw new \InvalidArgumentException('can not locate field ' . $current . ' in file ' . $location);
                }
                $assignments[$desired] = $location;
            }
            $cnt = 0;
        } else {
            $location = 0;
            foreach ($field_conversion as $desired) {
                $assignments[$desired] = $location;
                $location++;
            }
            $cnt = 1;
        }

        $conversion_count = count($field_conversion);

        $return = [];
        $cnt_rows = 0;
        foreach ($it as $row) {
            $cnt_rows++;
            while (count($row) < $conversion_count) {
                $row[] = '';
            }
            if ($cnt === 0) {
                $cnt = 1;
                continue;
            }
            $aux = [];
            foreach ($assignments as $field_desired => $position) {
                $aux[$field_desired] = $row[$position];
            }
            if (!$this->rowEmpty($aux)) {
                $return[] = $this->stringify($aux);
            }
        }
        return $return;
    }

    protected function stringify(array $row)
    {
        $return = [];
        foreach ($row as $key => $value) {
            if ($value instanceof \DateTime) {
                $return[$key] = $value->format('Y-m-d');
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    protected function rowEmpty(array $row)
    {
        foreach ($row as $key => $value) {
            if (is_object($value)) {
                return false;
            }
            if (trim((string) $value) !== '') {
                return false;
            }
        }
        return true;
    }
}
