<?php declare(strict_types=1);

namespace CaT\Plugins\AgendaItemPool\AgendaItem;

/**
 * Trait IDD_GDV_Content.
 * Holds the data for IDD and GDV.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
trait IDD_GDV_Content
{
    private static $idd_content = [
        "" => "-"
    ];

    private static $gdv_content = [
        "001" => "Privat-Vorsorge-Lebens-/Rentenversicherung",
        "002" => "Privat-Vorsorge-Kranken-/Pflegeversicherung",
        "003" => "Privat-Sach-/Schadenversicherung",
        "004" => "Firmenkunden-Vorsorge(bAV/Personenversicherung)",
        "005" => "Firmenkunden-Sach-/Schadenversicherung",
        "006" => "Spartenübergreifend",
        "007" => "Beratungskompetenz"
    ];
}
