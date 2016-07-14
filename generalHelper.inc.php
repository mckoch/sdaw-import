<?php

class GH {

    static function makePlzPolyPointData($rs){
        foreach ($rs as $data) {
            $js[] = array('label' => htmlentities($data['plzort99']), 'plz'=>$data['plz99'], 'poly'=>$data['the_geom']);
        }
        return json_encode($js);
    }
    
    //makeDynamicSearchInputFieldJSON

    static function makeDynamicSearchInputFieldJSON($rs) {
        $result = '';
        foreach ($rs as $data) {
            /* @var $data <type> */
            /**
             * return {
              label:  item.formatted_address,
              value: item.formatted_address,
              latitude: item.geometry.location.lat(),
              longitude: item.geometry.location.lng()
              }
             */
            $js[] = array('label' => $data['PLZ'] . " " . htmlentities($data['Ortsteil']),
                'value' => $data['PLZ'] . " " . htmlentities($data['Ortsteil']),
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude']
            );
        }
        return json_encode($js);
    }
static function makeDynamicSearchJSON($rs) {
        $result = '';
        foreach ($rs as $data) {
            /* @var $data <type> */
            /**
             * return {
              label:  item.formatted_address,
              value: item.formatted_address,
              latitude: item.geometry.location.lat(),
              longitude: item.geometry.location.lng()
              }
             */
            $js[] = array('label' => $data['PLZ'] . " " . htmlentities($data['Ortsteil']),
                'value' => $data['PLZ'] . " " . htmlentities($data['Ortsteil']),
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'id' => $data['count'],
                'description' => htmlentities(str_replace('\\', ' ', $data['Standortbezeichnung'])),
                'plz' => $data['PLZ'],
                'stellenart' => $data['Stellenart'],
                'leistungswert1' => $data['Leistungswert1'],
                'ortskennziffer' => $data['StatOrtskz'],
                'preis' => $data['Preis'],
                'beleuchtung' => $data['Beleuchtung'],
                'standortnr' => $data['Standortnr'],
                'belegdauerart' => $data['Belegdauerart'],
                'bauart' => $data['Bauart'],
                'hoehe' => $data['AbmessungenH'],
                'breite' => $data['AbmessungenB'],
                'aktiverstatus' => $data['AktiverStatus']
                
            );
            
            // $js[] =  htmlentities($data['PLZ']." ".$data['Ortsteil'] );
        }
        return json_encode($js);
         //return print_r($js);
    }

    static function makeMapResultsDiv($rs) {
        $result = '';
        foreach ($rs as $data) {
            //$result .= ". ";

            /**
             * <div class="map-location" data-jmapping="{id: 1, point: {lng: -122.2678847, lat: 37.8574888}, category: 'market'}">
              <a href="#" class="map-link">Berkeley Bowl</a>
              <div class="info-box">
              <p>A great place to get all your groceries, especially fresh fruits and vegetables. <a href="#">Merkzettel</a></p>
              </div>
              </div> 
             */
            $result .= "<div class=\"map-location ". $data['count']."\" s-class=\"". $data['count']."\" data-jmapping=\"{id: " . $data['count']
                    . ", point: {lng: " . $data['longitude'] . ", lat: " . $data['latitude'] . "}, category: '" . $data['Beleuchtung'] . "'}\">"
                    . "<span class=\"ui-icon fff-icon-folder-add\" style=\"float: left\"></span>"
                    . "<span class=\"ui-icon fff-icon-".substr($data['Stellenart'],0,2)."\" style=\"float: left\"></span>"
                    . "<span class=\"ui-icon fff-icon-bullet-yellow\" style=\"float: left\" rel=\"" . $data['Preis'] . "\"></span>"
                    . "<span class=\"ui-icon fff-icon-tag-green\" style=\"float: left\" rel=\"" . $data['Leistungswert1'] . "\"></span>"
                    . "<span class=\"ui-icon fff-icon-arrow-refresh-small\" style=\"float: left\"" . " longitude=\"". $data['longitude'] ."\" latitude=\"". $data['latitude'] ."\"></span>"
                    . "<a href=\"#\" class=\"map-link " . $data['count']." ". $data['Beleuchtung'] . "\" PLZ=\"" . $data['PLZ']
                    . "\" description=\"" . $data['Standortbezeichnung'] . "\" "
                    . " longitude=\"". $data['longitude'] ."\" latitude=\"". $data['latitude'] ."\">"
                    . "<span class=\"ui-icon fff-icon-comment\" style=\"float: left\"></span>"
                    . $data['PLZ'] . " " . $data['Ortsteil'] . "</a>"
                    . "<div class=\"info-box " . $data['Beleuchtung'] . "\"><img src=\"img/120x120/" . $data['count'] .".png\""
                    . " rel=\"". $data['count']."\"/>"
                    . $data['PLZ'] . " " . $data['Ortsteil'] . " | " . $data['Beleuchtung'] . " | " . $data['Standortbezeichnung'] . " | " . $data['count'] ." | " .$data['Stellenart']
                    . "</div></div>";

            //$result .= $data['Standortnr'];
            //print_r($data);
        }
        return $result;
    }

    static function getXmlFile($file) {

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($file);
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            echo "<pre>" . self::display_xml_error($error, $xml) . "</pre>";
        }
        libxml_clear_errors();
        return $xml;
        ;
    }

    /*
     *
     * Helper f�r SimpleXML Fehlermeldungen .
     *
     */

    static function display_xml_error($error, $xml) {
        $return = "<div class='warning'>" . $xml[$error->line - 1] . "\n";
        $return .= str_repeat('-', $error->column) . "^\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }

        $return .= trim($error->message) .
                "\n  Line: $error->line" .
                "\n  Column: $error->column";

        if ($error->file) {
            $return .= "\n  File: $error->file";
        }

        return "$return\n\n--------------------------------------------\n\n</div>";
    }

    static function dumpObject($object) {
        var_dump(get_object_vars($object));
        var_dump(get_class_methods($object));
    }

}

?>