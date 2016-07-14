<?php

class DISP {

    function __construct() {

    }

    protected $params = '';

    public function exec($exec, $params='') {
        try {
            switch ($exec) {
                case 'info':
                    $db = new DBI;
                    /**
                     * schauderhaftes Return (screen only)!!!
                     * @todo korrektes Objekt erzeugen
                     */
                    return $db->countRecords() . '<br/>' . $db->listTables() . '<br/>';
                    break;
                /**
                 * aus GET: PLZ Polygone suchen.
                 * _keine_ Suche im SDAW-Bestand!
                 */
                case 'plzpolygons':
                    require_once (INCLUDEDIR . 'FINDER.inc.php');
                    $f = new FINDER;
                    //$f->setparams($this->params);
                    //print_r($this->params);
                    return $f->plz2polygons(json_decode($_GET['pastedata']));
                    break;
                /**
                 * aus POST: Eingabe von GDM datasets
                 * das ist extrem unsauber: $_POST durch this->params ersetzen.
                 */
                case 'userdata':
                    require_once (INCLUDEDIR . 'FINDER.inc.php');
                    $f = new FINDER;
                    $f->setparams($this->params);

                    switch ($_POST['pastedatatype']) {
                        case 'cartitems':
                            $cart = json_decode($_POST['pastedata']);
                            //$cartdata = [];
                            foreach ($cart as $i){
                                $cartdata[] = $i->id;
                            }
                            //print var_dump($cartdata);
                            return $f->sysidsearch($cartdata);
                            break;
                        case 'postcodes':
                            return $f->plzsearch(json_decode($_POST['pastedata']));
                            break;
                        case 'sdawids':
                            return $f->sdawidsearch(json_decode($_POST['pastedata']));
                            break;
                        case 'sysids':
                            return $f->sysidsearch(json_decode($_POST['pastedata']));
                            break;
                        case 'coords':
                            return $f->coordsearch(json_decode($_POST['pastedata']));
                            break;
                        case 'gdmdata':
                            break;
                    }

                    break;
                case 'polygon':
                    require_once (INCLUDEDIR . 'FINDER.inc.php');
                    $f = new FINDER;
                    $f->setparams(json_decode($this->params));
                    return $f->polygonSearch();
                    break;
                case 'rectangle':
                    require_once (INCLUDEDIR . 'FINDER.inc.php');
                    $f = new FINDER;
                    $f->setparams($this->params);
                    return $f->rectangleSearch();
                    break;
                case 'gpos':
                    require_once (INCLUDEDIR . 'FINDER.inc.php');
                    $f = new FINDER;
                    $f->setparams($this->params);
                    return $f->gposAreaSearch();
                    break;
                case 'dynamicsearch':
                    require_once (INCLUDEDIR . 'FINDER.inc.php');
                    $f = new FINDER;
                    $f->setparams($this->params);
                    return $f->doDynamicSearch();
                    break;

                case 'find':
                    require_once (INCLUDEDIR . 'FINDER.inc.php');
                    $f = new FINDER;
                    $f->setparams($this->params);
                    return $f->doSearch();
                    break;

                case 'init':
                    return new INIT;
                    break;

                case 'newtable':
                    require_once (INCLUDEDIR . 'XmlTableStructureImport.inc.php');
                    return new XmlTableStructureImport;
                    break;

                case 'insert':
                    require_once (INCLUDEDIR . 'TextFileImport.inc.php');
                    return new TextFileImport;
                    break;

                case 'export':
                    require_once (INCLUDEDIR . 'TextFileExport.inc.php');
                    return new TextFileExport;
                    break;
            }
            return false;
        } catch (Exception $e) {
            echo "<pre>" . $e . "</pre>";
        }
    }

    public function setparams($params) {
        $this->params = $params;
        return;
    }

}

?> 