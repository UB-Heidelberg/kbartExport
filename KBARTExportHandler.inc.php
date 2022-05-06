<?php

/**
 * @file NewsletterHelperHandler.inc.php
 *
 * Copyright (c) 2022 Heidelberg University
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class NewsletterHelperHandler
 */

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

        $plugin = PluginRegistry::getPlugin('generic','kbartexportplugin');
        $context = Application::get()->getRequest()->getContext();
        $contextId = $context ? $context->getId() : CONTEXT_SITE;

        $providerName = $plugin->getSetting($contextId, 'providerName');
        $regionConsortium = $plugin->getSetting($contextId, 'regionConsortium');
        $packageName = $plugin->getSetting($contextId, 'packageName');

        $timestamp = time();
        $date = date("Y-m-d", $timestamp);

        $fileName = $providerName . "_" . $regionConsortium . "_" . $packageName . "_" . $date . ".txt";

        // Define file header
        $headers = [
            "publication_title\t",
            "print_identifier\t",
            "online_identifier\t",
            "date_first_issue_online\t",
            "num_first_vol_online\t",
            "num_first_issue_online\t",
            "date_last_issue_online\t",
            "num_last_vol_online\t",
            "num_last_issue_online\t",
            "title_url\t",
            "first_author\t",
            "title_id\t",
            "embargo_info\t",
            "coverage_depth\t",
            "notes\t",
            "publisher_name\t",
            "publication_type\t",
            "date_monograph_published_print\t",
            "date_monograph_published_online\t",
            "monograph_volume\t",
            "monograph_edition\t",
            "first_editor\t",
            "parent_publication_title_id\t",
            "preceding_publication_title_id\t",
            "access_type\n"
        ];

        // Get all journals
        $journals = DAORegistry::getDAO('JournalDAO')->getAll(true)->toArray();
        foreach($journals as $journal) {

            // Get all published issues for a given journal.
            $issues = $this->getIssuesByJournalId($journal->getId());

            // // Extract datePublished property.
            // $dates = array_column($issues_arr_data,'datePublished');
            // error_log(var_export($dates,true));
            // error_log("Date first issue online: " . var_export(min($dates),true));

            $publicationTitle = $this->getPublicationTitle($journal);
            $printIdentifier = $this->getPrintIdentifier($journal);
            $onlineIdentifier = $this->getOnlineIdentifier($journal);

            error_log("Date First Issue Avaible Online: " . $this->getDateFirstIssueOnline($issues));

            $dateFirstIssueOnline = $this->getDateFirstIssueOnline($issues);
            $numFirstVolOnline = $this->getNumFirstVolOnline($issues);
            $numFirstIssueOnline = $this->getNumFirstIssueOnline($issues);
            $dateLastIssueOnline = $this->getDateLastIssueOnline($issues);
            $numLastVolOnline = $this->getNumLastVolOnline($issues);
            $numLastIssueOnline = $this->getNumLastIssueOnline($issues);

            $titleUrl = $this->getTitleUrl($request, $journal);
            $firstAuthor = $this->getFirstAuthor();
            $titleId = $this->getTitleId();
            $embargoInfo = $this->getEmbargoInfo();
            $coverageDepth = $this->getCoverageDepth();
            $notes = $this->getNotes();
            $publisherName = $this->getPublisherName($journal);
            $publicationType = $this->getPublicationType();
            $dateMonographPublishedPrint = $this->getDateMonographPublishedPrint();
            $dateMonographPublishedOnline = $this->getMonographPublishedOnline();
            $monographVolume = $this->getMonographVolume();
            $monographEdition = $this->getMonographEdition();
            $firstEditor = $this->getFirstEditor();
            $parentPublicationTitleId = $this->getParentPublicationTitleId();
            $precedingPublicationTitleId = $this->getPrecedingPublicationTitleId();
            $accessType = $this->getAccessType();

            $entry = [
                $publicationTitle,
                $printIdentifier,
                $onlineIdentifier,
                $dateFirstIssueOnline,
                $numFirstVolOnline,
                $numFirstIssueOnline,
                $dateLastIssueOnline,
                $numLastVolOnline,
                $numLastIssueOnline,
                $titleUrl,
                $firstAuthor,
                $titleId,
                $embargoInfo,
                $coverageDepth,
                $notes,
                $publisherName,
                $publicationType,
                $dateMonographPublishedPrint,
                $dateMonographPublishedOnline,
                $monographVolume,
                $monographEdition,
                $firstEditor,
                $parentPublicationTitleId,
                $precedingPublicationTitleId,
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

    /**
     * Get all published issues of a given journal
     *
     * @param $journalId int
     * @return Array
     */
    function getIssuesByJournalId($journalId) {
        $issueDao = DAORegistry::getDAO('IssueDAO');
        return $issueDao->getPublishedIssues($journalId)->toArray();
        // return $issueDao->getIssuesByIdentification($journalId)->toArray();
    }

    function getPropertyFromIssues($issues, $property) {

        // Convert nested array of Issue objects to pure nested array.
        $issues_arr = json_decode(json_encode($issues), true);

        // Create nested array filled only with '_data' property of Issue object.
        $issues_arr_data = array_column($issues_arr, '_data');

        // return $issues_arr_data;
        return array_column($issues_arr_data, $property);
    }

    // Get the publication title of the journal
    function getPublicationTitle($journal) {
        return $journal->getLocalizedName();
    }

    // Get the print-format identifier of the journal
    function getPrintIdentifier($journal) {
        return $journal->getData('printIssn');
    }

    function getOnlineIdentifier($journal) {
        return $journal->getData('onlineIssn');
    }

    // Get the date of the first issue avaible online
    function getDateFirstIssueOnline($issues) {

        // Get publication dates of given issues.
        $dates = $this->getPropertyFromIssues($issues, 'datePublished');

        return min($dates);
    }

    // Get the number of the first issue avaible online
    function getNumFirstVolOnline($issues) {
        $numbers = [];

        // Collect all numbers of a published issue
        foreach ($issues as $issue) {
            if ($issue->getData('published') == 1) {
                array_push($numbers, $issue->getData('number'));
            }
        }
        // error_log("ISSUE: " . var_export($numbers,true) . " NUMBERS: " . var_export(min($numbers),true));
        if (isset($numbers) && !empty($numbers)) {
            return min($numbers);
        } else {
            return "\t"; // TODO Ist das korrekt?
        }
    }

    function getNumFirstIssueOnline($issues) {
        // Get all publication dates of published issues
        $dates = [];
        foreach ($issues as $issue) {
            if ($issue->getData('published') == 1) {
                array_push($dates, $issue->getData('datePublished'));
            }
        }
        return "getNumFirstIssueOnline";
    }

    // function getPublicationDatesByIssues($issues) {
    //     // Convert nested array of Issue objects to pure nested array.
    //     $issues_arr = json_decode(json_encode($issues), true);
    //
    //     // Create nested array filled only with '_data' property of Issue object.
    //     $issues_arr_data = array_column($issues_arr, '_data');
    //
    //     // Extract datePublished property.
    //     $dates = array_column($issues_arr_data, 'datePublished');
    //
    //     return $dates;
    // }

    function getDateLastIssueOnline($issues) {

        // Get publication dates of given issues.
        $dates = $this->getPropertyFromIssues($issues, 'datePublished');

        return max($dates);
    }

    function getNumLastVolOnline() {
        return "getNumLastVolOnline";
    }

    function getNumLastIssueOnline() {
        return "getNumLastIssueOnline";
    }

    function getTitleUrl($request, $journal) {
        return $request->getRouter()->url($request, $journal->getPath());
    }

    function getFirstAuthor() {
        return "getFirstAuthor";
    }

    function getTitleId() {
        return "getTitleId";
    }

    function getEmbargoInfo() {
        return "getEmbargoInfo";
    }

    function getCoverageDepth() {
        return "getCoverageDepth";
    }

    function getNotes() {
        return "getNotes";
    }

    function getPublisherName($journal) {
        return $journal->getSetting('publisherInstitution');
    }

    function getPublicationType() {
        return "getPublicationType";
    }

    function getDateMonographPublishedPrint() {
        return "getDateMonographPublishedPrint";
    }

    function getMonographPublishedOnline() {
        return "getMonographPublishedOnline";
    }

    function getMonographVolume() {
        return "getMonographVolume";
    }

    function getMonographEdition() {
        return "getMonographEdition";
    }

    function getFirstEditor() {
        return "getFirstEditor";
    }

    function getParentPublicationTitleId() {
        return "getParentPublicationTitleId";
    }

    function getPrecedingPublicationTitleId() {
        return "getPrecedingPublicationTitleId";
    }

    function getAccessType() {
        return "getAccessType";
    }

}
