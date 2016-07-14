<?php

class TextFileImport extends DBI {

    protected $defsfile;
    protected $defs;
    protected $sdawfile;

    public function getDefs($path) {
        return $this->defs->$path;
    }

    public function loadDefsFile($defsfile) {
        $this->defsfile = $defsfile;
        $this->defs = GH::getXmlFile($this->defsfile); // debug: echo "<pre>"; print_r($defs);
        return;
    }

    public function loadSdawFile($sdawfile) {
        global $lockfilehandle; // LOGGING
        $this->sdawfile = $sdawfile;
        $filelines = count(file($sdawfile));
        fwrite($lockfilehandle, '#TOTAL LINES ' . $filelines . ' '. PHP_EOL);
        $pc = $filelines / 100;
        $pcdone = 0;

        $sdawfilehandle = fopen($this->sdawfile, "r+");
        if (flock($sdawfilehandle, LOCK_EX | LOCK_NB)) { // do an exclusive lock on USER
            $file = file($this->sdawfile);
            $ver = new VERSION;
            $ver->setHeaderDefs(GH::getXmlFile($GLOBALS[$this->defs->filetype . 'KopfDatenFile']));
            $lines = 0;
            $updates = 0;
            $inserts = 0;
            $loops = 0;
            $looplines = 0;
            $linedeletes = 0;
            $loopdeletes = 0;
            fwrite($lockfilehandle, '#START PROCESSING '. PHP_EOL);

            // $idxsql = 'ALTER TABLE ' . $this->defs->filetype . ' DISABLE KEYS;';
            $idxsql ='START TRANSACTION;'. PHP_EOL;
            parent::exec($idxsql);
            fwrite($lockfilehandle, '#SQL '.$idxsql.' ');
            if ($this->defs->loop) {
                foreach ($this->defs->loop as $loop) {
                    $idxtable = $this->defs->filetype . $loop->title;
                    $idxsql = 'ALTER TABLE ' . $idxtable . ' DISABLE KEYS;';
                    // parent::exec($idxsql);
                    // fwrite($lockfilehandle, '#SQL '.$idxsql.' '. PHP_EOL);
                }
            }

            foreach ($file as $line) {
                if (substr($line, 0, 1) == $this->defs->dataidentifier) {
                   // fwrite($lockfilehandle, '+');
                    $sql = "INSERT INTO " . $this->defs->filetype . "  (";
                    $sqlfieldlist = "";
                    $sqlvalues = "";
                    $updatestring = "";

                    $updatesql = "UPDATE " . $this->defs->filetype . " SET ";
                    foreach ($this->defs->sdawfieldset->field as $field) {
                        $sqlfieldlist .= $field->title . ",";
                        $updatestring .= $field->title . "=";
                        if ($field->type == 'Char') {
                            $sqlvalues .= parent::gqstr(substr($line, $field->startpos - 1, $field->length)) . ",";
                            $updatestring .= parent::gqstr(substr($line, $field->startpos - 1, $field->length)) . ",";
                        } else {
                            $sqlvalues .= substr($line, $field->startpos - 1, $field->length) . ",";
                            $updatestring .= substr($line, $field->startpos - 1, $field->length) . ",";
                        }
                    }

                    if ($this->defs->series) {
                        foreach ($this->defs->series->field as $series) {
                            $sqlfieldlist .= $series->title . ",";
                            if ($series->type == 'Char') {
                                $sqlvalues .= parent::gqstr($SERIES) . ",";
                                $updatestring .= $series->title . "=" . parent::gqstr($SERIES) . ",";
                            } else {
                                $sqlvalues .= $SERIES . ",";
                                $updatestring .= $series->title . "=" . $SERIES . ",";
                            }
                        }
                    }

                    $sql .= substr($sqlfieldlist, 0, -1) . ") VALUES (" . substr($sqlvalues, 0, -1) . ");";
                    $updatesql .= substr($updatestring, 0, -1) . " ";
                    $sqlwhere = " WHERE ";
                    foreach ($this->defs->uniqueitemid->index as $index) {
                        foreach ($ver->headerdefs->series as $series) {
                            if ((string) $index->name == (string) $series->field->title) {
                                $sqlwhere .= " " . $index->name . " LIKE "
                                        . parent::gqstr($SERIES) . " AND ";
                            }
                            else
                                $sqlwhere .= " " . $index->name . " LIKE "
                                        . parent::gqstr(substr($line, $index->startpos - 1, $index->length)) . " AND ";
                        }
                    }

                    $updatesql .= substr($sqlwhere, 0, -5) . "; ";
                    $testsql = $this->defs->filetype . "  " . substr($sqlwhere, 0, -5);

                    if ($this->defs->groupdelete) {
                        $deletesql = "DELETE FROM " . $this->defs->filetype . " WHERE ";
                        foreach ($this->defs->groupdelete->index as $index) {
                            foreach ($ver->headerdefs->series as $series) {
                                if ((string) $index->name == (string) $series->field->title) {
                                    $deletesql .= " " . $index->name . " LIKE "
                                            . parent::gqstr($SERIES) . " AND ";
                                }
                                else
                                    $deletesql .= " " . $index->name . " LIKE "
                                            . parent::gqstr(substr($line, $index->startpos - 1, $index->length)) . " AND ";
                            }
                        }
                        $deletesql = substr($deletesql, 0, -5);
                        // print $deletesql;
                        parent::sqldebug(false);
                        parent::exec($deletesql);
                        $linedeletes++;
                        // die;
                    }
                    // print $sql . "<br/>" . $updatesql;
                    // die;
                    parent::sqldebug(false);
                    // if ((string)$ver->headerdefs->mode == 'UPDATE') {
                    if (!$this->defs->groupdelete) {
                        if (parent::checkNumberOfRecords($testsql) > 0) {
                            parent::exec($updatesql);
                            $updates++;
                            // print "U ";
                        } else {
                            parent::exec($sql);
                            $inserts++;
                            // print "N ";
                        }
                    } else {
                        parent::exec($sql);
                        $inserts++;
                        // print "N ";
                    }
//                    } elseif ((string)$ver->headerdefs->mode == 'REPLACE')  {
//                        parent::exec('DELETE FROM ' . $testsql);
//                        parent::exec($sql);
//                    } else die('NO VALID MODE GIVEN');

                    /**
                     * Hook for data  loops
                     */
                    if ($this->defs->loop) {
                        foreach ($this->defs->loop as $loop) {
                            $table = $this->defs->filetype . $loop->title;
                            // $idxsql = 'ALTER TABLE ' . $table . ' DISABLE KEYS;';
                            // parent::sqldebug(false);
                            // parent::exec($idxsql);

                            $l = 0;

                            if ($this->defs->loop->groupdelete) {
                                $deletesql = "DELETE FROM " . $table . " WHERE ";
                                foreach ($this->defs->loop->groupdelete->index as $index) {
                                    foreach ($ver->headerdefs->series as $series) {
                                        if ((string) $index->name == (string) $series->field->title) {
                                            $deletesql .= " " . $index->name . " LIKE "
                                                    . parent::gqstr($SERIES) . " AND ";
                                        }
                                        else
                                            $deletesql .= " " . $index->name . " LIKE "
                                                    . parent::gqstr(substr($line, $index->startpos - 1, $index->length)) . " AND ";
                                    }
                                }
                                $deletesql = substr($deletesql, 0, -5) . "; ";
                                // print $deletesql;
                                // parent::sqldebug(true);
                                parent::exec($deletesql);
                                $loopdeletes++;
                                // die;
                            }

                            foreach ($loop->field as $field) {
                                $l = $l + $field->length;
                            };
                            $data = str_split(substr($line, $loop->startpos), $l);
                            foreach ($data as $newline) {
                                if (substr($newline, 0, 1) == $loop->eodata) {
                                    // print " <hr/> ";
                                    break;
                                } else {

                                    $sql = "INSERT INTO " . $table . " (";
                                    $sqlfieldlist = "";
                                    $sqlvalues = "";
                                    $updatestring = "";
                                    $updatesql = "UPDATE " . $table . " SET ";
                                    // print $table . " | ";
                                    foreach ($loop->field as $field) {
                                        $sqlfieldlist .= $field->title . ",";
                                        $updatestring .= $field->title . "=";
                                        if ($field->type == 'Char') {
                                            $sqlvalues .= parent::gqstr(substr($newline, $field->startpos - 1, $field->length)) . ",";
                                            $updatestring .= parent::gqstr(substr($newline, $field->startpos - 1, $field->length)) . ",";
                                        } else {
                                            $sqlvalues .= substr($newline, $field->startpos - 1, $field->length) . ",";
                                            $updatestring .= substr($newline, $field->startpos - 1, $field->length) . ",";
                                        }
                                    }
                                    foreach ($this->defs->uniqueitemid->index as $field) {
                                        $sqlfieldlist .= $field->name . ",";
                                        $updatestring .= $field->name . "=";
                                        foreach ($ver->headerdefs->series as $series) {
                                            if ((string) $field->name == (string) $series->field->title) {
                                                if ($field->type == 'Char') {
                                                    $sqlvalues .= parent::gqstr($SERIES) . ","; //parent::gqstr(substr($line, $field->startpos - 1, $field->length)) . ",";
                                                    $updatestring .= parent::gqstr($SERIES) . ",";
                                                } else {
                                                    $sqlvalues .= $SERIES . ","; //parent::gqstr(substr($line, $field->startpos - 1, $field->length)) . ",";
                                                    $updatestring .= $SERIES . ",";
                                                }
                                            } else {
                                                if ($field->type == 'Char') {
                                                    $sqlvalues .= parent::gqstr(substr($line, $field->startpos - 1, $field->length)) . ",";
                                                    $updatestring .= parent::gqstr(substr($line, $field->startpos - 1, $field->length)) . ",";
                                                } else {
                                                    $sqlvalues .= substr($line, $field->startpos - 1, $field->length) . ",";
                                                    $updatestring .= substr($line, $field->startpos - 1, $field->length) . ",";
                                                }
                                            }
                                        }
                                    }
                                    $sql .= substr($sqlfieldlist, 0, -1) . ") VALUES (" . substr($sqlvalues, 0, -1) . ");";

                                    $updatesql .= substr($updatestring, 0, -1) . " ";
                                    $sqlwhere = " WHERE ";

                                    ///
                                    foreach ($this->defs->loop->uniqueitemid->index as $index) {
                                        foreach ($ver->headerdefs->series as $series) {
                                            if ((string) $index->name == (string) $series->field->title) {
                                                $sqlwhere .= " " . $index->name . " LIKE "
                                                        . parent::gqstr($SERIES) . " AND ";
                                            }
                                            else
                                                $sqlwhere .= " " . $index->name . " LIKE "
                                                        . parent::gqstr(substr($line, $index->startpos - 1, $index->length)) . " AND ";
                                        }
                                    }

                                    $updatesql .= substr($sqlwhere, 0, -5) . "; ";
                                    $testsql = $table . "  " . substr($sqlwhere, 0, -5);


                                    parent::sqldebug(false);

                                    if (!$this->defs->groupdelete) {
                                        if (parent::checkNumberOfRecords($testsql) > 0) {
                                            parent::exec($updatesql);
                                            $updates++;
                                            // print "U ";
                                        } else {
                                            parent::exec($sql);
                                            $inserts++;
                                            // print "N ";
                                        }
                                    } else {
                                        parent::exec($sql);
                                        $inserts++;
                                        // print "N ";
                                    }
                                    $looplines++;
                                    // fwrite($lockfilehandle, '.');
                                }
                            }
                            $loops++; // die;
                        }
                    }
                } elseif (substr($line, 0, 1) == $ver->headerdefs->dataidentifier) {
                    $ver->setVersionData($line);
                    $_SESSION['fileheader'] = $line;

                    if ($ver->headerdefs->series) {
                        $SERIES = substr($line, $ver->headerdefs->series->field->startpos - 1, $ver->headerdefs->series->field->length);
                    }
                    if ($ver->registerFile() != 1) {
                        trigger_error("Datei $sdawfile bereits vorhanden.", E_USER_NOTICE);
                        fwrite($lockfilehandle, '#ERROR VERSION FILE '.$line. PHP_EOL);
                        print ( $line . ': Serienkennung für Datei bereits registriert. ');
                        break;
                    }
                }
                $lines++;
                if ($lines / $pc > $pcdone) {
                    $pcdone++;
                    fwrite($lockfilehandle, ' ... '.$pcdone.'% ');
                }
            }

            // $idxsql = 'ALTER TABLE ' . $this->defs->filetype . ' ENABLE KEYS;';
            $idxsql = 'COMMIT;';
            fwrite($lockfilehandle, '#SQL '.$idxsql.' '. PHP_EOL);
            parent::exec($idxsql);
            if ($this->defs->loop) {
                foreach ($this->defs->loop as $loop) {
                    $idxtable = $this->defs->filetype . $loop->title;
                    $idxsql = 'ALTER TABLE ' . $idxtable . ' ENSABLE KEYS;';
                    //  fwrite($lockfilehandle, '#SQL '.$idxsql.' ');
                    // parent::exec($idxsql);
                }
            }

            //flock aufheben!!!
            fwrite($lockfilehandle, '#END PROCESSING '. PHP_EOL);
            fclose($sdawfilehandle);
            $_SESSION['lines'] = $lines;
            $_SESSION['loops'] = $loops;
            $_SESSION['looplines'] = $looplines;
            $_SESSION['updates'] = $updates;
            $_SESSION['inserts'] = $inserts;
            $_SESSION['linedeletes'] = $linedeletes;
            $_SESSION['loopdeletes'] = $loopdeletes;
            return true;
        } else
        // die ('File '.$this->sdawfile.' locked!');
            fwrite($lockfilehandle, '#DIE LOCKED FILE ');
        return false;
    }

}

?>