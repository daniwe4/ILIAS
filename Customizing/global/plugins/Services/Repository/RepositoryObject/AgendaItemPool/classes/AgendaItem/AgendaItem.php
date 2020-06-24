<?php declare(strict_types=1);

namespace CaT\Plugins\AgendaItemPool\AgendaItem;

use CaT\Plugins\AgendaItemPool\Helper;

/**
 * Class AgendaItem.
 *
 * An AgendaItem is a piece of an Agenda.
 * So you can manage the Agenda by sorting the AgendaItems.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class AgendaItem
{
    use Helper;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var bool
     */
    protected $is_active;

    /**
     * @var bool
     */
    protected $idd_relevant;

    /**
     * @var bool
     */
    protected $is_deleted;

    /**
     * @var DateTime
     */
    protected $last_change;

    /**
     * @var DateTime
     */
    protected $change_usr_id;

    /**
     * @var int|null
     */
    protected $pool_id;

    /**
     * @var bool
     */
    protected $is_blank;

    /**
     * @var int[] | null
     */
    protected $training_topics;

    /**
     * @var int[] | null
     */
    protected $goals;

    /**
     * @var string
     */
    protected $gdv_learning_content;

    /**
     * @var string
     */
    protected $idd_learning_content;

    /**
     * @var string
     */
    protected $agenda_item_content;

    /**
     * Constructor of the class AgendaItem.
     *
     * @param 	int 			$obj_id
     * @param 	string 			$title
     * @param 	string|null		$description
     * @param 	bool 			$is_active
     * @param 	bool 			$idd_relevant
     * @param 	bool 			$is_deleted
     * @param 	DateTime 		$last_change
     * @param 	int 			$change_usr_id
     * @param 	int|null		$pool_id
     * @param 	bool 			$is_blank
     * @param 	int[]|null		$training_topics
     * @param 	string|null		$goals
     * @param 	string			$gdv_learning_content
     * @param 	string			$idd_learning_content
     * @param 	string|null		$agenda_item_content
     * @return 	void
     */
    public function __construct(
        int $obj_id,
        string $title,
        string $description,
        bool $is_active,
        bool $idd_relevant,
        bool $is_deleted,
        \DateTime $last_change,
        int $change_usr_id,
        int $pool_id,
        bool $is_blank,
        array $training_topics,
        string $goals,
        string $gdv_learning_content,
        string $idd_learning_content,
        string $agenda_item_content
    ) {
        $this->obj_id = $obj_id;
        $this->title = $title;
        $this->description = $description;
        $this->is_active = $is_active;
        $this->idd_relevant = $idd_relevant;
        $this->is_deleted = $is_deleted;
        $this->last_change = $last_change;
        $this->change_usr_id = $change_usr_id;
        $this->pool_id = $pool_id;
        $this->is_blank = $is_blank;
        $this->training_topics = $training_topics;
        $this->goals = $goals;
        $this->gdv_learning_content = $gdv_learning_content;
        $this->idd_learning_content = $idd_learning_content;
        $this->agenda_item_content = $agenda_item_content;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * Set title with $value
     * @return 	self
     */
    public function withTitle(string $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->title = $value;
        return $clone;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * Set description with $value
     * @return 	self
     */
    public function withDescription(string $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->description = $value;
        return $clone;
    }

    public function getIsActive() : bool
    {
        return $this->is_active;
    }

    /**
     * Set is_active with $value
     * @return 	self
     */
    public function withIsActive(bool $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->is_active = $value;
        return $clone;
    }

    public function getIddRelevant() : bool
    {
        return $this->idd_relevant;
    }

    /**
     * Set idd_relevant with $value
     * @return 	self
     */
    public function withIddRelevant(bool $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->idd_relevant = $value;
        return $clone;
    }

    public function getIsDeleted() : bool
    {
        return $this->is_deleted;
    }

    /**
     * Set is_deleted with $value
     * @return 	self
     */
    public function withIsDeleted(bool $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->is_deleted = $value;
        return $clone;
    }

    /**
     * @return 	\DateTime
     */
    public function getLastChange()
    {
        return $this->last_change;
    }

    /**
     * Set last_change with $value
     * @return 	self
     */
    public function withLastChange(\DateTime $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->last_change = $value;
        return $clone;
    }

    public function getChangeUsrId() : int
    {
        return $this->change_usr_id;
    }

    /**
     * Set change_usr_id with $value
     * @return 	self
     */
    public function withChangeUsrId(int $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->change_usr_id = $value;
        return $clone;
    }

    public function getPoolId() : int
    {
        return $this->pool_id;
    }

    /**
     * Set pool_id with $value
     * @return 	self
     */
    public function withPoolId(int $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->pool_id = $value;
        return $clone;
    }

    public function getIsBlank() : bool
    {
        return $this->is_blank;
    }

    /**
     * Set is_blank with $value
     * @return 	self
     */
    public function withIsBlank(bool $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->is_blank = $value;
        return $clone;
    }

    public function getTrainingTopics() : array
    {
        return $this->training_topics;
    }

    /**
     * Get clone of this with new training_topics
     * @return 	self
     */
    public function withTrainingTopics(array $training_topics = null) : AgendaItem
    {
        assert($this->checkIntArray($training_topics));
        $clone = clone $this;
        $clone->training_topics = $training_topics;
        return $clone;
    }

    public function getGoals() : string
    {
        return $this->goals;
    }

    public function withGoals($value) : AgendaItem
    {
        $clone = clone $this;
        $clone->goals = $value;
        return $clone;
    }

    public function getGDVLearningContent() : string
    {
        return $this->gdv_learning_content;
    }

    /**
     * Set gdv_learning_content with $value
     * @return 	self
     */
    public function withGDVLearningContent(string $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->gdv_learning_content = $value;
        return $clone;
    }

    public function getIDDLearningContent() : string
    {
        return $this->idd_learning_content;
    }

    /**
     * Set idd_learning_content with $value
     * @return 	self
     */
    public function withIDDLearningContent(string $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->idd_learning_content = $value;
        return $clone;
    }

    public function getAgendaItemContent() : string
    {
        return $this->agenda_item_content;
    }

    /**
     * Set agenda_item_content with $value
     * @return 	self
     */
    public function withAgendaItemContent(string $value) : AgendaItem
    {
        $clone = clone $this;
        $clone->agenda_item_content = $value;
        return $clone;
    }
}
