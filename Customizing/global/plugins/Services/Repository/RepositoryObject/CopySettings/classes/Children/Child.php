<?php

namespace CaT\Plugins\CopySettings\Children;

/**
 * Immutable class for copy settings of each child within the container
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Child
{
    const COPY = "copy";
    const REFERENCE = "reference";
    const NOTHING = "nothing";

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $target_ref_id;

    /**
     * @var int
     */
    protected $target_obj_id;

    /**
     * @var bool
     */
    protected $is_referenced;

    /**
     * @var string
     */
    protected $process_type;

    /**
     * @param int 	$obj_id
     * @param int 	$target_ref_id
     * @param int 	$target_obj_id
     * @param bool 	$is_referenced
     * @param string 	$process_type
     */
    public function __construct($obj_id, $target_ref_id, $target_obj_id, $is_referenced, $process_type)
    {
        assert('is_int($obj_id)');
        assert('is_int($target_ref_id)');
        assert('is_int($target_obj_id)');
        assert('is_bool($is_referenced)');
        assert('is_string($process_type) && $this->validProcess($process_type)');

        $this->obj_id = $obj_id;
        $this->target_ref_id = $target_ref_id;
        $this->target_obj_id = $target_obj_id;
        $this->is_referenced = $is_referenced;
        $this->process_type = $process_type;
    }

    /**
     * Get the obj id
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Get the target ref id
     *
     * @return int
     */
    public function getTargetRefId()
    {
        return $this->target_ref_id;
    }

    /**
     * Get the target obj id
     *
     * @return int
     */
    public function getTargetObjId()
    {
        return $this->target_obj_id;
    }

    /**
     * Get the target is a reference
     *
     * @return bool
     */
    public function isReferenced()
    {
        return $this->is_referenced;
    }

    /**
     * Get the process type
     *
     * @return string
     */
    public function getProcessType()
    {
        return $this->process_type;
    }

    /**
     * Get clone of this with target ref_id
     *
     * @param int 	$target_ref_id
     *
     * @return $this
     */
    public function withTargetRefId($target_ref_id)
    {
        assert('is_int($target_ref_id)');
        $clone = clone $this;
        $clone->target_ref_id = $target_ref_id;
        return $clone;
    }

    /**
     * Get clone of this with target obj_id
     *
     * @param int 	$target_obj_id
     *
     * @return $this
     */
    public function withTargetObjId($target_obj_id)
    {
        assert('is_int($target_obj_id)');
        $clone = clone $this;
        $clone->target_obj_id = $target_obj_id;
        return $clone;
    }

    /**
     * Get clone of this with is referenced
     *
     * @param int 	$is_referenced
     *
     * @return $this
     */
    public function withIsReferenced($is_referenced)
    {
        assert('is_bool($is_referenced)');
        $clone = clone $this;
        $clone->is_referenced = $is_referenced;
        return $clone;
    }

    /**
     * Get clone of this with process type
     *
     * @param int 	$process_type
     *
     * @return $this
     */
    public function withProcessType($process_type)
    {
        assert('is_string($process_type) && $this->validProcess($process_type)');
        $clone = clone $this;
        $clone->process_type = $process_type;
        return $clone;
    }

    /**
     * Checks the given process is valid
     *
     * @param string 	$process_type
     *
     * @return bool
     */
    protected function validProcess($process_type)
    {
        return in_array(
            $process_type,
            array(self::COPY,
                        self::REFERENCE,
                        self::NOTHING
                    )
        );
    }
}
