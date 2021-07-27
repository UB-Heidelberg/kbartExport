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
        
        // Configure and build the name of the .txt file
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
            "Print ISSN\t",
            "Publisher\n"
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
