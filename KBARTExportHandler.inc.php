<?php

/**
 * @file KBARTExportHandler.inc.php
 *
 * Copyright (c) 2022 Heidelberg University
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class KBARTExportHandler
 */

import('classes.handler.Handler');

class KBARTExportHandler extends Handler {

    /** @var KBARTExportPlugin The kbart export plugin */
    static $plugin;

    /**
     * Provide the kbart export plugin to the handler.
     *
     * @param $plugin KBARTExportPlugin
     */
    static function setPlugin($plugin) {
        self::$plugin = $plugin;
    }

    /**
     * Handle index request (redirect to "view").
     *
     * @param $args array Arguments array.
     * @param $request PKPRequest Request object.
     */
    function index($args, $request) {

        $plugin = PluginRegistry::getPlugin('generic','kbartexportplugin');
        $contextId = CONTEXT_SITE;

        // Configure the parameters to show up in the file name.
        $providerName = $plugin->getSetting($contextId, 'providerName');
        $regionConsortium = $plugin->getSetting($contextId, 'regionConsortium');
        $packageName = $plugin->getSetting($contextId, 'packageName');

        $timestamp = time();
        $date = date("Y-m-d", $timestamp);

        $fileName = $providerName . "_" . $regionConsortium . "_" . $packageName . "_" . $date . ".txt";

        // Define file header.
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

        // Get all journals of the OJS instance.
        $journals = DAORegistry::getDAO('JournalDAO')->getAll(true)->toArray();

        foreach($journals as $journal) {

            // Get all published issues for a given journal.
            $issues = $this->getIssuesByJournalId($journal->getId());

            $publicationTitle = $this->getPublicationTitle($journal);
            $printIdentifier = $this->getPrintIdentifier($journal);
            $onlineIdentifier = $this->getOnlineIdentifier($journal);

            $dateFirstIssueOnline = $this->getDateFirstIssueOnline($issues);
            $numFirstVolOnline = $this->getNumFirstVolOnline($issues, $dateFirstIssueOnline);
            $numFirstIssueOnline = $this->getNumFirstIssueOnline($issues, $dateFirstIssueOnline);

            $dateLastIssueOnline = $this->getDateLastIssueOnline($issues);
            $numLastVolOnline = $this->getNumLastVolOnline($issues, $dateLastIssueOnline);
            $numLastIssueOnline = $this->getNumLastIssueOnline($issues, $dateLastIssueOnline);

            $titleUrl = $this->getTitleUrl($request, $journal);
            $firstAuthor = $this->getFirstAuthor();
            $titleId = $this->getTitleId($journal);
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

        // List table entries in alphabetical order by journal title.
        usort($entries, function ($item1, $item2) {
            return strnatcasecmp($item1[0], $item2[0]);
        });

        // Trigger the "Save as" dialog.
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Content-Type: text/plain");

        // Output file header.
        foreach($headers as $header) {
            echo $header;
        }

        // Output file body.
        foreach($entries as $entry) {
            echo implode("\t",$entry) . "\n";
        }
    }

    /**
     * Get all published issues of a given journal.
     *
     * @param int $journalId
     * @return array
     */
    function getIssuesByJournalId($journalId) {
        $issueDao = DAORegistry::getDAO('IssueDAO');
        return $issueDao->getPublishedIssues($journalId)->toArray();
    }

    /**
     * Store a property common to all given issues in an array.
     *
     * @param array $issues
     * @param string $property
     */
    function getPropertyFromIssues($issues, $property) {

        // Convert nested array of Issue objects to pure nested array.
        $issues_arr = json_decode(json_encode($issues), true);

        // Create nested array filled only with '_data' property of Issue object.
        $issues_arr_data = array_column($issues_arr, '_data');

        // Create new array containing the values of the given property.
        $properties = array_column($issues_arr_data, $property);

        return $properties;
    }

    /**
     * Get the publication's title of the journal.
     *
     * @param Journal $journal
     * @return string
     */
    function getPublicationTitle($journal) {
        return $journal->getLocalizedName();
    }

    /**
     * Get the print-format identifier of the journal.
     *
     * @param Journal $journal
     * @return string
     */
    function getPrintIdentifier($journal) {
        return $journal->getData('printIssn');
    }

    /**
     * Get the online-format identifier of the journal.
     *
     * @param Journal $journal
     * @return string
     */
    function getOnlineIdentifier($journal) {
        return $journal->getData('onlineIssn');
    }

    /**
     * Get the date of the first issue avaible online.
     *
     * @param array $issues
     * @return string
     */
    function getDateFirstIssueOnline($issues) {

        // Get publication dates of given issues as array.
        $dates = $this->getPropertyFromIssues($issues, 'datePublished');

        // Return minimal value in dates array to get earliest date.
        return min($dates);
    }

    /**
     * Get the volume number of the first issue avaible online.
     *
     * @param array $issues
     * @param string $dateFirstIssueOnline
     * @return int $volumeNumber
     */
    function getNumFirstVolOnline($issues, $dateFirstIssueOnline) {
        foreach ($issues as $issue) {
            if ($issue->getData('datePublished') == $dateFirstIssueOnline) {
                $volumeNumber = $issue->getData('volume');
                break;
            }
        }
        return $volumeNumber;
    }

    /**
     * Get the issue number of the first issue avaible online.
     *
     * @param array $issues
     * @param string $dateFirstIssueOnline
     * @param int $issueNumber
     */
    function getNumFirstIssueOnline($issues, $dateFirstIssueOnline) {
        foreach ($issues as $issue) {
            if ($issue->getData('datePublished') == $dateFirstIssueOnline) {
                $issueNumber = $issue->getData('number');
                break;
            }
        }
        return $issueNumber;
    }

    /**
     * Get the date of the last issue avaible online.
     *
     * @param array $issues
     * @return string
     */
    function getDateLastIssueOnline($issues) {

        // Get publication dates of given issues as array.
        $dates = $this->getPropertyFromIssues($issues, 'datePublished');

        // Return maximal value in dates array to get latest date.
        return max($dates);
    }

    /**
     * Get the volume number of the last issue avaible online.
     *
     * @param array $issues
     * @param string $dateLastIssueOnline
     * @return int $volumeNumber
     */
    function getNumLastVolOnline($issues, $dateLastIssueOnline) {
        foreach ($issues as $issue) {
            if ($issue->getData('datePublished') == $dateLastIssueOnline) {
                $volumeNumber = $issue->getData('volume');
                break;
            }
        }
        return $volumeNumber;
    }

    /**
     * Get the issue number of the last issue avaible online.
     *
     * @param array $issues
     * @param string $dateLastIssueOnline
     * @return int $volumeNumber
     */
    function getNumLastIssueOnline($issues, $dateLastIssueOnline) {
        foreach ($issues as $issue) {
            if ($issue->getData('datePublished') == $dateLastIssueOnline) {
                $issueNumber = $issue->getData('number');
                break;
            }
        }
        return $issueNumber;
    }

    /**
     * Get the title URL.
     *
     * @param Request $request
     * @return Journal $journal
     */
    function getTitleUrl($request, $journal) {
        return $request->getRouter()->url($request, $journal->getPath());
    }

    /**
     * Get the first author's name (not avaible for journals).
     *
     * @return string
     */
    function getFirstAuthor() {
        return "";
    }

    function getTitleId($journal) {
        return $journal->getId();
    }

    /**
     * Get the embargo information.
     *
     * @return string
     */
    function getEmbargoInfo() {
        return "";
    }

    /**
     * Get the coverage depth.
     *
     * @return string
     */
    function getCoverageDepth() {
        return "fulltext";
    }

    /**
     * Get notes.
     *
     * @return string
     */
    function getNotes() {
        return "";
    }

    /**
     * Get the publisher's name of the journal.
     *
     * @param Journal $journal
     * @return string
     */
    function getPublisherName($journal) {
        return $journal->getSetting('publisherInstitution');
    }

    /**
     * Get the first author's name (not avaible for journals).
     *
     * @return string
     */
    function getPublicationType() {
        return "serial";
    }

    /**
     * Get the date when the monograph was published (not avaible for journals).
     *
     * @return string
     */
    function getDateMonographPublishedPrint() {
        return "";
    }

    /**
     * Get the date when the monograph was published online (not avaible for journals).
     *
     * @return string
     */
    function getMonographPublishedOnline() {
        return "";
    }

    /**
     * Get the monograph's volume (not avaible for journals).
     *
     * @return string
     */
    function getMonographVolume() {
        return "";
    }

    /**
     * Get the monograph's edition (not avaible for journals).
     *
     * @return string
     */
    function getMonographEdition() {
        return "";
    }

    /**
     * Get the first editor's name (not avaible for journals).
     *
     * @return string
     */
    function getFirstEditor() {
        return "";
    }

    /**
     * Get the parent publication's title id.
     *
     * @return string
     */
    function getParentPublicationTitleId() {
        return "";
    }

    /**
     * Get the preceding publication's title id.
     *
     * @return string
     */
    function getPrecedingPublicationTitleId() {
        return "";
    }

    /**
     * Get the access type.
     *
     * @return string
     */
    function getAccessType() {
        return "F";
    }

}
