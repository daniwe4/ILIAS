<?php
namespace CaT\Plugins\AgendaItemPool\Settings;

/**
 * Class Settings.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class Settings
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var bool
     */
    protected $is_online;

    /**
     * @var \DateTime
     */
    protected $last_changed;

    /**
     * @var int
     */
    protected $last_changed_usr_id;

    /**
     * Constructor of the class Settings.
     *
     * @param 	int				$obj_id
     * @param 	bool			$is_online
     * @param 	\DateTime|null	$last_changed
     * @param 	int|null		$last_changed_usr_id
     */
    public function __construct(
        int $obj_id,
        bool $is_online,
        ?\DateTime $last_changed,
        ?int $last_changed_usr_id
    ) {
        $this->obj_id = $obj_id;
        $this->is_online = $is_online;
        $this->last_changed = $last_changed;
        $this->last_changed_usr_id = $last_changed_usr_id;
    }

    /**
     * Get obj_id
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Get is_online
     *
     * @return bool
     */
    public function getIsOnline()
    {
        return $this->is_online;
    }

    /**
     * Set is_online with $value
     *
     * @param 	bool	$value
     * @return 	self
     */
    public function withIsOnline(bool $value)
    {
        $clone = clone $this;
        $clone->is_online = $value;
        return $clone;
    }

    /**
     * Get last_changed
     *
     * @return \DateTime|null
     */
    public function getLastChanged()
    {
        return $this->last_changed;
    }

    /**
     * Set last_changed with $value
     *
     * @param 	\DateTime|null	$value
     * @return 	self
     */
    public function withLastChanged(?\DateTime $value)
    {
        $clone = clone $this;
        $clone->last_changed = $value;
        return $clone;
    }

    /**
     * Get last_changed_usr_id
     *
     * @return int|null
     */
    public function getLastChangedUsrId()
    {
        return $this->last_changed_usr_id;
    }

    /**
     * Set last_changed_usr_id with $value
     *
     * @param 	int|null	$value
     * @return 	self
     */
    public function withLastChangedUsrId(?int $value)
    {
        $clone = clone $this;
        $clone->last_changed_usr_id = $value;
        return $clone;
    }
}
