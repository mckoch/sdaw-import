<?php

/**
 * @name CALENDAR.inc.php
 * @package SDAW_Classes
 * @author mckoch - 13.07.2011
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 * @license No GPL, no CC. Property of author.
 *
 * SDAW Classes:CALENDAR
 *
 *
 */

require_once('include/dateclass.php');
require_once('include/class_daterange.php');

class CALENDAR {

    /**
     * Dekaden:
     * 1 -33. Jeweils 10 Tage
     * Ausnahmen: 1,33,34 je 14 Tage (2011)
     * 1,34 je 14 Tage (2012)
     * */
    private $dekaplan;
    private $dekaEnum = 34;
     /* private $dekakeys = array(
      * 2011
        'A' => array('duration' => array(0, 14, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10,
                11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 14, 14), 'start' => '28.12.2010'),
        'B' => array('duration' => array(0, 14, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10,
                11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 14, 14), 'start' => '31.12.2010'),
        'C' => array('duration' => array(0, 14, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10,
                11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 14, 14), 'start' => '04.01.2011')
    ); */
    /* 2012 */
    private $dekakeys = array(
        'A' => array('duration' => array(0, 14, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10,
                11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 14), 'start' => '28.12.2012'),
        'B' => array('duration' => array(0, 14, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10,
                11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 14), 'start' => '01.01.2013'),
        'C' => array('duration' => array(0, 14, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 11, 10,
                11, 10, 11, 10, 11, 10, 11, 10, 11, 10, 14), 'start' => '04.01.2013')
        );

    public function __construct() {
        $this->renderDekaPlan();
    }

    private function newDate($date, $adjust) {
        $newDate = new DateClass($date);
        return $newDate->Add('days', $adjust);
    }

    private function newDateSpan($start, $end) {
        return new DateRange($start, $end);
    }
    
    /**
     *
     * @param type $kw
     * @return array 
     * alle freien Stellen für eine KW - summarisch
     */
    public function getAvailableForWeek($kw){
        require_once ('../SDAWSQL/database.ini.php');
        $dbi = new DBI();
        $sql = "SELECT DISTINCT Zeitraum AS KW, COUNT(Standortnr) AS FreieStellen 
            FROM FRE_FREIE_TAFELN WHERE Zeitraum = ".$kw." GROUP BY Zeitraum ORDER BY KW ASC;";        
        return $dbi->arr($sql);
    }
    
    /**
     *
     * @param array $kw
     * @param array $list
     * @return array 
     * 
     * alle Termine zu Stelle(n) $list (eigene ID) 
     * in Zeitraum $kw  in KW
     */
    public function kwData($kw, $list){
        require_once ('../SDAWSQL/database.ini.php');
        $dbi = new DBI();        
        $idorloop = '';
        $kworloop = '';
        foreach ($list as $item){
            $idorloop .= " STA.count=".$item." OR ";
        }
        foreach ($kw as $item){
            $kworloop .= " FRE_FREIE_TAFELN.Zeitraum=".$item." OR ";
        }
        $idorloop = substr($idorloop,0,-4);
        $kworloop = substr($kworloop,0,-4);
        $sql="SELECT STA.count AS id, FRE.Belegdauerart,FRE_FREIE_TAFELN.Tagespreis, 
            FRE_FREIE_TAFELN.Zeitraum, FRE_FREIE_TAFELN.Rechnungstage, STA.Leistungsklasse1, 
            STA.Leistungswert1,STA.Stellenart, STA.PLZ, STA.Fotoname, STA.longitude, 
            STA.latitude, STA.StatOrtskz,STA.Standortbezeichnung,STA.Beleuchtung
            FROM STA LEFT JOIN 
            (FRE_FREIE_TAFELN,FRE) ON ( FRE.Standortnr=STA.Standortnr 
            AND FRE.Stellennr=STA.Stellennummer AND STA.Standortnr=FRE_FREIE_TAFELN.Standortnr 
            AND STA.Stellennummer=FRE_FREIE_TAFELN.Stellennr ) 
            WHERE (".$kworloop.") AND (".$idorloop.") LIMIT 100";
         return $dbi->arr($sql);
    }
    
    public function dekasInDateRange($start, $end) {
        $dekarange['startdeka'] = $this->dateInDekas($start);
        $dekarange['enddeka'] = $this->dateInDekas($end);
        // print_r($dekarange);
        $blocks = '';
        $kws = ''; //K
        foreach ($dekarange as $deka) {
            // print_r($deka);
            foreach (array($deka['A'], $deka['B'], $deka['C']) as $block) {
               // print($block).'  ';  dateToKw
                $blocks[] = $block;
                $k = $this->dateToKw($deka['date']);
                $kws[] = $k['week'];
            }
        }
        $dekarange['startdeka'] = min($blocks);
        $dekarange['enddeka'] = max($blocks);
        
        $dekarange['kwmin'] = min($kws);
        $dekarange['kwmax'] = max($kws);
        // print_r($kws);
        

        return json_encode($dekarange);
    }

    private function renderDekaPlan() {
        $i = 0;
        $doyA = 0;
        $doyB = 0;
        $doyC = 0;
        while ($i < $this->dekaEnum) {
            $i++;
            $dekaplan[$i] = array(
                'A' => array('block' => 'A', 'deka' => $i, 'duration' => $this->dekakeys['A']['duration'][$i],
                    'start' => $this->newDate($this->dekakeys['A']['start'], $doyA),
                    'end' => $this->newDate($this->dekakeys['A']['start'], $doyA + $this->dekakeys['A']['duration'][$i] - 1)
                ),
                'B' => array('block' => 'B', 'deka' => $i, 'duration' => $this->dekakeys['B']['duration'][$i],
                    'start' => $this->newDate($this->dekakeys['B']['start'], $doyB),
                    'end' => $this->newDate($this->dekakeys['B']['start'], $doyB + $this->dekakeys['B']['duration'][$i] - 1)
                ),
                'C' => array('block' => 'C', 'deka' => $i, 'duration' => $this->dekakeys['C']['duration'][$i],
                    'start' => $this->newDate($this->dekakeys['C']['start'], $doyC),
                    'end' => $this->newDate($this->dekakeys['C']['start'], $doyC + $this->dekakeys['C']['duration'][$i] - 1)
                )
            );
            $dekaplan[$i]['A']['span'] = $this->newDateSpan($dekaplan[$i]['A']['start']->ToSTring(), $dekaplan[$i]['A']['end']->ToSTring());
            $dekaplan[$i]['B']['span'] = $this->newDateSpan($dekaplan[$i]['B']['start']->ToSTring(), $dekaplan[$i]['B']['end']->ToSTring());
            $dekaplan[$i]['C']['span'] = $this->newDateSpan($dekaplan[$i]['C']['start']->ToSTring(), $dekaplan[$i]['C']['end']->ToSTring());

            $dekaplan[$i]['A']['KWstart'] = (int) date('W', strtotime($dekaplan[$i]['A']['start']->ToSTring()));
            $dekaplan[$i]['A']['KWend'] = (int) date('W', strtotime($dekaplan[$i]['A']['end']->ToSTring()));
            $dekaplan[$i]['B']['KWstart'] = (int) date('W', strtotime($dekaplan[$i]['B']['start']->ToSTring()));
            $dekaplan[$i]['B']['KWend'] = (int) date('W', strtotime($dekaplan[$i]['B']['end']->ToSTring()));
            $dekaplan[$i]['C']['KWstart'] = (int) date('W', strtotime($dekaplan[$i]['C']['start']->ToSTring()));
            $dekaplan[$i]['C']['KWend'] = (int) date('W', strtotime($dekaplan[$i]['C']['end']->ToSTring()));

            $doyA = $doyA + $this->dekakeys['A']['duration'][$i];
            $doyB = $doyB + $this->dekakeys['B']['duration'][$i];
            $doyC = $doyC + $this->dekakeys['C']['duration'][$i];
        }
        $this->dekaplan = $dekaplan;
    }

    public function getDekaNo($deka) {
        return array('dekano' => $deka, 'dekaplan' => $this->dekaplan[$deka]);
    }

    public function dateInDekas($date) {
        //$date=strtotime($date);
        foreach ($this->dekaplan as $deka) {
            if ($deka['A']['span']->inRange($date)) {
                $dekas['A'] = $deka['A']['deka'];
            }
            if ($deka['B']['span']->inRange($date)) {
                $dekas['B'] = $deka['B']['deka'];
            }
            if ($deka['C']['span']->inRange($date)) {
                $dekas['C'] = $deka['C']['deka'];
            }
            $dekas['date'] = $this->newDate($date, 0)->ToString('Y-m-d');
        }
        return $dekas;
    }

    public function weekToDekas($kw) {

        foreach ($this->dekaplan as $deka) {
            if ($deka['A']['KWstart'] == $kw) {
                $dekas['A']['start'] = $deka['A']['deka'];
            }
            if ($deka['A']['KWend'] == $kw) {
                $dekas['A']['end'] = $deka['A']['deka'];
            }
            if ($deka['A']['KWstart'] < $kw && $deka['A']['KWend'] > $kw) {
                $dekas['A']['full'] = $deka['A']['deka'];
            }

            if ($deka['B']['KWstart'] == $kw) {
                $dekas['B']['start'] = $deka['B']['deka'];
            }
            if ($deka['B']['KWend'] == $kw) {
                $dekas['B']['end'] = $deka['B']['deka'];
            }
            if ($deka['B']['KWstart'] < $kw && $deka['B']['KWend'] > $kw) {
                $dekas['B']['full'] = $deka['B']['deka'];
            }

            if ($deka['C']['KWstart'] == $kw) {
                $dekas['C']['start'] = $deka['C']['deka'];
            }
            if ($deka['C']['KWend'] == $kw) {
                $dekas['C']['end'] = $deka['C']['deka'];
            }
            if ($deka['C']['KWstart'] < $kw && $deka['C']['KWend'] > $kw) {
                $dekas['C']['full'] = $deka['C']['deka'];
            }
            if ($kw < 10)
                $kw = '0' . substr($kw, -1, 1);
            $sd = '2013-W' . $kw;
            $sd = strtotime($sd);
            $sd = date('Y-m-d', $sd);
            $dekas['KWstartDate'] = $sd;
            // $this->newDate(, 0)->BOW('Y-m-d') ;
        }
        return $dekas;
    }

    public function dateToKw($date) {
        $d = $this->newDate($date, 0);
        return array('week' => (int) date('W', strtotime($date)), 'bow' => $d->BOW(), 'eow' => $d->EOW());
    }

    public function renderFullDekaPlan() {
        return $this->dekaplan;
    }

}