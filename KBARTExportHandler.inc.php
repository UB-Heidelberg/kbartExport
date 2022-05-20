<?php

/**
 * @file KBARTExportHandler.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
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

        // Get all monographs of the press.
        $pressDao = DAORegistry::getDAO('PressDAO');
        $press = $request->getContext();
        $submissionDao = DAORegistry::getDAO('SubmissionDAO');
        $monographs = $submissionDao->getByContextId($press->getId())->toArray();

        foreach($monographs as $monograph) {

            $publication = $monograph->getCurrentPublication();
            $publicationFormats = $publication->getData('publicationFormats');
            
            //foreach ($publicationFormats as $publicationFormat) {
            //    $identificationCode = $publicationFormat->getIdentificationCodes();
            //    error_log("getIdentificationCodes: " . var_export($identificationCode,true));
            //}
            //$physicalFormats = $this->getPropertyFromPublicationFormats($publicationFormats, 'physicalFormat');
            //error_log(var_export($physicalFormats,true));
            //die();

            $publicationTitle = $this->getPublicationTitle($monograph);
            $printIdentifier = $this->getPrintIdentifier($monograph);
            $onlineIdentifier = $this->getOnlineIdentifier($monograph);

            $dateFirstIssueOnline = $this->getDateFirstIssueOnline();
            $numFirstVolOnline = $this->getNumFirstVolOnline();
            $numFirstIssueOnline = $this->getNumFirstIssueOnline();

            $dateLastIssueOnline = $this->getDateLastIssueOnline();
            $numLastVolOnline = $this->getNumLastVolOnline();
            $numLastIssueOnline = $this->getNumLastIssueOnline();

            $titleUrl = $this->getTitleUrl($request, $monograph);
            $firstAuthor = $this->getFirstAuthor($monograph);
            $titleId = $this->getTitleId($monograph);
            $embargoInfo = $this->getEmbargoInfo();
            $coverageDepth = $this->getCoverageDepth();
            $notes = $this->getNotes();
            $publisherName = $this->getPublisherName($monograph);
            $publicationType = $this->getPublicationType();

            $dateMonographPublishedPrint = $this->getDateMonographPublishedPrint($monograph);
            $dateMonographPublishedOnline = $this->getMonographPublishedOnline($monograph);
            $monographVolume = $this->getMonographVolume($monograph);
            $monographEdition = $this->getMonographEdition($monograph);
            $firstEditor = $this->getFirstEditor($monograph);
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
            //error_log(var_export($entry,true));
            echo implode("\t",$entry) . "\n";
        }
    }

    /**
     * Store a property common to all given monographs in an array.
     *
     * @param array $issues
     * @param string $property
     */
    function getPropertyFromPublicationFormats($publicationFormats, $property) {

        // Convert nested array of Issue objects to pure nested array.
        $publicationFormats_arr = json_decode(json_encode($publicationFormats), true);

        // Create nested array filled only with '_data' property of Issue object.
        $publicationFormats_arr_data = array_column($publicationFormats_arr, '_data');

        // Create new array containing the values of the given property.
        $properties = array_column($publicationFormats_arr_data, $property);

        return $properties;
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
    function getPropertyFromMonographs($monographs, $property) {

        // Convert nested array of Issue objects to pure nested array.
        $monographs_arr = json_decode(json_encode($monographs), true);

        // Create nested array filled only with '_data' property of Issue object.
        $monographs_arr_data = array_column($monographs_arr, '_data');

        // Create new array containing the values of the given property.
        $properties = array_column($monographs_arr_data, $property);

        return $properties;
    }

    /**
     * Get the publication's title of the journal.
     *
     * @param Submission $monograph
     * @return string
     */
    function getPublicationTitle($monograph) {
        $publication = $monograph->getCurrentPublication();
        // TODO: If monograph has no localized titles, getLocalizedTitle returns array
        return $publication->getLocalizedTitle();
    }

    /**
     * Get the print-format identifier of the journal.
     *
     * @param Journal $journal
     * @return string
     */
    function getPrintIdentifier($monograph) {
        $publication = $monograph->getCurrentPublication();
        $publicationFormats = $publication->getData('publicationFormats');
        foreach ($publicationFormats as $publicationFormat) {
            if ($publicationFormat->getData('physicalFormat') == 0) {
                $identificationCodes = $publicationFormat->getIdentificationCodes()->toArray();
                if (isset($identificationCodes) && !empty($identificationCodes)) {
                    $isbn = $identificationCodes[0]->getData('value');
                    break;
                }
            }
        }
        return $isbn;
    }

    /**
     * Get the online-format identifier of the journal.
     *
     * @param Journal $journal
     * @return string
     */
    function getOnlineIdentifier($monograph) {
        $publication = $monograph->getCurrentPublication();
        $publicationFormats = $publication->getData('publicationFormats');
        foreach ($publicationFormats as $publicationFormat) {
            if ($publicationFormat->getData('physicalFormat') == 1) {
                $identificationCodes = $publicationFormat->getIdentificationCodes()->toArray();
                if (isset($identificationCodes) && !empty($identificationCodes)) {
                    $isbn = $identificationCodes[0]->getData('value');
                    break;
                }
            }
        }
        return $isbn;
    }

    /**
     * Get the date of the first issue avaible online.
     *
     * @param array $issues
     * @return string
     */
    function getDateFirstIssueOnline() {
        return "";
    }

    /**
     * Get the volume number of the first issue avaible online.
     *
     * @param array $issues
     * @param string $dateFirstIssueOnline
     * @return int $volumeNumber
     */
    function getNumFirstVolOnline() {
        return "";
    }

    /**
     * Get the issue number of the first issue avaible online.
     *
     * @param array $issues
     * @param string $dateFirstIssueOnline
     * @param int $issueNumber
     */
    function getNumFirstIssueOnline() {
        return "";
    }

    /**
     * Get the date of the last issue avaible online.
     *
     * @param array $issues
     * @return string
     */
    function getDateLastIssueOnline() {
        return "";
    }

    /**
     * Get the volume number of the last issue avaible online.
     *
     * @param array $issues
     * @param string $dateLastIssueOnline
     * @return int $volumeNumber
     */
    function getNumLastVolOnline() {
        return "";
    }

    /**
     * Get the issue number of the last issue avaible online.
     *
     * @param array $issues
     * @param string $dateLastIssueOnline
     * @return int $volumeNumber
     */
    function getNumLastIssueOnline() {
        return "";
    }

    /**
     * Get the title URL.
     *
     * @param Request $request
     * @return Submission $monograph
     */
    function getTitleUrl($request, $monograph) {
        return $request->getRouter()->url($request, null, 'catalog', 'book', $monograph->getId());
    }

    /**
     * Get the first author's name (not avaible for journals).
     *
     * @return string
     */
    function getFirstAuthor($monograph) {
        return "";
        // return $monograph->getCurrentPublication()->getPrimaryAuthor()->getFullName();
        // $authors = $monograph->getCurrentPublication()->getData('authors');
        // $authorIds = $this->getPropertyFromMonographs($monograph, '')
    }

    function getTitleId($monograph) {
        return $monograph->getId();
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
    function getPublisherName($monograph) {
        return $monograph->getCurrentPublication()->getData('publisherInstitution');
    }

    /**
     * Get the first author's name (not avaible for journals).
     *
     * @return string
     */
    function getPublicationType() {
        return "monograph";
    }

    /**
     * Get the date when the monograph was published (not avaible for journals).
     *
     * @return string
     */
    function getDateMonographPublishedPrint($monograph) {
        return "";
    }

    /**
     * Get the date when the monograph was published online (not avaible for journals).
     *
     * @return string
     */
    function getMonographPublishedOnline($monograph) {
        return "";
    }

    /**
     * Get the monograph's volume (not avaible for journals).
     *
     * @return string
     */
    function getMonographVolume($monograph) {
        return "";
    }

    /**
     * Get the monograph's edition (not avaible for journals).
     *
     * @return string
     */
    function getMonographEdition($monograph) {
        return "";
    }

    /**
     * Get the first editor's name (not avaible for journals).
     *
     * @return string
     */
    function getFirstEditor($monograph) {
        // return $monograph->getCurrentPublication()->getEditorString();
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
