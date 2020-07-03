<?php

namespace CaT\Plugins\OnlineSeminar\VC\CSN;

use \CaT\Plugins\OnlineSeminar\VC;
use \CaT\Plugins\OnlineSeminar\Exceptions;

/**
 * Import for CSN VC data from xlsx
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Import implements VC\DataImport
{
    const XLS_HEADER_ROW_NUMBER = 1;
    const XLS_MAX_COLUMN_COUNT = 10;
    const XLS_START_COLUMN = "A";

    /**
     * Values recently requested
     */
    private $required_values = array("user_name", "email", "minutes", "phone");

    /**
     * Column header we expect in xlsx
     */
    private $default_header_columns = array("Telefonnummer" => array("column" => null, "obj_prop" => "phone"),
        "Name" => array("column" => null, "obj_prop" => "user_name"),
        "E-Mail" => array("column" => null, "obj_prop" => "email"),
        "Firma" => array("column" => null, "obj_prop" => "company"),
        "Dauer in Min." => array("column" => null, "obj_prop" => "minutes")
    );

    /**
     * @var int[]
     */
    protected $invalid_lines;

    /**
     * @var VC\Parser
     */
    protected $parser;

    public function __construct(VC\Parser $parser)
    {
        $this->parser = $parser;
        $this->invalid_lines = array();
    }
    /**
     * @inheritdoc
     */
    public function parseFile($file_path)
    {
        $this->parser->load($file_path);

        return $this->getFileContent();
    }

    /**
     * Get the content of xlsx file.
     * Each row as array
     *
     * @return string 	$line
     */
    protected function getFileContent()
    {
        $header_columns = $this->determineHeaderColumns();

        $lines = array();
        $last_row = $this->parser->getHighestRow();
        $start_row = self::XLS_HEADER_ROW_NUMBER + 1;
        $empty_first_column = false;

        while (true) {
            $line = array();
            foreach ($header_columns as $key => $value) {
                $column_value = $this->parser->formatedCellValue($value["column"], $start_row);

                if ($key == 0 && $column_value == "") {
                    $empty_first_column = true;
                    break;
                }

                $line[$value["obj_prop"]] = $column_value;
            }

            if ($empty_first_column) {
                break;
            }

            if (!$this->validLine($line)) {
                $this->invalid_lines[] = $start_row;
                $start_row++;
            } else {
                $lines[] = $line;
                $start_row++;
            }

            if ($start_row > $last_row) {
                break;
            }
        }

        $lines = $this->aggregateMinutesByUserName($lines);
        uasort($lines, array($this, 'sortLines'));

        return $lines;
    }

    /**
     * Sort default header columns to real xlsx columns
     *
     * @return array<string, mixed[]>
     */
    protected function determineHeaderColumns()
    {
        $header_columns = array();
        $counter = 0;

        foreach ($this->default_header_columns as $key => $value) {
            $start_column = self::XLS_START_COLUMN;

            while ($key != $this->parser->cellValue($start_column, self::XLS_HEADER_ROW_NUMBER)) {
                $start_column++;

                if ($start_column == self::XLS_MAX_COLUMN_COUNT) {
                    throw new Exceptions\InvalidFileException(__METHOD__ . " needed column header not found. Header: " . $key);
                }
            }

            $value["column"] = $start_column;
            $header_columns[$counter] = $value;
            $counter++;
        }

        asort($header_columns);

        return $header_columns;
    }

    /**
     * Sort lines by user name
     *
     * @param string[] 	$a
     * @param string[] 	$b
     *
     * @return string[]
     */
    protected function sortLines(array $a, array $b)
    {
        if ($a['user_name'] == $b['user_name']) {
            return 0;
        }

        if ($a['user_name'] > $b['user_name']) {
            return -1;
        }

        return 1;
    }

    /**
     * Aggregate values for each user
     *
     * @param string[] 	$lines
     *
     * @return string[]
     */
    protected function aggregateMinutesByUserName(array $lines)
    {
        $ret = array();
        foreach ($lines as $line) {
            $cur_name = $line["user_name"];

            $same_user_lines = array_filter($lines, function ($line) use ($cur_name) {
                if ($line["user_name"] == $cur_name) {
                    return $line;
                }
            });

            $new_user_line = null;
            if (count($same_user_lines) > 0) {
                foreach ($same_user_lines as $user_line) {
                    if ($new_user_line != null) {
                        $new_user_line["minutes"] = $this->addMinutes($new_user_line["minutes"], $user_line["minutes"]);
                    } else {
                        $new_user_line = $user_line;
                    }

                    unset($lines[array_search($user_line, $lines)]);
                }
                $new_user_line["minutes"] = (int) explode(":", $new_user_line["minutes"])[0];
                $ret[] = $new_user_line;
            }
        }

        return $ret;
    }

    /**
     * Add Minutes of lines
     *
     * @param string 	$minutes_a
     * @param string 	$minutes_b
     *
     * @return string
     */
    protected function addMinutes($minutes_a, $minutes_b)
    {
        $minutes_a = explode(":", $minutes_a);
        $minutes_b = explode(":", $minutes_b);

        $ret[0] = (int) $minutes_a[0] + (int) $minutes_b[0];
        $ret[1] = (int) $minutes_a[1] + (int) $minutes_b[1];

        if ($ret[1] > 60) {
            $ret[0]++;
            $ret[1] -= 60;
        }

        return implode(":", $ret);
    }

    /**
     * Is line from xlsx content valid
     *
     * @param string[]
     *
     * @return bool
     */
    public function validLine(array $line)
    {
        foreach ($line as $key => $value) {
            if (!is_string($value)) {
                return false;
            }

            if (in_array($key, $this->required_values)) {
                if ($value == "") {
                    return false;
                }
            }

            if ($key == "email") {
                if (!preg_match('/.+\@.+\..+/', $value)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the invalid line numbers
     *
     * @return int[]
     */
    public function getInvalidLineNumbers()
    {
        return $this->invalid_lines;
    }
}
