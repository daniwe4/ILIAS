<?php
namespace CaT\Plugins\CourseMember\SignatureList;

/**
 * Commuication class between back and front end
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ilActions
{
    /**
     * @var \ilCourseMemberPlugin
     */
    protected $plugin;

    /**
     * @var ilFileStorage
     */
    protected $file_storage;

    public function __construct(\ilCourseMemberPlugin $plugin, ilFileStorage $file_storage)
    {
        $this->plugin = $plugin;
        $this->file_storage = $file_storage;
    }

    /**
     * Get the plugin object.
     *
     * @throws \Exception 	if no plugin is set
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
     * This function uploads a file.
     *
     * @param array<string, mixed> 	$file_infos
     * @return bool
     */
    public function upload($file_infos)
    {
        return $this->file_storage->uploadFile($file_infos);
    }

    /**
     * Return the path of the uploaded file.
     *
     * @return string | null
     */
    public function getPath()
    {
        return $this->file_storage->getFilePath();
    }

    /**
     * Delete the uploaded file.
     *
     * @return void
     */
    public function delete()
    {
        return $this->file_storage->deleteCurrentFile();
    }
}
