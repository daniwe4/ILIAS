<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;

class ilLSFileUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    /**
     * @inheritDoc
     */
    public function getUploadURL() : string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilObjLearningSequenceSettingsGUI::class, self::class],
            self::CMD_UPLOAD
        );
    }

    /**
     * @inheritDoc
     */
    public function getExistingFileInfoURL() : string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilObjLearningSequenceSettingsGUI::class, self::class],
            self::CMD_INFO
        );
    }

    /**
     * @inheritDoc
     */
    public function getFileRemovalURL() : string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilObjLearningSequenceSettingsGUI::class, self::class],
            self::CMD_REMOVE
        );
    }

    /**
     * @inheritDoc
     */
    public function getFileIdentifierParameterName() : string
    {
        return 'ls_item';
    }

    protected function getUploadResult() : HandlerResult
    {
        $status = HandlerResult::STATUS_OK;
        $identifier = md5(random_bytes(65));
        $message = 'Everything ok';

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    protected function getRemoveResult(string $identifier) : HandlerResult
    {
        $status = HandlerResult::STATUS_OK;
        $message = 'File Deleted';

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    protected function getInfoResult(string $identifier) : FileInfoResult
    {
        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            "$identifier.img",
            64,
            "image/jpeg"
        );
    }

    public function getInfoForExistingFiles(array $file_ids) : array
    {
        $infos = [];
        foreach ($file_ids as $file_id) {
            $infos[] = new BasicFileInfoResult(
                $this->getFileIdentifierParameterName(),
                $file_id,
                "Name $file_id.img",
                1234,
                "image/jpeg"
            );
        }

        return $infos;
    }
}
