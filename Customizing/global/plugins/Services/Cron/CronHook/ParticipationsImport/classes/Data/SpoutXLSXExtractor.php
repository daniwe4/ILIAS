<?php

namespace CaT\Plugins\ParticipationsImport\Data;

use Box\Spout\Reader\AbstractReader;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

/**
 * Extract data from an xlsx-file
 */

class SpoutXLSXExtractor extends SpoutExtractor
{
    protected $sheet = 1;

    public function withSheet(int $sheet) : SpoutXLSXExtractor
    {
        $other = clone $this;
        $other->sheet = $sheet;
        return $other;
    }

    protected function getRowIterator(string $path_to_file) : \Iterator
    {
        $reader = $this->getReader();
        $reader->open($path_to_file);
        $it = $reader->getSheetIterator();
        $it->rewind();
        $cur_sheet = 1;
        while ($cur_sheet < $this->sheet) {
            $it->next();
            $cur_sheet++;
        }

        $it = $it->current()->getRowIterator();
        $it->rewind();
        return $it;
    }

    /**
     * @inheritdoc
     */
    protected function getReader() : AbstractReader
    {
        return ReaderFactory::create(Type::XLSX);
    }
}
