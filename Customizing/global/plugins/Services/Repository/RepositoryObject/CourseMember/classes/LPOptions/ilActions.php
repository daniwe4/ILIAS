<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseMember\LPOptions;

/**
 * Commuication class between back and front end
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilActions
{
    /**
     * @var \ilCourseMemberPlugin
     */
    protected $plugin;

    public function __construct(\ilCourseMemberPlugin $plugin, DB $lp_options_db)
    {
        $this->plugin = $plugin;
        $this->lp_options_db = $lp_options_db;
    }

    /**
     * Get the plugin object
     *
     * @throws \Exception 	if no plugin is set
     *
     * @return \ilCourseMemberPlugin
     */
    public function getPlugin()
    {
        if ($this->plugin === null) {
            throw new \Exception("No plugin set");
        }

        return $this->plugin;
    }

    /**
     * Creates a new lp option
     *
     * @param string 	$title
     * @param int | null	$ilias_lp
     * @param bool 	$active
     * @param bool 	$standard
     *
     * @return LPOption
     */
    public function create(string $title, ?int $ilias_lp, bool $active, bool $standard)
    {
        return $this->lp_options_db->create($title, $ilias_lp, $active, $standard);
    }

    /**
     * Updates an existing lp option
     *
     * @param LPOption
     *
     * @return void
     */
    public function update(LPOption $lp_option)
    {
        $this->lp_options_db->update($lp_option);
    }

    /**
     * Delete a lp option by id
     *
     * @param int 	$id
     *
     * @return void
     */
    public function delete(int $id)
    {
        $this->lp_options_db->delete($id);
    }

    /**
     * Get current defined lp options
     *
     * @return LPOption[]
     */
    public function getLPOptions()
    {
        return $this->lp_options_db->select();
    }

    /**
     * Get current aktive lp options as select input option array
     *
     * @return array<string, string>
     */
    public function getSelectInputOptions()
    {
        return $this->lp_options_db->select(true);
    }

    /**
     * Get an empty lp option
     *
     * @return LPOption
     */
    public function getEmptyLPOption($id)
    {
        return $this->lp_options_db->getEmptyLPOption($id);
    }

    /**
     * Get an filled lp option
     */
    public function getLPOptionWith(int $id, string $title, int $ilias_lp, bool $active, bool $standard) : LPOption
    {
        return $this->lp_options_db->getLPOptionWith($id, $title, $ilias_lp, $active, $standard);
    }

    /**
     * Get the title of lp option by id
     *
     * @param int 	$id
     *
     * @return string | null
     */
    public function getLPOptionTitleBy(int $id)
    {
        return $this->lp_options_db->getLPOptionTitleBy($id);
    }

    /**
     * Get the ilias lp by id
     *
     * @param int 	$id
     *
     * @return string | null
     */
    public function getILIASLPBy(int $id)
    {
        return $this->lp_options_db->getILIASLPBy($id);
    }
}
