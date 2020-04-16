<?php

class ilTrainingSearchPageConfig extends ilPageConfig
{
    public function init()
    {
        $this->setEnableInternalLinks(true);
        $this->setIntLinkHelpDefaultType("RepositoryItem");
        $this->setEnablePCType("FileList", false);
        $this->setEnablePCType("Map", true);
        $this->setEnablePCType("Resources", true);
        $this->setMultiLangSupport(true);
        $this->setSinglePageMode(true);
        $this->setEnablePermissionChecks(true);
    }
}
