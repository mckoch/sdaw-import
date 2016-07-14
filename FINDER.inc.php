<?php

/**
 * @name FINDER.inc.php
 * @package SDAW_Classes
 * @author mckoch - 14.04.2011
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 * @license No GPL, no CC. Property of author.
 *
 * SDAW Classes:FINDER
 *
 *
 */
class FINDER extends DBI {

    private $params, $stafilter;

    public function setparams($params) {
        $this->params = $params;
        $sta = explode(',', $_GET['sta']);
        $usta = explode(',', $_GET['usta']);
        $bel = '';
        
        $combinations = '';
        foreach ($sta as $hs) {
            if ($hs != 'U' && $hs != 'B') {
                foreach ($usta as $ns) {
                    $combinations[] = substr($hs,0,2) . substr($ns,0,2);      
                }
            } else {
                if ($hs=="U") $bel[] = " beleuchtung LIKE 'U' ";
                if ($hs=="B") $bel[] = " beleuchtung LIKE 'B' ";
            }
        }
        if (is_array($bel)){
            //print_r($bel);
            $bsql = $bel[0];
            if (count($bel) === 2){
                $bsql .= " OR ".$bel[1];
            }
        }
        //print $bsql;
        $fsql = " stellenart IN ( ";
         foreach($combinations as $el){
             $fsql .= "'".$el."',";
         }
        $this->stafilter = substr($fsql,0,-1).") AND (".$bsql.") ";
        return true;
    }

    /**
     *
     * @param <array> $data
     * @return <array>
     * @todo: Suchen versäubern: SELECT-Auswahlreihenfolge!
     */
    public function plz2polygons($data) {
        $sql = "SELECT plz99, plzort99, the_geom FROM   post_code_areas WHERE ";
        foreach ($data as $plz) {
            $sql .= "plz99_n=" . $plz . " OR plz99 LIKE '" . $plz . "' OR ";
        }
        $sql = substr($sql, 0, -3);
        parent::sqldebug(false);
        return parent::rs($sql);
    }

    public function coordsearch($data) {
        /**
         * @todo Umkreissuche integrieren!(?redundant?)
         */
        $gfk = explode(',', $this->params['gfk']);
        $prc = explode(',', $this->params['prc']);
        $sql = "SELECT * FROM STA WHERE  leistungswert1 >= $gfk[0] AND leistungswert1 <= $gfk[1] AND
        preis >=$prc[0] AND preis <=$prc[1] AND ".$this->stafilter." AND (";
        $or = '';
        foreach ($data as $coords) {
            $or .= "CONCAT(latitude,' ',longitude) LIKE '" . $coords . "' OR ";
        }
        $sql .= substr($or, 0, -3) . ")";
        parent::sqldebug(false);
        return parent::rs($sql);
    }

    public function sysidsearch($data) {
        $gfk = explode(',', $this->params['gfk']);
        $prc = explode(',', $this->params['prc']);
        $sql = "SELECT * FROM STA WHERE  ("; 
        //leistungswert1 >= $gfk[0] AND leistungswert1 <= $gfk[1] AND preis >=$prc[0] AND preis <=$prc[1] AND ".$this->stafilter." AND (";
        $or = '';
        foreach ($data as $id) {
            $or .= "count=" . $id . " OR ";
        }
        $sql .= substr($or, 0, -3) . ")";
        parent::sqldebug(false);
        return parent::rs($sql);
    }

    public function sdawidsearch($data) {
        $gfk = explode(',', $this->params['gfk']);
        $prc = explode(',', $this->params['prc']);
        $sql = "SELECT * FROM STA WHERE  leistungswert1 >= $gfk[0] AND leistungswert1 <= $gfk[1] AND
        preis >=$prc[0] AND preis <=$prc[1] AND ".$this->stafilter." AND (";
        $or = '';
        foreach ($data as $standortnr) {
            $or .= "Standortnr=" . $standortnr . " OR ";
        }
        $sql .= substr($or, 0, -3) . ")";
        parent::sqldebug(false);
        return parent::rs($sql);
    }

    public function plzsearch($data) {
        $gfk = explode(',', $this->params['gfk']);
        $prc = explode(',', $this->params['prc']);
        $sql = "SELECT * FROM STA WHERE  leistungswert1 >= $gfk[0] AND leistungswert1 <= $gfk[1] AND
        preis >=$prc[0] AND preis <=$prc[1] AND ".$this->stafilter." AND (";
        $or = '';
        foreach ($data as $plz) {
            $or .= "plz=" . $plz . " OR ";
        }
        $sql .= substr($or, 0, -3) . ")";
        parent::sqldebug(false);
        return parent::rs($sql);
    }

    public function polygonSearch() {
        $bbox = 'POLYGON((';
        $i = 0;
        /**
         * manchmal hasse ich google.......
         */
        foreach ($this->params->b as $param) {
            $arr = array_keys(get_object_vars($param));
            $bbox .= $param->$arr[0] . ' ' . $param->$arr[1] . ',';
            if ($i == 0) {
                $start = $param->$arr[0] . ' ' . $param->$arr[1];
            }
            $i++;
        }
        $bbox .= $start . "))";
        $gfk = explode(',', $_GET['gfk']);
        $prc = explode(',', $_GET['prc']);
        parent::sqldebug(false);
        $sql = "SELECT * FROM STA  WHERE leistungswert1 >=$gfk[0] AND leistungswert1 <=$gfk[1] AND 
        preis >=$prc[0] AND preis <=$prc[1] AND ".$this->stafilter." AND
        Intersects( GeomFromText( CONCAT(
            'POINT(', STA.latitude, ' ', STA.longitude ,')'),4326) , GeomFromText('" . $bbox . "',4326) )";
        return parent::rs($sql);
    }

    public function rectangleSearch() {
        //var api = 'api.php?command=6&bounds='+lng.toUrlValue();
        // bounds = lat_lo,lng_lo,lat_hi,lng_hi
        $pms = explode(',', $this->params);
        $gfk = explode(',', $_GET['gfk']);
        $prc = explode(',', $_GET['prc']);
        $sql = "SELECT * FROM STA WHERE  leistungswert1 >= $gfk[0] AND leistungswert1 <= $gfk[1] AND
        preis >=$prc[0] AND preis <=$prc[1] AND
        latitude >= $pms[0] AND latitude <= $pms[2] AND
        longitude >= $pms[1] AND longitude <=$pms[3] AND ".$this->stafilter." ";
        parent::sqldebug(false);
        return parent::rs($sql);
    }

    public function gposAreaSearch() {
        $lat = $this->params['latitude'];
        $lng = $this->params['longitude'];
        $distance = $this->params['umkreis'] / 1000;
        $gfk = explode(',', $_GET['gfk']);
        $prc = explode(',', $_GET['prc']);

        $sql = "SELECT *, ( 6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude )
            - radians($lng) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) )
            AS distance FROM STA WHERE leistungswert1 >= $gfk[0] AND leistungswert1 <=$gfk[1] AND
        preis >=$prc[0] AND preis <=$prc[1] AND ".$this->stafilter."
        HAVING distance <= $distance ORDER BY distance LIMIT 0 , 10000;";

        parent::sqldebug(false);
        return parent::rs($sql);
    }

    public function doSearch() {

        if ($this->params == 'default') {
            /**
             * load default parameter set from preconfigured table
             */
            //$sql=$this->params;
            $sql = 'SELECT * FROM STA_DEFAULT'; // WHERE ' . parent::gqstr($this->params['plz']);
            $sql .= ' ORDER BY latitude,longitude LIMIT 200';
            //
            //DUMMY 
        } else {

            $p = explode(' ', $this->params['value']);
            $gfk = explode(',', $_GET['gfk']);
            $prc = explode(',', $_GET['prc']);

            if (array_count_values($p) > 1) {
                $sql = 'SELECT * FROM STA  WHERE  leistungswert1 >=' . $gfk[0] . ' AND leistungswert1 <=' . $gfk[1] . ' AND 
        preis >=' . $prc[0] . ' AND preis <=' . $prc[1] . ' AND '.$this->stafilter.' AND ';
                foreach ($p as $psub) {
                    if (is_numeric($psub)) {
                        $sql .= ' PLZ LIKE \'' . $psub . '\'';
                    } else {
                        $sql .= ' AND Ortsteil LIKE \'' . html_entity_decode($psub) . '%\' ';
                    }
                }
                $sql .= ' ORDER BY latitude,longitude LIMIT 200';
            }
            if ($this->params['latitude'] > 0 && $this->params['longitude'] > 0) {
                $sql = 'SELECT * FROM STA  WHERE  leistungswert1 >=' . $gfk[0] . ' AND leistungswert1 <=' . $gfk[1] . ' AND 
        preis >=' . $prc[0] . ' AND preis <=' . $prc[1] . ' AND longitude LIKE \'' . $this->params['longitude']
                        . '\' AND latitude  LIKE \'' . $this->params['latitude'] . '\' LIMIT 2000';
            }
        }
        //DUMMY
        parent::sqldebug(false);
        return parent::rs($sql);
    }

    public function doDynamicSearch() {

        $p = explode(' ', trim($this->params));
        $gfk = explode(',', $_GET['gfk']);
        $prc = explode(',', $_GET['prc']);

        if (count($p) > 1) {
            //return;
            $sql = 'SELECT DISTINCT PLZ,Ortsteil,latitude,longitude FROM STA  WHERE  leistungswert1 >=' . $gfk[0] . ' AND leistungswert1 <=' . $gfk[1] . ' AND 
        preis >=' . $prc[0] . ' AND preis <=' . $prc[1] . ' AND '.$this->stafilter.' AND ';
            $i = 0;
            foreach ($p as $psub) {
                if (is_numeric($psub)) {
                    if ($i > 0)
                        $sql .= " AND ";
                    $sql .= ' CAST(PLZ AS CHAR) LIKE \'' . $psub . '%\'';
                    $i++;
                } else {
                    if ($i > 0)
                        $sql .= " AND ";
                    $sql .= ' ( Ortsteil LIKE \'%' . substr(html_entity_decode($psub), 0, 10) . '%\' ';
                    $sql .= ' OR  Standortbezeichnung LIKE \'%' . substr(html_entity_decode($psub), 0, 10) . '%\') ';
                    $i++;
                }
            }
            $sql .= ' ORDER BY latitude,longitude, Ortsteil LIMIT 200';
        } else {
            /**
             * kein Array!
             */
            if (is_numeric($this->params)) {
                //numerisch = PLZ suchen'
                $sql = 'SELECT  DISTINCT PLZ,Ortsteil,latitude,longitude FROM STA WHERE  leistungswert1 >=' . $gfk[0] . ' AND leistungswert1 <=' . $gfk[1] . ' AND 
        preis >=' . $prc[0] . ' AND preis <=' . $prc[1] . ' AND '.$this->stafilter.' AND CAST(PLZ AS CHAR) LIKE \'' . substr($this->params, 0, 5) . '%\' ';
            } else {
                //alphanumerisch = Stadt suchen
                $sql = 'SELECT DISTINCT PLZ,Ortsteil, latitude,longitude FROM STA WHERE  leistungswert1 >=' . $gfk[0] . ' AND leistungswert1 <=' . $gfk[1] . ' AND 
        preis >=' . $prc[0] . ' AND preis <=' . $prc[1] . ' AND '.$this->stafilter.' AND (Ortsteil LIKE \'%' . substr($this->params, 0, 5)
                        . '%\' OR Standortbezeichnung LIKE \'%' . substr($this->params, 0, 15) . '%\') ';
            }
            $sql .= ' ORDER BY latitude,longitude,PLZ, Ortsteil LIMIT 200';
        }
        parent::sqldebug(false);
        return parent::rs($sql);
    }

}

?>