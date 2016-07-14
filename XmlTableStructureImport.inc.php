<?php

class XmlTableStructureImport extends DBI {

    protected $xmlfile;
    protected $xml;

    public function getXmlFile($file) {
        $this->xmlfile = $file;
        $this->xml = GH::getXmlFile($this->xmlfile);
        return;
    }

    public function writeNewTable() {
        parent::sqldebug(true);
        self::dropTable();
        self::createTable();
        self::createFields();
        self::createIndexes();
        self::createPrimaryIndex();
        return;
    }

    /*
     * SQL Tabelle löschen -> Neu-Initialisierung
     */

    private function dropTable() {
        if ($this->xml->loop) {
            foreach ($this->xml->loop as $loop) {
                parent::exec("DROP TABLE IF EXISTS " . $this->xml->filetype . $loop->title . ";");
            }
        }
        $table = $this->xml->filetype;
        return parent::exec("DROP TABLE IF EXISTS " . $table . ";");
    }

    /**
     * SQL Tabelle erstellen (aus XML-Dateinamen)
     * loops: Hilfstabellen für variable Satzlängen
     */
    private function createTable() {
        /**
         * Loops berücksichtigen:
         * Hilfstabelle/n erstellen
         */
        if ($this->xml->loop) {
            foreach ($this->xml->loop as $loop) {
                parent::exec("CREATE TABLE IF NOT EXISTS " . $this->xml->filetype . $loop->title
                        . " (count MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (count));");
            }
        }
        /**
         * Standardrückgabe / Stadardaktion: Hauptttabelle erzegen
         */
        return parent::exec("CREATE TABLE IF NOT EXISTS " . $this->xml->filetype
                        . " (count MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (count));");
    }

    /*
     * Felder aus XML-File: Erstellen in Tabelle
     */

    private function createFields() {
        $headerdefs = GH::getXmlFile($GLOBALS[substr($this->xml->filetype,0,3) . 'KopfDatenFile']);

        foreach ($this->xml->sdawfieldset->field as $field) {
            parent::exec("ALTER TABLE " . $this->xml->filetype . " ADD `" . $field->title . "` "
                    . $field->type . "(" . $field->length . ") NOT NULL;");
            /**
             * now: creating subtable for i.e. FRE:
             * handle loops A -> Z:
             * if $field = looptrigger:
             * $field -> var length$field -> tablename
             * this -> createTable($this->xml->filetype_LOOP)
             * each $field -> fields[] !!!
             */
        }
        if ($headerdefs->series) {
            print "series: ";
            $series = $headerdefs->series;
            foreach ($series->field as $field) {
                parent::exec("ALTER TABLE " . $this->xml->filetype . " ADD `" . $field->title . "` "
                        . $field->type . "(" . $field->length . ") NOT NULL;");
            }
        }
        if ($this->xml->loop) {
            print "loop: ";
            foreach ($this->xml->loop as $loop) {
                foreach ($this->xml->loop->field as $field) {
                    parent::exec("ALTER TABLE " . $this->xml->filetype . $loop->title
                            . " ADD `" . $field->title . "` " . $field->type . "("
                            . $field->length . ") NOT NULL;");
                }
                /**
                 * eindeutigen Index hinzufügen und 
                 * Einzeldatensätze mit Tafel verbinden!
                 */
                foreach ($this->xml->uniqueitemid->index as $field) {
                    parent::exec("ALTER TABLE " . $this->xml->filetype . $loop->title
                            . " ADD `" . $field->name . "` " . $field->type . "("
                            . $field->length . ") NOT NULL;");
                }
            }
        }
        return;
    }

    /*
     * Indexes erstellen aus XML-Datei
     */

    private function createIndexes() {
        print "indexes: ";
        foreach ($this->xml->keys->index as $index) {
            parent::exec("CREATE FULLTEXT INDEX `IDX_" . $this->xml->filetype . '_' . $index . "` ON `"
                    . $this->xml->filetype
                    . "` (" . $index . ");");
        }
        if ($this->xml->loop) {
            foreach ($this->xml->loop as $loop) {
                foreach ($loop->keys->index as $index) {
                    parent::exec("CREATE FULLTEXT INDEX `IDX_" . $this->xml->filetype . $loop->title . '_' . $index . "` ON `"
                            . $this->xml->filetype . $loop->title . "` (" . $index . ");");
                }
            }
        }
        return;
    }

    /*
     * Primärindex erstellen aus XML-Datei
     */

    private function createPrimaryIndex() {
        if ($this->xml->loop) {
            foreach ($this->xml->loop as $loop) {
                return parent::exec("ALTER TABLE " . $this->xml->filetype . $loop->title
                                . " ADD CONSTRAINT  PIDX_" . $this->xml->filetype . $loop->title
                                . " UNIQUE (" . $loop->keys->primary . ");");
            }
        }
        return parent::exec("ALTER TABLE " . $this->xml->filetype
                        . " ADD CONSTRAINT PIDX_" . $this->xml->filetype . " UNIQUE ("
                        . $this->xml->keys->primary . ");");
    }

}

?>