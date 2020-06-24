<?php
namespace CaT\Plugins\AgendaItemPool\Options;

/**
 * Base class for options in AgendaItemPool.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class Option
{
    /**
     * @var int
     */
    protected $agenda_item_id;

    /**
     * @var int
     */
    protected $caption_id;

    /**
     * Constructor of Option
     *
     * @param int 		$agenda_item_id
     * @param string 	$caption
     */
    public function __construct(int $agenda_item_id, int $caption_id)
    {
        $this->agenda_item_id = $agenda_item_id;
        $this->caption_id = $caption_id;
    }

    /**
     * Get agenda_item_id.
     *
     * @return 	int
     */
    public function getAgendaItemId()
    {
        return $this->agenda_item_id;
    }

    /**
     * Get the caption.
     *
     * @return int
     */
    public function getCaptionId()
    {
        return $this->caption_id;
    }
}
