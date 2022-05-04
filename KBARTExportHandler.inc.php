<?php

import('classes.handler.Handler');

class KBARTExportHandler extends Handler {
    /** @var KBARTExportPlugin The kbart export plugin */
    static $plugin;

    /**
     * Provide the kbart export plugin to the handler.
     * @param $plugin KBARTExportPlugin
     */
    static function setPlugin($plugin) {
        self::$plugin = $plugin;
    }

    /**
     * Handle index request (redirect to "view")
     * @param $args array Arguments array.
     * @param $request PKPRequest Request object.
     */
    function index($args, $request) {

        $context = Application::get()->getRequest()->getContext();
        $contextId = $context ? $context->getId() : CONTEXT_SITE;

        $plugin = PluginRegistry::getPlugin('generic','kbartexportplugin');
        $providerName = $plugin->getSetting($contextId, 'providerName');
        $regionConsortium = $plugin->getSetting($contextId, 'regionConsortium');
        $packageName = $plugin->getSetting($contextId, 'packageName');

        $timestamp = time();
        $date = date("Y-m-d",$timestamp);

        $fileName = $providerName . "_" . $regionConsortium . "_" . $packageName . "_" . $date . ".txt";

        // Define file header
        $headers = [
            "Journal-Titel ; ",
            "URL ; ",
            "Print ISSN ; ",
            "Online ISSN ; ",
            "Publisher ; ",
            "Journal ID\n"
        ];

        // Build Rows
        $journals = DAORegistry::getDAO('JournalDAO')->getAll(true)->toArray();
        foreach($journals as $journal) {
            $title = $journal->getLocalizedName();
            $journalId = $journal->getData('id');
            $url = $request->getRouter()->url($request, $journal->getPath());
            $printIssn = $journal->getSetting('printIssn');
            $onlineIssn = $journal->getSetting('onlineIssn');
            $publisherName = $journal->getSetting('publisherInstitution');

            $entry = [
                $title,
                $url,
                $printIssn,
                $onlineIssn,
                // $dateFirstIssueOnline,
                // $numberFirstVolumeOnline,
                // $numberFirstIssueOnline,
                // $dateLastIssueOnline,
                // $numberLastVolumeOnline,
                // $numberLastIssueOnline,
                $publisherName,
                //$titleUrl
                $journalId
            ];

            $entries[] = $entry;
        }

        // List table entries in alphabetical order by journal title
        usort($entries, function ($item1, $item2) {
            return strnatcasecmp($item1[0], $item2[0]);
        });

        // Trigger the "Save as" dialog
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        //header('Content-Length: ' . filesize($file));
        header("Content-Type: text/plain");

        // Output file header
        foreach($headers as $header) {
            echo $header;
        }

        // Output file body
        foreach($entries as $entry) {
            echo implode(" ; ",$entry) . "\n";
        }
    }

}
