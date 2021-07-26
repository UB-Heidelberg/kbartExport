<?php

import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.submission.SubmissionFile');
import('lib.pkp.classes.components.forms.FieldOptions');

class KBARTExportPlugin extends GenericPlugin {

	public function register($category, $path, $mainContextId = NULL) {

    	// Register the plugin even when it is not enabled
		$success = parent::register($category, $path);
		if ($success && $this->getEnabled()) {
            HookRegistry::register('LoadHandler', array($this, 'callbackHandleContent'));
		}
		return $success;
	}

	/**
	 * Provide a name for this plugin
	 */
	public function getDisplayName() {
		return 'KBARTExportPlugin';
	}

	/**
	 * Provide a description for this plugin
	 */
	public function getDescription() {
		return 'This plugin was created for exporting metadata in the KBART format.';
	}

    function callbackHandleContent($hookName, $args) {
        $request = Application::get()->getRequest();

        $page =& $args[0];
        $op =& $args[1];

        // Check if this is a request for a static page or preview.
        if ($page == 'kbartexport') {

            // It is -- attach the static pages handler.
            define('HANDLER_CLASS', 'KBARTExportHandler');
            $this->import('KBARTExportHandler');

            // Allow the static pages page handler to get the plugin object
            // StaticPagesHandler::setPlugin($this);
            // StaticPagesHandler::setPage($staticPage);
            return true;
        }
        return false;
    }

}
