<?php
/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\CopySettings\CourseCreation;

use CaT\Plugins\CopySettings\Children\Child;
use ILIAS\TMS\CourseCreation\Step;
use ILIAS\TMS\CourseCreation\Request;
use ILIAS\TMS\CourseCreation\RequestBuilder;
use ILIAS\TMS\CourseCreation\ChildAssistant;
use \CaT\Ente\ILIAS\Entity;

/**
 * Step to inform the user about content of training
 */
class AdditionalInfosStep extends \CourseCreationStep
{
    const F_WEBLINK = "f_weblink";
    const F_FILE = "f_file";
    const F_FILE_DELETE = "f_file_delete";
    const HTTP_REGEXPS = "#^(http|https)://#";
    const ORG_FILE_NAME = "org_file_name";
    const FILE_TYPE = "file_type";
    const FILE_SIZE = "file_size";
    const UPLOAD_FILE_PATH = "upload_file_path";

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var	RequestBuilder|null
     */
    protected $request_builder;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var	\ilObjCopySettings
     */
    protected $object;

    public function __construct(
        Entity $entity,
        \Closure $txt,
        \ilObjCopySettings $object,
        AdditionInfoFileStorage $file_storage,
        \ilObjUser $user
    ) {
        $this->entity = $entity;
        $this->txt = $txt;
        $this->object = $object;
        $this->file_storage = $file_storage;
        $this->user = $user;
    }

    // from Ente\Component

    /**
     * @inheritdocs
     */
    public function entity()
    {
        return $this->entity;
    }

    // from TMS\Wizard\Step

    /**
     * @inheritdocs
     */
    public function getLabel()
    {
        return $this->txt("additional_infos");
    }

    /**
     * @inheritdocs
     */
    public function getDescription()
    {
        return $this->txt("additional_infos_desc");
    }

    /**
     * @inheritdocs
     */
    public function appendToStepForm(\ilPropertyFormGUI $form)
    {
        $ti = new \ilTextInputGUI($this->txt("weblink"), self::F_WEBLINK);
        $form->addItem($ti);

        $fu = new \ilFileInputGUI($this->txt("file"), self::F_FILE);
        $fu->setALlowDeletion(true);
        $form->addItem($fu);

        $hi = new \ilHiddenInputGUI(self::UPLOAD_FILE_PATH);
        $form->addItem($hi);
        $hi = new \ilHiddenInputGUI(self::ORG_FILE_NAME);
        $form->addItem($hi);
        $hi = new \ilHiddenInputGUI(self::FILE_TYPE);
        $form->addItem($hi);
        $hi = new \ilHiddenInputGUI(self::FILE_SIZE);
        $form->addItem($hi);
    }

    /**
     * @inheritdocs
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        $values = [];
        $values[self::F_WEBLINK] = $data[self::F_WEBLINK];
        $values[self::F_FILE] = $data[self::ORG_FILE_NAME];
        $values[self::UPLOAD_FILE_PATH] = $data[self::UPLOAD_FILE_PATH];
        $values[self::ORG_FILE_NAME] = $data[self::ORG_FILE_NAME];
        $values[self::FILE_SIZE] = $data[self::FILE_SIZE];
        $values[self::FILE_TYPE] = $data[self::FILE_TYPE];

        if (count($values) > 0) {
            $form->setValuesByArray($values);
        }
    }

    /**
     * @inheritdocs
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");

        $weblink = $data[self::F_WEBLINK];
        if (strlen(preg_replace(self::HTTP_REGEXPS, "", $weblink)) == 0) {
            $weblink = "-";
        }
        $item = new \ilNonEditableValueGUI($this->txt("weblink"), "", true);
        $item->setValue($weblink);
        $form->addItem($item);

        $file_name = "-";
        if (isset($data[self::ORG_FILE_NAME]) && $data[self::ORG_FILE_NAME] != "") {
            $file_name = $data[self::ORG_FILE_NAME];
        }
        $item = new \ilNonEditableValueGUI($this->txt("file"), "", true);
        $item->setValue($file_name);
        $form->addItem($item);
    }

    /**
     * @inheritdocs
     */
    public function processStep($data)
    {
        $weblink = $data[self::F_WEBLINK];
        if (!is_null($weblink)
            && $weblink != ""
        ) {
            $this->request_builder->addConfigurationFor(
                $this->entity->object(),
                ["weblink" => $weblink]
            );
        }

        if ($data[self::ORG_FILE_NAME] != "") {
            $this->request_builder->addConfigurationFor(
                $this->entity->object(),
                [
                    "upload_file" => [
                        self::ORG_FILE_NAME => $data[self::ORG_FILE_NAME],
                        self::UPLOAD_FILE_PATH => $data[self::UPLOAD_FILE_PATH],
                        self::FILE_SIZE => $data[self::FILE_SIZE],
                        self::FILE_TYPE => $data[self::FILE_TYPE]
                    ]
                ]
            );
        }
    }

    /**
     * @inheritdocs
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $data = [];
        $post = $_POST;
        $weblink = $post[self::F_WEBLINK];

        if (!is_null($weblink)
            && $weblink != ""
            && !preg_match(self::HTTP_REGEXPS, $weblink)
        ) {
            $weblink = "https://" . $weblink;
        }
        $data[self::F_WEBLINK] = $weblink;

        $file_infos = $post[self::F_FILE];

        $delete = false;
        if (isset($post[self::F_FILE_DELETE]) && $post[self::F_FILE_DELETE] == 1) {
            $file_path = $post[self::UPLOAD_FILE_PATH];
            $this->file_storage->deleteFile($file_path);
            $delete = true;
        }

        if ($file_infos["name"] != "") {
            $file_path = $post[self::UPLOAD_FILE_PATH];
            $this->file_storage->deleteFile($file_path);
            $delete = true;

            $new_file_path = $this->uploadFileToTemp($file_infos);
            $data[self::ORG_FILE_NAME] = $file_infos["name"];
            $data[self::UPLOAD_FILE_PATH] = $new_file_path;

            $data[self::FILE_TYPE] = $file_infos["type"];
            $data[self::FILE_SIZE] = $file_infos["size"];
        }

        if (!$delete && $post[self::ORG_FILE_NAME] != "") {
            $data[self::ORG_FILE_NAME] = $post[self::ORG_FILE_NAME];
            $data[self::UPLOAD_FILE_PATH] = $post[self::UPLOAD_FILE_PATH];

            $data[self::FILE_TYPE] = $post[self::FILE_TYPE];
            $data[self::FILE_SIZE] = $post[self::FILE_SIZE];
        }

        return $data;
    }

    protected function uploadFileToTemp(array $file_infos)
    {
        $new_file_name = $this->createFileName($file_infos["name"]);
        $file_infos["name"] = $new_file_name;
        $this->file_storage->uploadFile($file_infos);

        return $this->file_storage->getFilePathByFileName($new_file_name);
    }

    protected function createFileName($filename)
    {
        $str = $filename . $this->user->getLogin();
        $str .= $this->randomString(5);
        $file_name_crypt = md5($str);

        return $file_name_crypt;
    }

    protected function randomString($chars)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < $chars; $i++) {
            $randstring = $characters[rand(0, strlen($characters))];
        }

        return $randstring;
    }

    // from TMS\CourseCreation\Step

    /**
     * @inheritdocs
     */
    public function getPriority()
    {
        return 5000;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable()
    {
        $extended_settings = $this->object->getExtendedSettings();
        return $extended_settings->getAdditionalInfos();
    }

    /**
     * @inheritdocs
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @inheritdocs
     */
    public function setRequestBuilder(RequestBuilder $request_builder)
    {
        $this->request_builder = $request_builder;
    }

    /**
     * Get the ref id of entity object
     *
     * @return int
     */
    protected function getEntityRefId()
    {
        return $this->entity()->object()->getRefId();
    }

    /**
     * Get the ILIAS dictionary
     *
     * @return \ArrayAccess | array
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * i18n
     *
     * @param	string	$id
     * @return	string	$text
     */
    protected function txt($id)
    {
        assert('is_string($id)');
        return call_user_func($this->txt, $id);
    }
}
