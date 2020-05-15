<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Security\PluginLogin;

class ilDCSecurityGUI
{
	const CMD_CONFIG = "configure";
	const CMD_SHOW_CONFIG = "showSecurityConfig";
	const CMD_SAVE_CONFIG = "saveSecurityConfig";
	const CMD_AUTOCOMPLETE = "userfieldAutocomplete";
	const F_LOGIN = "login";

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilGlobalTemplateInterface
	 */
	protected $tpl;

	/**
	 * @var Closure
	 */
	protected $txt;

	/**
	 * @var string
	 */
	protected $plugin_id;

	/**
	 * @var PluginLogin\DB
	 */
	protected $db;

	public function __construct(
		ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
		Closure $txt,
		string $plugin_id,
		PluginLogin\DB $db
	) {
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->txt = $txt;
		$this->plugin_id = $plugin_id;
		$this->db = $db;
	}

	/**
	 * @throws Exception
	 */
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case self::CMD_CONFIG:
			case self::CMD_SHOW_CONFIG:
				$this->showConfig();
				break;
			case self::CMD_SAVE_CONFIG:
				$this->saveConfig();
				break;
			case self::CMD_AUTOCOMPLETE:
				$this->userfieldAutocomplete();
				break;
			default:
				throw new Exception("Unknown command: ".$cmd);
		}
	}

	protected function showConfig()
	{
		$form = new PluginLogin\ILIAS\ilAllowedUsernamesGUI(
			$this->ctrl->getFormAction($this),
			self::CMD_SAVE_CONFIG,
			self::CMD_SHOW_CONFIG,
			self::F_LOGIN,
			$this->ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", false, false),
			$this->txt,
			$this->getFormValues()
		);

		$this->tpl->setContent($form->getHtml());
	}

	protected function saveConfig()
	{
		$post = $_POST;
		$this->db->deleteFor($this->plugin_id);

		$logins = $post[self::F_LOGIN];
		$logins = array_unique($logins);
		$logins = array_filter(
			$logins,
			function (string $login) {
				return $login != "";
			}
		);

		if (count($logins) > 0) {
			foreach ($logins as $login) {
				$this->db->addUsername($login, $this->plugin_id);
			}
			ilUtil::sendSuccess($this->txt("usernames_saved"), true);
		} else {
			ilUtil::sendInfo($this->txt("nothing_to_save"), true);
		}
		$this->ctrl->redirect($this, self::CMD_SHOW_CONFIG);
	}

	protected function userfieldAutocomplete()
	{
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields(array('login','firstname','lastname','email'));
		$auto->enableFieldSearchableCheck(false);
		if (($_REQUEST['fetchall'])) {
			$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
		}
		echo $auto->getList($_REQUEST['term']);
		exit();
	}

	protected function getFormValues(): array
	{
		$ret = [];
		$values = [];

		foreach ($this->db->selectUsernames($this->plugin_id) as $user) {
			array_push($values, $user->getUsername());
		}

		$ret[self::F_LOGIN] = $values;
		return $ret;
	}

	protected function txt(string $code): string
	{
		return call_user_func($this->txt, $code);
	}
}
