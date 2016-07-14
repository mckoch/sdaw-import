<?php

/**
 * @name VERSION.php
 * @package SDAW_Classes
 * @author mckoch - 09.06.2011
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 * @license No GPL, no CC. Property of author.
 *
 * SDAW Classes:VERSION
 * Versionierung der SDAW-Datein: Kopfdaten gem. Tabelle KOPFSAETZE
 *
 */
class VERSION extends DBI {

    public $headerdefs;
    protected $versiondata;
    protected $vdata;

    public function setHeaderDefs($headerdefs) {
        $this->headerdefs = $headerdefs;
    }

    public function setVersionData($line) {
        $this->versiondata = $line;
    }

    public function registerFile() {
        $sql = "INSERT INTO " . $this->headerdefs->filetype . "  (";
        $sqlfieldlist = "";
        $sqlvalues = "";
        foreach ($this->headerdefs->sdawfieldset->field as $field) {
           
            $sqlfieldlist .= $field->title . ",";
            if ($field->type == 'Char') {
                $sqlvalues .= parent::gqstr(substr($this->versiondata, $field->startpos - 1, $field->length)) . ",";
            } else {
                $sqlvalues .= substr($this->versiondata, $field->startpos - 1, $field->length) . ",";
            }
        }
        $sql .= substr($sqlfieldlist, 0, -1) . ") VALUES (" . substr($sqlvalues, 0, -1) . ");";
        parent::sqldebug(false);
        return parent::exec($sql);
        
    }

}

?>