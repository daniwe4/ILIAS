<?php

namespace CaT\Plugins\Accounting;

use CaT\Plugins\Accounting;
use CaT\Plugins\Accounting\Data\Export;
use CaT\Plugins\Accounting\Config\CostType\CostType;
use CaT\Plugins\Accounting\Config\VatRate\VatRate;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilPluginActions
{
    const F_COST_TYPE_CHK = "costtype_chk";
    const F_VAT_RATE_CHK = "vat_rate_chk";

    const F_COSTTYPE_VALUE = "costtype_value";
    const F_COSTTYPE_LABEL = "costtype_label";
    const F_COSTTYPE_ID = "costtype_id";
    const F_COSTTYPE_ORG_NAME = "costtype_org_name";
    const F_DELETE_COSTTYPE_IDS = "to_delete_ids";
    const F_COSTTYPE_ACTIVE = "costtype_active";

    const F_VATRATE_VALUE = "vatrate_value";
    const F_VATRATE_LABEL = "vatrate_label";
    const F_VATRATE_ID = "vatrate_id";
    const F_VATRATE_ORG_NAME = "vatrate_org_name";
    const F_DELETE_VATRATE_IDS = "to_delete_ids";
    const F_VATRATE_ACTIVE = "vatrate_active";

    /**
     * @var Accounting\Config\CostType\DB
     */
    protected $costtype_db;

    /**
     * @var Accounting\Config\VatRate\DB
     */
    protected $vat_rate_db;

    /**
     * @var Accounting\ilObjAccounting
     */
    protected $object;

    /**
     * Constructor of the class ilObjectActions
     *
     * @param Accounting\Config\VatRate\ilDB 	$costtype_db
     * @param Accounting\Config\VatRate\ilDB 	$vat_rate_db
     */
    public function __construct(
        Accounting\Config\CostType\ilDB $costtype_db,
        Accounting\Config\VatRate\ilDB $vat_rate_db
    ) {
        $this->setCostTypeDB($costtype_db);
        $this->setVatRateDB($vat_rate_db);
    }

    /**
     * Get the plugin object
     *
     * @return \ilRoomSetupPlugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }


    /**********************************************************************************
     ***							Cost Type DB									***
     **********************************************************************************/
    /**
     * Set the CostType db
     *
     * @param Accounting\Config\CostType\DB 		$value
     */
    protected function setCostTypeDB(Accounting\Config\CostType\DB $value)
    {
        $this->costtype_db = $value;
    }

    /**
     * Insert CostType into db
     *
     * @param Accounting\Config\CostType\CostType 		$entry
     */
    public function insertCostType(Accounting\Config\CostType\CostType $entry)
    {
        return $this->costtype_db->insert($entry->getValue(), $entry->getLabel(), $entry->getActive());
    }

    /**
     * Update CostType
     *
     * @param Accounting\Congig\CostType\CostType 		$entry
     */
    public function updateCostType(Accounting\Config\CostType\CostType $entry)
    {
        $this->costtype_db->update($entry);
    }

    /**
     * Get all datasets from db
     *
     * @return CostType[]
     */
    public function readCostType()
    {
        return $this->costtype_db->read();
    }

    /**
     * Get an array for the selection box with (value => name) pairs
     *
     * @return array<string, string>
     */
    public function getCosttypeSelectEntries()
    {
        return $this->costtype_db->getSelectionArray();
    }

    /**
     * Delete db entry
     *
     * @param int 		$id
     */
    public function deleteCostType(int $id)
    {
        $this->costtype_db->deleteFor($id);
    }

    /**
     * Get a new Costtype object
     *
     * @param int 		$id
     * @param string 	$value
     * @param string 	$label
     * @param boolean 	$active
     *
     * @return CostType
     */
    public function getCostType(int $id, string $value, string $label, bool $active)
    {
        return new CostType($id, $value, $label, $active);
    }

    /**
     * Create a new blanko costtype with fixed id
     *
     * @return CostType
     */
    public function getEmptyCosttype()
    {
        return new CostType(-1);
    }

    /**
     * Check for existing relationships
     *
     * @param integer 	$id 	id of a costtype object
     * @return boolean
     */
    public function hasCosttypeRelationships(int $id)
    {
        return $this->costtype_db->hasRelationships($id);
    }

    /**
     * Get label for cost type
     *
     * @param 	integer 		$costtype
     * @return 	string
     */
    public function getCTLabel($costtype)
    {
        return $this->costtype_db->getCTLabel($costtype);
    }

    /**
     * Get value for cost type
     *
     * @param 	integer 		$costtype
     * @return 	string
     */
    public function getCTValue($costtype)
    {
        return $this->costtype_db->getCTValue($costtype);
    }

    /**********************************************************************************
     ***							Vat Rate DB 									***
     **********************************************************************************/
    /**
     * Set the VatRate db
     *
     * @param Accounting\Config\VatRate\DB 		$value
     */
    protected function setVatRateDB(Accounting\Config\VatRate\DB $value)
    {
        $this->vat_rate_db = $value;
    }

    /**
     * Insert VatRate into db
     *
     * @param Accounting\Config\VatRate\VatRate 		$entry
     */
    public function insertVatRate(Accounting\Config\VatRate\VatRate $entry)
    {
        return $this->vat_rate_db->insert($entry->getValue(), $entry->getLabel(), $entry->getActive());
    }

    /**
     * Update VatRate
     *
     * @param Accounting\Config\VatRate\VatRate 		$entry
     */
    public function updateVatRate(Accounting\Config\VatRate\VatRate $entry)
    {
        $this->vat_rate_db->update($entry);
    }

    /**
     * Get all datasets from db
     *
     * @return VatRate[]
     */
    public function readVatRate()
    {
        return $this->vat_rate_db->read();
    }

    /**
     * Get an array for the selection box with (value => name) pairs
     *
     * @return array<string, string>
     */
    public function getVatRateSelectEntries()
    {
        return $this->vat_rate_db->getSelectionArray();
    }

    /**
     * Delete db entry
     *
     * @param int 		$id
     */
    public function deleteVatRate(int $id)
    {
        $this->vat_rate_db->deleteFor($id);
    }

    /**
     * Get the value of selected vatrate
     *
     * @param int 	$id
     *
     * @return float
     */
    public function getVatRateValueById(int $id)
    {
        return $this->vat_rate_db->getVatRateValueById($id);
    }

    /**
     * Get a new Vatrate object
     *
     * @param int 		$id
     * @param string 	$value
     * @param string 	$label
     * @param boolean 	$active
     *
     * @return VatRate
     */
    public function getVatRate(int $id, string $value, string $label, bool $active)
    {
        return new VatRate($id, $value, $label, $active);
    }

    /**
     * Check for existing relationships
     *
     * @param integer 	$id 	id of a costtype object
     * @return boolean
     */
    public function hasVatrateRelationships(int $id)
    {
        return $this->vat_rate_db->hasRelationships($id);
    }

    /**
     * Create a new blanko VatRate with fixed id
     *
     * @return VatRate
     */
    public function getEmptyVatrate()
    {
        return new VatRate(-1);
    }

    /**
     * Get label for vatrate type
     *
     * @param 	integer 		$vatrate
     * @return 	string
     */
    public function getVRLabel($vatrate)
    {
        return $this->vat_rate_db->getVRLabel($vatrate);
    }
}
