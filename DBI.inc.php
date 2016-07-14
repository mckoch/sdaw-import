<?php

/**
 * Datenbank Klasse:
 */

class DBI {

    private $conn;
    private $debug = false;

    function __construct() {
        $this->conn = $this->_dbConn();
    }

    /**
     *
     * @global <type> $dbhost
     * @global <type> $dbuname
     * @global <type> $dbpass
     * @global <type> $dbname
     * @return <type>
     *
     * ADODB Verbindung erstellen
     */

    private function _dbConn() {
        global $dbhost, $dbuname, $dbpass, $dbname;
        $conn = ADONewConnection('mysql'); // # eg. 'mysql' or 'oci8'
        $conn->debug = $this->debug;
        $conn->NConnect($dbhost, $dbuname, $dbpass, $dbname);
        return $conn;
    }

    /**
     *
     * @param <type> $sql
     * @return <type>
     *
     * SQL ausführen
     */

    public function exec($sql) {
        $this->conn->debug = $this->debug;
        if ($this->conn->Execute($sql)) {
            return true;
        } else {
            return $this->conn->ErrorNo();
        }
    }

    /** Debug für SQL
     *
     * @param <type> $debug
     */

    public function sqldebug($debug) {
        $this->debug = $debug;
    }
    /**
     *
     * @param <type> $input
     * @return <type>
     */

    public function gqstr ($input) {
        /**
         * @todo param magicquotes, see ADODB docs
         */
        return $this->conn->qstr ($input);
    }

    public function rs($sql){
        $this->conn->debug = $this->debug;
        //$this->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        
            return $this->conn->Execute($sql);
        
    }
    
    public function arr($sql){
        $this->conn->SetFetchMode(ADODB_FETCH_ASSOC);
       $arr = $this->conn ->Execute($sql);
       return $arr->GetArray();
    }
    
    public function checkNumberOfRecords($sql){
     $sql = "SELECT * FROM " . $sql .";";
        $this->conn->debug = $this->debug;
        $this->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->conn->Execute($sql)->RecordCount();   
    }

    public function countRecords(){
        $sql = "SELECT COUNT(*) FROM STA";
        $this->conn->debug = $this->debug;
        $this->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->conn->Execute($sql);
    }

    public function listTables(){
        $sql = "SHOW TABLES";
        $this->conn->debug = $this->debug;
        $this->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->conn->Execute($sql);
    }


    /**
     * SQL connection schließen
     */

    function __destruct() {
        $this->conn->Close();
    }

}

?>