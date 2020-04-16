<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

use CaT\Plugins\ParticipationsImport\Filesystem\ConfigStorage;
use CaT\Plugins\ParticipationsImport\IliasUtils\CourseUtils;

class CSVCourseMapping implements CourseMapping
{
    const ID_RELATIONS_FILE = 'id_relations.csv';
    const DELIMITER = ';';

    public static $columns = ['extern_id', 'crs_id'];

    protected $crs_id_tip = 0;

    protected $store = null;

    public function __construct(
        ConfigStorage $cs,
        CourseUtils $cu
    ) {
        $this->cu = $cu;
        $this->cs = $cs;
    }

    public function iliasCrsIdForExternCrsId(string $extern_id, bool $generate_new = true) : int
    {
        if (!$this->store) {
            $this->store = $this->loadStore($this->cs->loadCurrentConfig()->path());
        }
        $n_extern_id = $this->normalizeExternId($extern_id);
        if (!array_key_exists($n_extern_id, $this->store)) {
            if ($generate_new) {
                $this->store[$n_extern_id] = $this->createNewCourseId($extern_id);
            } else {
                return self::NO_MAPPING_FOUND_INT;
            }
        }
        return $this->store[$n_extern_id];
    }

    protected function normalizeExternId(string $extern_id) : string
    {
        return '_' . $extern_id;
    }

    protected function loadStore(string $path) : array
    {
        if (!is_readable($path) || !is_dir($path)) {
            throw new \Exception('may not write into ' . $id_relations_folder);
        }
        $id_relations_path = $path . DIRECTORY_SEPARATOR . self::ID_RELATIONS_FILE;
        if (!is_file($id_relations_path)) {
            $this->createIdRelationsFile($id_relations_path);
        }

        $this->id_relations_ressource = fopen($id_relations_path, 'r+');
        return $this->readIdRelationsFile($this->id_relations_ressource);
    }

    protected function readIdRelationsFile($id_relations_ressource) : array
    {
        $head = fgetcsv($id_relations_ressource, 0, self::DELIMITER);
        if ($head !== self::$columns) {
            throw new \Exception('inproper format');
        }
        $return = [];
        while ($rec = fgetcsv($id_relations_ressource, 0, self::DELIMITER)) {
            $return[$this->normalizeExternId($rec[0])] = (int) $rec[1];
            if ((int) $rec[1] < $this->crs_id_tip) {
                $this->crs_id_tip = (int) $rec[1];
            }
        }
        return $return;
    }

    protected function createIdRelationsFile(string $id_relations_path)
    {
        $res = fopen($id_relations_path, 'a');
        fputcsv($res, self::$columns, self::DELIMITER);
        fclose($res);
    }

    protected function createNewCourseId(string $extern_id) : int
    {
        do {
            $this->crs_id_tip--;
        } while ($this->cu->courseIdExists($this->crs_id_tip));
        $this->insertByRessource($this->id_relations_ressource, $extern_id, $this->crs_id_tip);
        return $this->crs_id_tip;
    }

    protected function insertByRessource($id_relations_ressource, string $extern_id, int $ilias_id)
    {
        fputcsv($id_relations_ressource, [$extern_id,$ilias_id], self::DELIMITER);
    }

    public function __destruct()
    {
        if ($this->id_relations_ressource) {
            fclose($this->id_relations_ressource);
        }
    }
}
