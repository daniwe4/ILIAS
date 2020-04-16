<?php declare(strict_types=1);

namespace CaT\Plugins\UserBookings\Settings;

/**
 * Keeps additional setting informations for each USerBooking object
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class UserBookingsSettings
{

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var bool
     */
    protected $superior_view;

    /**
     * @var bool
     */
    protected $local_evaluation;

    /**
     * @var bool
     */
    protected $recommendation_allowed;

    /**
     * @param int 	$obj_id
     * @param bool 	$superior_view
     */
    public function __construct(
        int $obj_id,
        bool $superior_view = false,
        bool $local_evaluation = false,
        bool $recommendation_allowed = false
    ) {
        $this->obj_id = $obj_id;
        $this->superior_view = $superior_view;
        $this->local_evaluation = $local_evaluation;
        $this->recommendation_allowed = $recommendation_allowed;
    }

    /**
     * @return int
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @return bool
     */
    public function getSuperiorView() : bool
    {
        return $this->superior_view;
    }

    /**
     * Get clone of this with superior view
     *
     * @param bool 	$superior_view
     *
     * @return $this
     */
    public function withSuperiorView(bool $superior_view) : UserBookingsSettings
    {
        $clone = clone $this;
        $clone->superior_view = $superior_view;
        return $clone;
    }

    /**
     * @return bool
     */
    public function getLocalEvaluation() : bool
    {
        return $this->local_evaluation;
    }

    /**
     * Get clone of this with local evaluation
     *
     * @param bool 	$superior_view
     *
     * @return $this
     */
    public function withLocalEvaluation(bool $local_evaluation) : UserBookingsSettings
    {
        $clone = clone $this;
        $clone->local_evaluation = $local_evaluation;
        return $clone;
    }

    public function getRecommendationAllowed() : bool
    {
        return $this->recommendation_allowed;
    }

    public function withRecommendationAllowed(bool $allowed) : UserBookingsSettings
    {
        $clone = clone $this;
        $clone->recommendation_allowed = $allowed;
        return $clone;
    }
}
