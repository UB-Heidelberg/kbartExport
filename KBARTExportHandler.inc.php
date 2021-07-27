<?php

/**
 * @file StaticPagesHandler.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.staticPages
 * @class StaticPagesHandler
 * Find static page content and display it when requested.
 */

import('classes.handler.Handler');

class KBARTExportHandler extends Handler {
    /** @var StaticPagesPlugin The static pages plugin */
    static $plugin;

    /** @var StaticPage The static page to view */
    static $staticPage;


    /**
     * Provide the static pages plugin to the handler.
     * @param $plugin StaticPagesPlugin
     */
    static function setPlugin($plugin) {
        self::$plugin = $plugin;
    }

    /**
     * Set a static page to view.
     * @param $staticPage StaticPage
     */
    static function setPage($staticPage) {
        self::$staticPage = $staticPage;
    }

    /**
     * Handle index request (redirect to "view")
     * @param $args array Arguments array.
     * @param $request PKPRequest Request object.
     */
    function index($args, $request) {
        
        // Configure and build the name of the .txt file
        // [ProviderName]_[Region/Consortium]_[PackageName]_[YYYY-MM-DD].txt
        $ProviderName = "UBHeidelberg";
        $RegionConsortium = "Global";
        $PackageName = "OJSJournals";
        $timestamp = time();
        $date = date("Y-m-d",$timestamp);

        $fileName = $ProviderName . "_" . $RegionConsortium . "_" . $PackageName . "_" . $date . ".txt";

        // Define file header 
        $headers = [
            "Journal-Titel\t",
            "Journal-ID\t",
            "URL\t",
            "Online ISSN\t",
            "Print ISSN\n"
        ];

        // Build Rows
        $journals = DAORegistry::getDAO('JournalDAO')->getAll(true)->toArray();
        foreach($journals as $journal) {
            $title = $journal->getLocalizedName();
            //$Id = $journal->getData('id');
            $url = $request->getRouter()->url($request, $journal->getPath());
            $printIssn = $journal->getSetting('printIssn');
            $onlineIssn = $journal->getSetting('onlineIssn');
            $publisherName = $journal->getSetting('publisherInstitution');
            
            $entry = [
                $title,
                $url,
                $printIssn,
                $onlineIssn,
                $publisherName
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
