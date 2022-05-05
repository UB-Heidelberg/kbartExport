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
            "publication_title\t"
            "print_identifier\t"
            "online_identifier\t"
            "date_first_issue_online\t"
            "num_first_vol_online\t"
            "num_first_issue_online\t"
            "date_last_issue_online\t"
            "num_last_vol_online\t"
            "num_last_issue_online\t"
            "title_url\t"
            "first_author\t"
            "title_id\t"
            "embargo_info\t"
            "coverage_depth\t"
            "notes\t"
            "publisher_name\t"
            "publication_type\t"
            "date_monograph_published_print\t"
            "date_monograph_published_online\t"
            "monograph_volume\t"
            "monograph_edition\t"
            "first_editor\t"
            "parent_publication_title_id\t"
            "preceding_publication_title_id\t"
            "access_type\t"
        ];

        // Build Rows
        $journals = DAORegistry::getDAO('JournalDAO')->getAll(true)->toArray();
        foreach($journals as $journal) {
            $publicationTitle = $journal->getLocalizedName();
            $printIdentifier = $journal->getData('printIssn');
            $onlineIdentifier = $journal->getData('onlineIssn');
            $dateFirstIssueOnline = 
            $numFirstVolOnline
            $numFirstIssueOnline
            $dateLastIssueOnline
            $numLastVolOnline
            $numLastIssueOnline
            $titleUrl = $request->getRouter()->url($request, $journal->getPath());
            $firstAuthor
            $titleId
            $embargoInfo
            $coverageDepth
            $notes
            $publisherName = $journal->getSetting('publisherInstitution');
            $publicationType
            $dateMonographPublishedPrint
            $dateMonographPublishedOnline
            $monographVolume
            $monographEdition
            $firstEditor
            $parentPublicationTitleId
            $precedingPublicationTitleId
            $accessType

            $entry = [
                $publicationTitle
                $printIdentifier
                $onlineIdentifier
                $dateFirstIssueOnline
                $numFirstVolOnline
                $numFirstIssueOnline
                $dateLastIssueOnline
                $numLastVolOnline
                $numLastIssueOnline
                $titleUrl
                $firstAuthor
                $titleId
                $embargoInfo
                $coverageDepth
                $notes
                $publisherName
                $publicationType
                $dateMonographPublishedPrint
                $dateMonographPublishedOnline
                $monographVolume
                $monographEdition
                $firstEditor
                $parentPublicationTitleId
                $precedingPublicationTitleId
                $accessType
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
            echo implode("\t",$entry) . "\n";
        }
    }

}
