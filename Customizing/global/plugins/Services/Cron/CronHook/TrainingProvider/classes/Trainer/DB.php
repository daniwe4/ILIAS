<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\Trainer;

/**
 * Interface for trainer database handle
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    public function install() : void;
    public function select(int $id) : Trainer;
    public function update(Trainer $trainer) : void;
    public function delete(int $id) : void;
    public function create(
        string $title,
        string $salutation,
        string $firstname,
        string $lastname,
        ?int $provider_id = null,
        string $email = "",
        string $phone = "",
        string $mobile_number = "",
        ?float $fee = null,
        ?string $extra_infos = null,
        bool $active = true
    ) : Trainer;
}
