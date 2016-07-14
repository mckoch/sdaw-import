<?php

/**
 * @name TextFileExport.inc.php
 * @package SDAW
 * @author mckoch
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 *
 * Text-Datei-Export ins SDAW Format
 *
 * Die Klasse generiert eine SDAW-Textdatei aus einer
 * Datenbanktabelle. Benötigt entsprechendes Defs-File in XML.
 *
 */

/**
 * TextFileExport:
 */
class TextFileExport extends DBI {

    /**
     *
     * @var <type>
     */
    protected $sdawfile;
    /**
     *
     * @var <type> 
     */
    protected $xml;

    /**
     *
     * @param <type> $xmlfile
     * @return <type>
     */
    public function doexport($xmlfile) {
        /**
         *
         */
        $this->xml = GH::getXmlFile($xmlfile);
        /**
         *
         */
        $this->sdawfile = OUTPUTDIR . $this->xml->filetype;
        /**
         *
         */
        $sdawfilehandle = fopen($this->sdawfile, "r+");
        /**
         *
         */
        if (flock($sdawfilehandle, LOCK_EX | LOCK_NB)) { // do an exclusive lock on USER
            $sql = "SELECT   ";
            $sqlfieldlist = "";
            foreach ($this->xml->sdawfieldset->field as $field) {
                $sqlfieldlist .= $field->title . ",";
            }
            $sql .= substr($sqlfieldlist, 0, -1) . " FROM  " . $this->xml->filetype . " ;";
            $rs = parent::rs($sql);
           
            print "<pre>";
            foreach ($rs as $data){
                $i = 0;
                foreach ($this->xml->sdawfieldset->field as $field) {

                    if ($field->type == 'Char'){
                        print str_pad($data[$i], $field->length);
                        /**
                         * @todo replace print with file
                         */

                    } else {
                        print str_pad($data[$i], $field->length, '0',STR_PAD_LEFT);


                    }
                    $i++;
                }
            print ' * '.PHP_EOL;



           }

            return;
        } else
            return "File $this->sdawfile locked!";
    }

}

?>
