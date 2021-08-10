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
    /*function isSitePlugin() {
        return true;
    }*/

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

        // Check if this is a request for a static page or preview.
        if ($page == 'kbartexport') {

            // It is -- attach the kbart export handler.
            define('HANDLER_CLASS', 'KBARTExportHandler');
            $this->import('KBARTExportHandler');
            
            return true;
        }
        return false;
    }

}
