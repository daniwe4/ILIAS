<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

abstract class Relation implements Mapping, \Iterator
{
    protected $relations = [];

    abstract protected function properValues() : array;

    public function addRelation(string $from, string $to)
    {
        if (!in_array($to, $this->properValues())) {
            throw new RelationException('invalid value ' . $to . ' to be assigned to ' . $from);
        }
        $this->relations[$from] = $to;
    }

    protected function getRelation(string $from) : string
    {
        if (!array_key_exists($from, $this->relations)) {
            return self::NO_MAPPING_FOUND_STRING;
        }
        return $this->relations[$from];
    }

    public function current()
    {
        return current($this->relations);
    }
    public function key()
    {
        return key($this->relations);
    }
    public function next()
    {
        next($this->relations);
    }
    public function rewind()
    {
        reset($this->relations);
    }
    public function valid()
    {
        return (bool) current($this->relations);
    }
}
