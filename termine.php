<?php

/**
 * @name termine.php
 * @package SDAW_Classes
 * @author mckoch - 27.12.2011
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 * @license No GPL, no CC. Property of author.
 *
 * SDAW Classes:termine
 *
 * Dictionary für obj CALENDAR
 *
 * Hilfsroutinen / Abfragen:
 *
 * besser als Extender für obj CALENDER
 * bzw. static formatter??
 * Dekaden 2011
 *
 *
 */
require_once('include/CALENDAR.inc.php');

$cal = new CALENDAR;
$strfmt = 'D, d.m.Y';


if ($_GET['kwdata']) {

    if ($_POST['kwdata'] && $_POST['ids']) {
        $ids = json_decode($_POST['ids']);
        foreach($ids as $id){
            $idlist[] = $id->id;
        }
        $kwlist = json_decode($_POST['kwdata']);
        // print_r($idlist);print_r($kwlist);
    } else {
        $kwlist[] = substr($_GET['kwdata'], 0, 2);
        $idlist[] = $_GET['Tafel'];
    }

    return print json_encode($cal->kwData($kwlist, $idlist));

} elseif ($_GET['wochensummen']) {
    $kw = substr($_GET['wochensummen'], 0, 2);
    $kw = $cal->getAvailableForWeek($kw);

    return print json_encode($kw[0]);
} elseif ($_GET['daterange']) {
    $dekas = explode(',', $_GET['daterange']);
    $dekas = $cal->dekasInDateRange($dekas[0], $dekas[1]);

    print_r($dekas);
    return;
} elseif ($_GET['Dekade']) {
    $result = $cal->getDekaNo(substr($_GET['Dekade'], 0, 2));
    if ($_GET['json'] && $_GET['full']) {
        print json_encode($result);
        return;
    };
    $kwstartA = $cal->dateToKw($result['dekaplan']['A']['start']->ToString());
    $kwstartA = $kwstartA['week'];
    $kwendA = $cal->dateToKw($result['dekaplan']['A']['end']->ToString());
    $kwendA = $kwendA['week'];
    $kwstartB = $cal->dateToKw($result['dekaplan']['B']['start']->ToString());
    $kwstartB = $kwstartB['week'];
    $kwendB = $cal->dateToKw($result['dekaplan']['B']['end']->ToString());
    $kwendB = $kwendB['week'];
    $kwstartC = $cal->dateToKw($result['dekaplan']['C']['start']->ToString());
    $kwstartC = $kwstartC['week'];
    $kwendC = $cal->dateToKw($result['dekaplan']['C']['end']->ToString());
    $kwendC = $kwendC['week'];
    $block = $result['dekaplan']['A'];
    $displaydate = substr($block["start"]->ToString(), 0, 10);
    $jresult = '{start:"' . substr($block["start"]->ToString(), 0, 10)
            . '",end:"' . substr($block["end"]->ToString(), 0, 10)
            . '",title:"' . $block["deka"] . '. Dekade'
            . '",className:"block' . $block['block']
            . '"},';
    $block = $result['dekaplan']['B'];
    $jresult .= '{start:"' . substr($block["start"]->ToString(), 0, 10)
            . '",end:"' . substr($block["end"]->ToString(), 0, 10)
            . '",title:"' . $block["deka"] . '. Dekade'
            . '",className:"block' . $block['block']
            . '"},';
    $block = $result['dekaplan']['C'];
    $jresult .= '{start:"' . substr($block["start"]->ToString(), 0, 10)
            . '",end:"' . substr($block["end"]->ToString(), 0, 10)
            . '",title:"' . $block["deka"] . '. Dekade'
            . '",className:"block' . $block['block']
            . '"}';
    $jresult = '[' . $jresult . ']';
    print '<taconite>
    <prepend select="#results">
    <table class="ui-widget">
        <thead><tr><th>' . $_GET['Dekade'] . '. Dekade </th><th>Start</th><th>KW</th><th>Ende</th><th>KW</th><th>Dauer</th></tr></thead>
        <tr class="blockA"><td>Block A</td><td>' . $result['dekaplan']['A']['start']->ToString($strfmt)
            . '</td><td>' . $kwstartA . '</td><td>' . $result['dekaplan']['A']['end']->ToString($strfmt)
            . '</td><td>' . $kwendA . '</td><td>' . $result['dekaplan']['A']['duration'] . '</td></tr>

        <tr class="blockB"><td>Block B</td><td>' . $result['dekaplan']['B']['start']->ToString($strfmt)
            . '</td><td>' . $kwstartB . '</td><td>' . $result['dekaplan']['B']['end']->ToString($strfmt)
            . '</td><td>' . $kwendB . '</td><td>' . $result['dekaplan']['B']['duration'] . '</td></tr>
        <tr class="blockC"><td>Block C</td><td>' . $result['dekaplan']['C']['start']->ToString($strfmt)
            . '</td><td>' . $kwstartC . '</td><td>' . $result['dekaplan']['C']['end']->ToString($strfmt)
            . '</td><td>' . $kwendC . '</td><td>' . $result['dekaplan']['C']['duration'] . '</td></tr>
    </table>
    </prepend>
    <eval>
        $(\'#monatskalender\').fullCalendar(\'addEventSource\',' . $jresult . ');
            showCalendarAllBocks();
    </eval>
    </taconite>';
} elseif ($_GET['KW']) {
    $result = $cal->weekToDekas(substr($_GET['KW'], 0, 2));
    if ($_GET['json']) {
        print json_encode($result);
        return;
    }
    print '<taconite>
    <append select="#results">
    <table  class="ui-widget KW">
        <thead><tr><th>KW ' . $_GET['KW'] . '</th><th>Start</th><th>Ende</th><th>voll</th></tr></thead>
        <tr class="blockA"><td>Block A</td><td>' . $result['A']['start'] . '</td><td>' . $result['A']['end'] . '</td><td>' . $result['A']['full'] . '</td></tr>
        <tr class="blockB"><td>Block B</td><td>' . $result['B']['start'] . '</td><td>' . $result['B']['end'] . '</td><td>' . $result['B']['full'] . '</td></tr>
        <tr class="blockC"><td>Block C</td><td>' . $result['c']['start'] . '</td><td>' . $result['C']['end'] . '</td><td>' . $result['C']['full'] . '</td></tr>
    </table>
    </append>
    <eval>showCalendarAllBocks();</eval>
</taconite>';
} elseif ($_GET['DatumKW']) {
    $result = $cal->dateToKw(substr($_GET['DatumKW'], 0, 10));
    if ($_GET['json']) {
        print json_encode($result);
        return;
    }
    $enum = $cal->getAvailableForWeek($result['week']);
    print '<taconite>
    <prepend select="#kwsmalltables">
    <table class="ui-widget">
        <thead><tr><th></th><th>KW</th><th>Start</th><th>Ende</th><th>frei ges.</th></tr></thead>
        <tr class="ui-state-highlight"><td>' . $_GET['DatumKW'] . '</td><td>'
            . $result['week'] . '</td><td>'
            . $result['bow']->ToString($strfmt) . '</td><td>'
            . $result['eow']->ToString($strfmt) . '</td><td>'
            . $enum[0]['FreieStellen'].'</td>

                </tr>
    </table>
    </prepend>

    <eval>
        $(\'#monatskalender\').fullCalendar(\'addEventSource\',[{
            start: \'' . $result['bow']->ToString('Y-m-d') . '\',
            end: \'' . $result['eow']->ToString('Y-m-d') . '\',
            title: \'' . $result['week'] . '. KW: '. $enum[0]['FreieStellen'].' frei ges.\',
            className: \'ui-state-active\'
        }]);
        KWLIST.push(' . $result['week'] . ');
        $.unique(KWLIST);
        showCalendarAllBocks();
    </eval>
</taconite>';
} elseif ($_GET['DatumDeka']) {
    $result = $cal->dateInDekas(substr($_GET['DatumDeka'], 0, 10));
    if ($_GET['json']) {
        print json_encode($result);
        return;
    }
    print '<taconite>
    <prepend select="#results">
    <table class="ui-widget">
        <thead><tr><th>' . $_GET['DatumDeka'] . '</th><th>Dekade</th></tr></thead>
        <tr class="blockA"><td>Block A</td><td>' . $result['A'] . '</td></tr>
        <tr class="blockB"><td>Block B</td><td>' . $result['B'] . '</td></tr>
        <tr class="blockC"><td>Block C</td><td>' . $result['C'] . '</td></tr>
    </table>
    </prepend>
    <eval>

                var start = new Date(Date.parse(\'' . $result['date'] . '\').sunday());
              var end = new Date(Date.parse(\'' . $result['date'] . '\').saturday());
        // $(\'#monatskalender\').fullCalendar( \'gotoDate\',start);
        // $(\'#monatskalender\').fullCalendar( \'select\',start,end);
        showCalendarAllBocks();
    </eval>
</taconite>';
} elseif ($_GET['full']) {
    $p = $cal->renderFullDekaPlan();
    $i = 0;
    $result = '';
    foreach ($p as $deka) {
        $i++;
        foreach ($deka as $block) {
            $result .= '{start:"' . substr($block["start"]->ToString(), 0, 10)
                    . '",end:"' . substr($block["end"]->ToString(), 0, 10)
                    . '",title:"' . $block["deka"] . '. Dekade'
                    . '",className:"block' . $block['block']
                    . '"},';
        }
    }
    print 'z=[' . substr($result, 0, -1) . '];';
    return;
} else {
    if ($_GET['json']) {
        print json_encode($result);
        return;
    }
    print '<taconite>
    <prepend select="#results">
    <p class="ui-state-error">Fehler.</p>
    </prepend>
    </taconite>';
}
?>