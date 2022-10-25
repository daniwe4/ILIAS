<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

class MysqlExecDumper implements MysqlDumper
{
    public const FILE_NAME = "dump.sql";

    protected ?string $path;

    public function __construct(?string $path)
    {
        $this->export_hook_path = $path;
    }

    public function createDump(
        string $host,
        string $user,
        string $password,
        string $name,
        string $port,
        string $target
    ): void {
        try {
            $dumper = new Ifsnop\Mysqldump\Mysqldump(
                "mysql:host=$host;port=$port;dbname=$name",
                $user,
                $password,
                ['add-drop-table' => true]
            );
            if (!is_null($this->export_hook_path)) {
                include $this->export_hook_path;
            }
            $dumper->start($target . "/" . self::FILE_NAME);
        } catch (\Exception $e) {
            throw new Exception("Error during sql dump: " . $e->getMessage());
        }
    }
}
