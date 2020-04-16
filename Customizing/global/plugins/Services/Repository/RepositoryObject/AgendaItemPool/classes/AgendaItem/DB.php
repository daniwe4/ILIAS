<?php declare(strict_types=1);

namespace CaT\Plugins\AgendaItemPool\AgendaItem;

/**
 * Interface for DB handle of additional setting values
 */
interface DB
{
    /**
     * Update settings of an existing repo object.
     *
     * @param	AgendaItem		$item
     * @return 	void
     */
    public function update(AgendaItem $item);

    /**
     * Create a new settings object for AgendaItemPool object.
     * @return 	\CaT\Plugins\AgendaItemPool\Settings\AgendaItemPool
     */
    public function create(
        string $title,
        \DateTime $last_change,
        int $change_usr_id,
        int $pool_id,
        string $description,
        bool $is_active,
        bool $idd_relevant,
        bool $is_deleted,
        bool $is_blank,
        array $training_topics,
        string $goals,
        string $gdv_learning_content,
        string $idd_learning_content,
        string $agenda_item_content
    );

    /**
     * return AgendaItemPool for $obj_id
     * @return 	\CaT\Plugins\AgendaItemPool\Settings\AgendaItemPool
     */
    public function selectFor(int $obj_id);

    /**
     * Delete all information of the given obj id
     *
     * @param 	int[] 	$agenda_itme_ids
     * @return 	void
     */
    public function deleteFor(array $agenda_itme_ids);
}
