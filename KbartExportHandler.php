<?php

/**
 * @file KBARTExportHandler.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Copyright (c) 2023 Heidelberg University Library
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class KBARTExportHandler
 */

// import('classes.handler.Handler');
namespace APP\plugins\generic\kbartExport;

use APP\plugins\generic\kbartExport\KbartExportPlugin;
use APP\core\Application;
use APP\handler\Handler;

use PKP\plugins\PluginRegistry;
use PKP\db\DAORegistry;

class KbartExportHandler extends Handler {

    /** @var KbartExportPlugin The kbart export plugin */
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

        $testSubmission = $submissionDao->getById(855);
        $publication = $testSubmission->getCurrentPublication();
        $publicationFormats = $publication->getData('publicationFormats');

        // Iterate over publication formats considering only those with a physical format.
        // foreach ($publicationFormats as $publicationFormat) {
        //     if ($publicationFormat->getData('physicalFormat') == 1) {
        //     
        //         // Get all publication dates and store them in an array.
        //         $publicationDates = $publicationFormat->getPublicationDates()->toArray();

        //         // Iterate over publication dates
        //         if (isset($publicationDates) && !empty($publicationDates)) {
        //             foreach ($publicationDates as $publicationDate) {
        //                 
        //                 // Get the role of the publication date.
        //                 $role = $publicationDate->getData('role');
        //                 
        //                 if (isset($role) && !empty($role)) {
        //                     if ($role == '11') {
        //                         // Get "Date of first publication"
        //                         $date = date_parse_from_format('Ymd', $publicationDate->getDate());
        //                         return $date['year'] . '-' . $date['month'] . '-' . $date['day'];
        //                         // Stop both foreach loops
        //                         break 2;
        //                     } elseif ($role == '01') {
        //                         // Get "Publication date"
        //                         $date = date_parse_from_format('Ymd', $publicationDate->getDate());
        //                         return $date['year'] . '-' . $date['month'] . '-' . $date['day'];
        //                         break 2;
        //                     }
        //                 }
        //             }
        //        }
        //     }
        // }

        foreach($monographs as $monograph) {            
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
            $publisherName = $this->getPublisherName($press);
            $publicationType = $this->getPublicationType();

            $dateMonographPublishedPrint = $this->getDateMonographPublishedPrint($monograph);
            $dateMonographPublishedOnline = $this->getMonographPublishedOnline($monograph);
            $monographVolume = $this->getMonographVolume($monograph);
            $monographEdition = $this->getMonographEdition($monograph);
            $firstEditor = $this->getFirstEditor($monograph);
            $parentPublicationTitleId = $this->getParentPublicationTitleId($press, $monograph);
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
     * Get the publication's title of the monograph.
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
     * Get the print-format identifier of the monograph.
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
                    if (isset($isbn) && !empty($isbn)) {
                        return $isbn;
                    } else {
                        return "";
                    }
                    break;
                }
            }
        }
    }

    /**
     * Get the online-format identifier of the monograph.
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
                    if (isset($isbn) && !empty($isbn)) {
                        return $isbn;
                    } else {
                        return "";
                    }
                    break;
                }
            }
        }
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
        $primaryAuthor = $monograph->getPrimaryAuthor();
        if (!isset($primaryAuthor)) {
            $authors = $monograph->getAuthors();
            $primaryAuthor = $authors[0];
        }
        return $primaryAuthor->getFullName();
    }

    function getTitleId($monograph) {
        $publication = $monograph->getCurrentPublication();
        $doi = $publication->getData('pub-id::doi');
        if (isset($doi) && !empty($doi)) {
            return "https://doi.org/" . $doi;
        } else {
            return "";
        }
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
     * Get the publisher's name of the monograph.
     *
     * @param Journal $journal
     * @return string
     */
    function getPublisherName($press) {
        return $press->getData('publisher');
    }

    /**
     * Get the publication type.
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
        $publication = $monograph->getCurrentPublication();
        $publicationFormats = $publication->getData('publicationFormats');
        
        // foreach ($publicationFormats as $publicationFormat) {
        //     if ($publicationFormat->getData('physicalFormat') == 1) {
        //         $publicationDates = $publicationFormat->getPublicationDates()->toArray();
        //         if (isset($publicationDates) && !empty($publicationDates)) {
        //             //error_log("publicationDates: " . var_export($publicationDates);
        //             $date = date_parse_from_format('Ymd', $publicationDates[0]->getDate());
        //             $publicationDate = $date['year'] . '-' . $date['month'] . '-' . $date['day'];
        //         } else {
        //             $publicationDate = "";
        //         }
        //         break;
        //     }
        // }
        // return $publicationDate;

        // Iterate over publication formats considering only those with a physical format.
        foreach ($publicationFormats as $publicationFormat) {
            if ($publicationFormat->getData('physicalFormat') == 1) {
                
                // Get all publication dates and store them in an array.
                $publicationDates = $publicationFormat->getPublicationDates()->toArray();

                // Iterate over publication dates
                if (isset($publicationDates) && !empty($publicationDates)) {
                    foreach ($publicationDates as $publicationDate) {

                        // Get the role of the publication date.
                        $role = $publicationDate->getData('role');

                        if (isset($role) && !empty($role)) {
                            if ($role == '11') {
                                // Get "Date of first publication"
                                $date = date_parse_from_format('Ymd', $publicationDate->getDate());
                                return $date['year'] . '-' . $date['month'] . '-' . $date['day'];
                                // Stop both foreach loops
                                break 2;
                            } elseif ($role == '01') {
                                // Get "Publication date"
                                $date = date_parse_from_format('Ymd', $publicationDate->getDate());
                                return $date['year'] . '-' . $date['month'] . '-' . $date['day'];
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get the date when the monograph was published online (not avaible for journals).
     *
     * @return string
     */
    function getMonographPublishedOnline($monograph) {
        $publication = $monograph->getCurrentPublication();
        return $publication->getData('datePublished');
    }

    /**
     * Get the monograph's volume (not avaible for journals).
     *
     * @return string
     */
    function getMonographVolume($monograph) {
        return $monograph->getSeriesPosition();
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
        $firstEditor = $monograph->getCurrentPublication()->getEditorString();
        //error_log("FirstEditor: " . var_export($firstEditor,true));
        return $firstEditor;
    }

    /**
     * Get the parent publication's title id.
     *
     * @return string
     */
    function getParentPublicationTitleId($press, $monograph) {
        $publication = $monograph->getCurrentPublication();
        $seriesId = $publication->getData('seriesId');
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /* @var $seriesDao SeriesDAO */
        $series = $seriesDao->getById($seriesId, $press->getId());
        if (isset($series) && !empty($series)) {
            return $series->getLocalizedTitle();
        } else {
            return "";
        }
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
