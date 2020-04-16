<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\General;

use CaT\Plugins\Venues\Venues\ConfigFormHelper;
use CaT\Plugins\Venues\Venues\Venue;
use CaT\Plugins\Venues\Tags;
use CaT\Plugins\Venues\ilActions;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * Assistant class for venu edit gui
 *
 * @author Stefan Hecken 	<stefan.heclen@concepts-and-training.de>
 */
class FormHelper implements ConfigFormHelper
{
    const F_NAME = "name";
    const F_OLD_NAME = "old_name";
    const F_HOMEPAGE = "homepage";
    const F_TAGS = "tags";
    const F_SEARCH_TAGS = "search_atgs";

    const NAME_LENGTH = 5;

    const HTTP_REGEXP = "/^(https:\/\/)|(http:\/\/)[\w]+/";

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilActions $actions,
        \Closure $txt,
        Tags\Venue\DB $tags_db,
        Tags\Search\DB $search_tags_db
    ) {
        $this->actions = $actions;
        $this->txt = $txt;
        $this->tags_db = $tags_db;
        $this->search_tags_db = $search_tags_db;
    }

    /**
     * @inheritdoc
     */
    public function addFormItems(\ilPropertyFormGUI $form)
    {
        $sh = new \ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("sh_overall"));
        $form->addItem($sh);

        $ti = new \ilTextInputGUI($this->txt("name"), self::F_NAME);
        $ti->setInfo(sprintf($this->txt("name_info"), self::NAME_LENGTH));
        $ti->setRequired(true);
        $form->addItem($ti);

        $msi = new \ilMultiSelectInputGUI($this->txt("tags"), self::F_TAGS);
        $msi->setOptions($this->getTagOptions());
        $msi->setWidthUnit("%");
        $msi->setWidth(100);
        $form->addItem($msi);

        $msi = new \ilMultiSelectInputGUI($this->txt("tags_search"), self::F_SEARCH_TAGS);
        $msi->setOptions($this->getSearchTagOptions());
        $msi->setWidthUnit("%");
        $msi->setWidth(100);
        $form->addItem($msi);

        $ti = new \ilTextInputGUI($this->txt("homepage"), self::F_HOMEPAGE);
        $ti->setMaxLength(255);
        $form->addItem($ti);

        $hi = new \ilHiddenInputGUI(self::F_OLD_NAME);
        $form->addItem($hi);
    }

    /**
     * @inheritdoc
     */
    public function createObject(int $venue_id, array $post)
    {
        $homepage = trim($post[self::F_HOMEPAGE]);
        if ($homepage != "" && preg_match(self::HTTP_REGEXP, $homepage) !== 1) {
            $homepage = "https://" . $homepage;
        }

        $tags = $this->tags_db->selectForIds($post[self::F_TAGS]);
        $search_tags = $this->search_tags_db->selectForIds($post[self::F_TAGS]);

        $this->actions->createGeneralObject(
            $venue_id,
            $post[self::F_NAME],
            $homepage,
            $tags,
            $search_tags
        );
    }

    /**
     * @inheritdoc
     */
    public function getObject(int $venue_id, array $post)
    {
        $homepage = trim($post[self::F_HOMEPAGE]);
        if ($homepage != "" && preg_match(self::HTTP_REGEXP, $homepage) !== 1) {
            $homepage = "https://" . $homepage;
        }

        $tags = $this->tags_db->selectForIds($post[self::F_TAGS]);
        $search_tags = $this->search_tags_db->selectForIds($post[self::F_SEARCH_TAGS]);

        return $this->actions->getGeneralObject(
            $venue_id,
            $post[self::F_NAME],
            $homepage,
            $tags,
            $search_tags
        );
    }

    /**
     * @inheritdoc
     */
    public function addValues(array &$values, Venue $venue)
    {
        $values[self::F_NAME] = $venue->getGeneral()->getName();
        $values[self::F_OLD_NAME] = $venue->getGeneral()->getName();
        $values[self::F_HOMEPAGE] = $venue->getGeneral()->getHomepage();
        $values[self::F_TAGS] = $this->getIdsOfAllocatedTag($venue->getGeneral()->getTags());
        $values[self::F_SEARCH_TAGS] = $this->getIdsOfAllocatedTag($venue->getGeneral()->getSearchTags());
    }

    /**
     * @inheritdoc
     */
    public function checkValues(\ilPropertyFormGUI $form, array $post)
    {
        $ret = true;
        $name = trim($post[self::F_NAME]);

        if (strlen($name) < self::NAME_LENGTH) {
            $gui = $form->getItemByPostVar(self::F_NAME);
            $gui->setAlert($this->txt("venue_name_short"));
            $ret = false;
        }

        return $ret;
    }

    /**
     * Check he name exists
     *
     * @param \ilPropertyFormGUI 	$form
     * @param string[] 	$post
     *
     * @return bool
     */
    public function checkName(\ilPropertyFormGUI $form, array $post)
    {
        $name = $post[self::F_NAME];

        if ($this->actions->venueNameExist($name)) {
            $gui = $form->getItemByPostVar(self::F_NAME);
            $gui->setAlert($this->txt("venue_name_exists"));

            return true;
        }

        return false;
    }

    /**
     * Check name is not changed and valis
     *
     * @param \ilPropertyFormGUI 	$form
     * @param string[] 	$post
     *
     * @return bool
     */
    public function checkNameChanged(\ilPropertyFormGUI $form, array $post)
    {
        $name = $post[self::F_NAME];
        $old_name = $post[self::F_OLD_NAME];

        $cmp = strcmp($name, $old_name);

        if ($cmp != 0 && $this->actions->venueNameExist($name)) {
            $gui = $form->getItemByPostVar(self::F_NAME);
            $gui->setAlert($this->txt("vendor_name_exists"));
            return true;
        }

        return false;
    }

    /**
     * @return string[]
     */
    protected function getTagOptions() : array
    {
        $tag_options = array();
        foreach ($this->tags_db->getTagsRaw() as $key => $tag) {
            $tag_options[$tag["id"]] = $tag["name"];
        }
        return $tag_options;
    }

    /**
     * @return string[]
     */
    protected function getSearchTagOptions() : array
    {
        $tag_options = array();
        foreach ($this->search_tags_db->getTagsRaw() as $key => $tag) {
            $tag_options[$tag["id"]] = $tag["name"];
        }
        return $tag_options;
    }

    /**
     * @param Tag[] 	$tags
     *
     * @return int[]
     */
    protected function getIdsOfAllocatedTag(array $tags) : array
    {
        return array_map(
            function ($t) {
                return $t->getId();
            },
            $tags
        );
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
