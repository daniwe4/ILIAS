<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Service;

/**
 * Interface for service configuration DB
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    public function create(
        int $id,
        string $mail_service_list = "",
        string $mail_room_setup = "",
        int $days_send_service = null,
        int $days_send_room_setup = null,
        string $mail_material_list = "",
        int $days_send_material_list = null,
        string $mail_accomodation_list = "",
        int $days_send_accomodation_list = null,
        int $days_remind_accomodation_list = null
    ) : Service;

    public function update(Service $service);
    public function delete(int $id);
}
