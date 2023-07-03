<?php

/**
 * @file KBARTExportSettingsForm.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Copyright (c) 2023 Heidelberg University
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class KBARTExportSettingsForm
 */

// import('lib.pkp.classes.form.Form');
namespace APP\plugins\generic\kbartExport;

use APP\core\Application;
use APP\notification\NotificationManager;
use APP\template\TemplateManager;

use PKP\form\Form;
use PKP\form\validation\FormValidationCSRF;
use PKP\form\validation\FormValidationPost;

class KbartExportSettingsForm extends Form
{
	public KbartExportPlugin $plugin;

	public function __construct(KbartExportPlugin $plugin)
	{
		// Define the settings template and store a copy of the plugin object.
		parent::__construct($plugin->getTemplateResource('settings.tpl'));
		$this->plugin = $plugin;

		// Always add POST and CSRF validation to secure your form.
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Load settings already saved in the database.
	 *
	 * Settings are stored by context, so that each journal or press
	 * can have different settings.
	 */
	public function initData()
	{
		$context = Application::get()->getRequest()->getContext();
		// $contextId = $context ? $context->getId() : CONTEXT_SITE;
		$contextId = CONTEXT_SITE;
		$this->setData('providerName', $this->plugin->getSetting($contextId, 'providerName'));
		$this->setData('regionConsortium', $this->plugin->getSetting($contextId, 'regionConsortium'));
		$this->setData('packageName', $this->plugin->getSetting($contextId, 'packageName'));
		parent::initData();
	}

	/**
	 * Load data that was submitted with the form.
	 */
	public function readInputData()
	{
		$this->readUserVars(['providerName']);
		$this->readUserVars(['regionConsortium']);
		$this->readUserVars(['packageName']);
		parent::readInputData();
	}

	/**
	 * Fetch any additional data needed for your form.
	 *
	 * Data assigned to the form using $this->setData() during the
	 * initData() or readInputData() methods will be passed to the
	 * template.
	 *
	 * @return string
	 */
	public function fetch($request, $template = null, $display = false)
	{
		// Build the download URL.
		$router = $request->getRouter();
		$dispatcher = $router->getDispatcher();
		$kbartDownloadUrl = $dispatcher->url($request, ROUTE_PAGE, null, 'kbartexport', null, null);

		// Pass the plugin name to the template so that it can be
		// used in the URL that the form is submitted to.
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->plugin->getName());
		$templateMgr->assign('kbartDownloadUrl', $kbartDownloadUrl);

		return parent::fetch($request, $template, $display);
	}

	/**
	 * Save the settings.
	 *
	 * @return null|mixed
	 */
	public function execute(...$functionArgs)
	{
		$contextId = CONTEXT_SITE;

		$this->plugin->updateSetting($contextId, 'providerName', $this->getData('providerName'));
		$this->plugin->updateSetting($contextId, 'regionConsortium', $this->getData('regionConsortium'));
		$this->plugin->updateSetting($contextId, 'packageName', $this->getData('packageName'));

		// Tell the user that the save was successful.
		import('classes.notification.NotificationManager');
		$notificationMgr = new NotificationManager();
		$notificationMgr->createTrivialNotification(
			Application::get()->getRequest()->getUser()->getId(),
			NOTIFICATION_TYPE_SUCCESS,
			['contents' => __('common.changesSaved')]
		);

		return parent::execute($functionArgs);
	}
}
