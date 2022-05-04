<?php
/**
 * @file KBARTExportPlugin.inc.php
 *
 * Copyright (c) 2017-2021 Simon Fraser University
 * Copyright (c) 2017-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class KBARTExportPlugin
 * @brief Plugin class for the KBART Export plugin.
 */
import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.submission.SubmissionFile');
import('lib.pkp.classes.components.forms.FieldOptions');

class KBARTExportPlugin extends GenericPlugin {

	/**
	 * @copydoc GenericPlugin::register()
	 */
	public function register($category, $path, $mainContextId = NULL) {

		// Register the plugin even when it is not enabled
		$success = parent::register($category, $path);
		// error_log("KBARTExportPlugin: registered? $success");
		if ($success && $this->getEnabled()) {
			// error_log("KBARTExportPlugin: enabled");
			HookRegistry::register('LoadHandler', array($this, 'callbackHandleContent'));
			HookRegistry::register('Templates::Article::Main', [$this, 'updateFileName']);
			// HookRegistry::register('TemplateManager::display',array(&$this, 'getPluginSettings'));
		}
		return $success;
	}

	/**
	 * Provide a name for this plugin
	 */
	public function getDisplayName() {
		return __('plugins.generic.kbartExport.displayName');
	}

	/**
	 * Provide a description for this plugin
	 */
	public function getDescription() {
		return __('plugins.generic.kbartExport.description');
	}

	/**
	 * @copydoc Plugin::isSitePlugin()
	 */
	function isSitePlugin() {
		return true;
	}

	/**
	 * Add a settings action to the plugin's entry in the
	 * plugins list.
	 *
	 * @param Request $request
	 * @param array $actionArgs
	 * @return array
	 */
	public function getActions($request, $actionArgs) {

		// Get the existing actions
		$actions = parent::getActions($request, $actionArgs);

		if (!$this->getEnabled()) {
			return $actions;
		}

		// Create a LinkAction that will call the plugin's
		// `manage` method with the `settings` verb.updateFileName
		$router = $request->getRouter();
		$dispatcher = $router->getDispatcher();

		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$linkAction = new LinkAction(
			'settings',
			new AjaxModal(
				$router->url(
					$request,
					null,
					null,
					'manage',
					null,
					[
						'verb' => 'settings',
						'plugin' => $this->getName(),
						'category' => 'generic'
					]
				),
				$this->getDisplayName()
			),
			__('manager.plugins.settings'),
			null
		);

		import('lib.pkp.classes.linkAction.request.RedirectAction');
		$redirectAction = new LinkAction(
			'downloadFile',
			new RedirectAction(
				$dispatcher->url($request, ROUTE_PAGE, null, 'kbartexport', null, null)
			),
			__('plugins.generic.kbartExport.settings.downloadButton'),
			null
		);

		// Add the LinkAction to the existing actions.
		// Make it the first action to be consistent with
		// other plugins.
		array_unshift($actions, $redirectAction);
		array_unshift($actions, $linkAction);

		return $actions;
	}

	/**
	 * Show and save the settings form when the settings action
	 * is clicked.
	 *
	 * @param array $args
	 * @param Request $request
	 * @return JSONMessage
	 */
	public function manage($args, $request) {
		switch ($request->getUserVar('verb')) {

			// Return a JSON response containing the
			// settings form
			case 'settings':

				// Load the custom form
				$this->import('KBARTExportSettingsForm');
				$form = new KBARTExportSettingsForm($this);

				// Fetch the form the first time it loads, before
				// the user has tried to save it
				if (!$request->getUserVar('save')) {
					$form->initData();
					return new JSONMessage(true, $form->fetch($request));
				}

				// Validate and save the form data
				$form->readInputData();
				if ($form->validate()) {
					$form->execute();
					return new JSONMessage(true);
				}
		}
		return parent::manage($args, $request);
	}

	/**
	 * Declare the handler function to process the actual page PATH
   	 * @param $hookName string The name of the invoked hook
   	 * @param $args array Hook parameters
   	 * @return boolean Hook handling status
   	 */
	function callbackHandleContent($hookName, $args) {
		$request = Application::get()->getRequest();

		$page =& $args[0];
		$op =& $args[1];
		// Site-wide if op = "index"
		// Check if this is a request for a static page or preview.
		if ($page == 'kbartexport') {

			// It is -- attach the kbart export handler.
			define('HANDLER_CLASS', 'KBARTExportHandler');
			$this->import('KBARTExportHandler');

			return true;
		}
		return false;
	}

	/**
	 * Update the file name as configured in the settings.
	 *
	 * @param string $hookName string
	 * @param array $params [[
	 * 	@option array Additional parameters passed with the hook
	 * 	@option TemplateManager
	 * 	@option string The HTML output
	 * ]]
	 * @return boolean
	 */
	function updateFileName($hookName, $params) {

		// $contextId = Application::get()->getRequest()->getContext()->getId();

		$contextId = CONTEXT_SITE;

		// Get the publication statement for this journal or press
		$providerName = $this->getSetting($contextId, 'providerName');
		$regionConsortium = $this->getSetting($contextId, 'regionConsortium');
		$packageName = $this->getSetting($contextId, 'packageName');

		// If the journal or press does not have a publication statement,
		// check if there is one saved for the site.
		if (!$providerName && $contextId !== CONTEXT_SITE) {
			$providerName = $this->getSetting(CONTEXT_SITE, 'providerName');
		}
		if (!$regionConsortium && $contextId !== CONTEXT_SITE) {
			$regionConsortium = $this->getSetting(CONTEXT_SITE, 'regionConsortium');
		}
		if (!$packageName && $contextId !== CONTEXT_SITE) {
			$packageName = $this->getSetting(CONTEXT_SITE, 'packageName');
		}

		// Do not modify the output if there is no publication statement
		if (!$providerName) {
			return false;
		}
		if (!$regionConsortium) {
			return false;
		}
		if (!$packageName) {
			return false;
		}

		// Add the publication statement to the output
		$output =& $params[2];
		//$output .= '<p class="providerName">' . PKPString::stripUnsafeHtml($providerName) . '</p>';

		return false;
	}

}
