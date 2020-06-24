<?php
namespace CaT\Plugins\MaterialList\Lists\Export;

use CaT\Plugins\MaterialList\HeaderConfiguration\XLSHeaderExport;
use CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry;
use \CaT\Libs\ExcelWrapper;

/**
 * Export a Materiallist to XLS
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Exporter
{
    const FILE_NAME_SUFFIX = "xlsx";


    /**
     * @var string | null
     */
    protected $tmp_folder;

    /**
     * @param 	XLSHeaderExport 	$xls_header_export
     * @param 	ConfigurationEntry[] 	$header_entries
     * @param 	\Closure 	$txt
     * @param 	ilObjMaterialList[] 	$xmat_objs
     */
    public function __construct(
        XLSHeaderExport $xls_header_export,
        array $header_entries,
        \Closure $txt,
        array $xmat_objs
        ) {
        if (count($xmat_objs) < 1) {
            throw new \InvalidArgumentException("Give at least one xmat-object", 1);
        }

        $this->header_entries = $header_entries;
        $this->xls_header_export = $xls_header_export;
        $this->xmat_objs = $xmat_objs;
        $this->txt = $txt;
    }

    /**
     * @return 	string[][]
     */
    protected function getHeaderValues()
    {
        $crs = $this->xmat_objs[0]->getParentCourse();
        return $this->xls_header_export->getHeaderValuesForExport($crs, $this->header_entries);
    }

    /**
     * Get Filename.
     *
     * @return 	string
     */
    protected function getExportFilename()
    {
        $first_obj = $this->xmat_objs[0];
        $crs_ref = $first_obj->getParentCourse()->getRefId();

        $file_name = sprintf(
            '%s_%s.%s',
            $crs_ref,
            $this->txt('filename_materiallist'),
            self::FILE_NAME_SUFFIX
        );
        return $file_name;
    }

    /**
     * Get the temporary-folder.
     *
     * @return 	string
     */
    protected function getTempFolder()
    {
        if (is_null($this->tmp_folder)) {
            $this->tmp_folder = $this->buildTempFolder();
        }
        return $this->tmp_folder;
    }

    /**
     * Build a unique temporary folder.
     *
     * @return 	string
     */
    protected function buildTempFolder()
    {
        $tmp_folder = sys_get_temp_dir();
        if (substr($tmp_folder, -1) != "/") {
            $tmp_folder .= "/";
        }
        $tmp_folder .= uniqid() . '/';
        mkdir($tmp_folder, 0700, true);
        return $tmp_folder;
    }

    /**
     * Get folder and filename.
     *
     * @return 	string[]
     */
    public function getFileLocation()
    {
        return [$this->getTempFolder(), $this->getExportFilename()];
    }

    /**
     * Return instance of ExportList with SpoutWriter
     *
     * @return 	ExportList
     */
    protected function getListExporter()
    {
        list($folder, $file_name) = $this->getFileLocation();
        $writer = new ExcelWrapper\Spout\SpoutWriter();
        $writer->setPath($folder);
        $writer->setFileName($file_name);

        return new ExportList(
            $writer,
            new ExcelWrapper\Spout\SpoutInterpreter(),
            $this->txt
        );
    }

    /**
     * Export the xls-list.
     *
     * @return 	void
     */
    public function export()
    {
        $list_exporter = $this->getListExporter();
        $list_exporter->startExport();
        $list_exporter->printHeader($this->getHeaderValues());
        foreach ($this->xmat_objs as $materiallist) {
            $object_actions = $materiallist->getActions();
            $materials = $object_actions->getMaterialsForExport();
            $materiallist_title = $object_actions->getObject()->getTitle();
            $list_exporter->printMaterials($materiallist_title, $materials);
        }
        $list_exporter->stopExport();
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt(string $code)
    {
        $txt = $this->txt;

        return $txt($code);
    }
}
