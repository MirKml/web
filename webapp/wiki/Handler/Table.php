<?php
class WikiText_Handler_Table extends WikiText_Handler_ReWritter
{
    private $_tableCalls = array();
    private $_maxCols = 0;
    private $_maxRows = 1;
    private $_currentCols = 0;
    private $_firstCell = false;
    private $_lastCellType = 'tablecell';

    public function finalise()
    {
        $last_call = end($this->_calls);
        $this->writeCall(array('table_end',array(), $last_call[2]));

        $this->process();
        $this->_callWriter->finalise();
    }

    public function process()
    {
        foreach ( $this->_calls as $call ) {
            switch ( $call[0] ) {
            case 'table_start':
                $this->_tableStart($call);
                break;
            case 'table_row':
                $this->_tableRowClose(array('tablerow_close',$call[1],$call[2]));
                $this->_tableRowOpen(array('tablerow_open',$call[1],$call[2]));
                break;
            case 'tableheader':
            case 'tablecell':
                $this->_tableCell($call);
                break;
            case 'table_end':
                $this->_tableRowClose(array('tablerow_close',$call[1],$call[2]));
                $this->_tableEnd($call);
                break;
            default:
                $this->_tableDefault($call);
                break;
            }
        }
        $this->_callWriter->writeCalls($this->_tableCalls);
    }

    private function _tableStart($call)
    {
        $this->_tableCalls[] = array('table_open',array(),$call[2]);
        $this->_tableCalls[] = array('tablerow_open',array(),$call[2]);
        $this->_firstCell = true;
    }

    private function _tableEnd($call)
    {
        $this->_tableCalls[] = array('table_close',array(),$call[2]);
        $this->_finalizeTable();
    }

    private function _tableRowOpen($call)
    {
        $this->_tableCalls[] = $call;
        $this->_currentCols = 0;
        $this->_firstCell = true;
        $this->_lastCellType = 'tablecell';
        $this->_maxRows++;
    }

    private function _tableRowClose($call)
    {
        // Strip off final cell opening and anything after it
        while ( $discard = array_pop($this->_tableCalls ) ) {
            if ( $discard[0] == 'tablecell_open' || $discard[0] == 'tableheader_open') {
                // Its a spanning element - put it back and close it
                if ( $discard[1][0] > 1 ) {
                    $this->_tableCalls[] = $discard;
                    if ( strstr($discard[0],'cell') ) {
                        $name = 'tablecell';
                    } else {
                        $name = 'tableheader';
                    }
                    $this->_tableCalls[] = array($name.'_close',array(),$call[2]);
                }
                break;
            }
        }

        $this->_tableCalls[] = $call;
        if ( $this->_currentCols > $this->_maxCols ) {
            $this->_maxCols = $this->_currentCols;
        }
    }

    private function _tableCell($call)
    {
        if ( !$this->_firstCell ) {
            // Increase the span
            $lastCall = end($this->_tableCalls);
            // A cell call which follows an open cell means an empty cell so span
            if ( $lastCall[0] == 'tablecell_open' || $lastCall[0] == 'tableheader_open' ) {
                $this->_tableCalls[] = array('colspan',array(),$call[2]);
            }
            $this->_tableCalls[] = array($this->_lastCellType.'_close',array(),$call[2]);
            $this->_tableCalls[] = array($call[0].'_open',array(1,NULL),$call[2]);
            $this->_lastCellType = $call[0];
        } else {
            $this->_tableCalls[] = array($call[0].'_open',array(1,NULL),$call[2]);
            $this->_lastCellType = $call[0];
            $this->_firstCell = false;
        }
        $this->_currentCols++;
    }

    private function _tableDefault($call)
    {
        $this->_tableCalls[] = $call;
    }

    private function _finalizeTable()
    {
        // Add the max cols and rows to the table opening
        if ( $this->_tableCalls[0][0] == 'table_open' ) {
            // Adjust to num cols not num col delimeters
            $this->_tableCalls[0][1][] = $this->_maxCols - 1;
            $this->_tableCalls[0][1][] = $this->_maxRows;
        } else {
            trigger_error('First element in table call list is not table_open');
        }

        $lastRow = 0;
        $lastCell = 0;
        $toDelete = array();

        // Look for the colspan elements and increment the colspan on the
        // previous non-empty opening cell. Once done, delete all the cells
        // that contain colspans
        foreach ( $this->_tableCalls as $key => $call ) {
            if ( $call[0] == 'tablerow_open' ) {
                $lastRow = $key;
            } else if ( $call[0] == 'tablecell_open' || $call[0] == 'tableheader_open' ) {
                $lastCell = $key;
            } else if ( $call[0] == 'table_align' ) {
                // If the previous element was a cell open, align right
                if ( $this->_tableCalls[$key-1][0] == 'tablecell_open' || $this->_tableCalls[$key-1][0] == 'tableheader_open' ) {
                    $this->_tableCalls[$key-1][1][1] = 'right';
                    // If the next element if the close of an element, align either center or left
                } else if ( $this->_tableCalls[$key+1][0] == 'tablecell_close' || $this->_tableCalls[$key+1][0] == 'tableheader_close' ) {
                    if ( $this->_tableCalls[$lastCell][1][1] == 'right' ) {
                        $this->_tableCalls[$lastCell][1][1] = 'center';
                    } else {
                        $this->_tableCalls[$lastCell][1][1] = 'left';
                    }
                }

                // Now convert the whitespace back to cdata
                $this->_tableCalls[$key][0] = 'cdata';

            } else if ( $call[0] == 'colspan' ) {
                $this->_tableCalls[$key-1][1][0] = false;
                for($i = $key-2; $i > $lastRow; $i--) {
                    if ( $this->_tableCalls[$i][0] == 'tablecell_open' || $this->_tableCalls[$i][0] == 'tableheader_open' ) {
                        if ( false !== $this->_tableCalls[$i][1][0] ) {
                            $this->_tableCalls[$i][1][0]++;
                            break;
                        }
                    }
                }

                $toDelete[] = $key-1;
                $toDelete[] = $key;
                $toDelete[] = $key+1;
            }
        }

        // condense cdata
        $cnt = count($this->_tableCalls);
        for( $key = 0; $key < $cnt; $key++){
            if($this->_tableCalls[$key][0] == 'cdata'){
                $ckey = $key;
                $key++;
                while($this->_tableCalls[$key][0] == 'cdata'){
                    $this->_tableCalls[$ckey][1][0] .= $this->_tableCalls[$key][1][0];
                    $toDelete[] = $key;
                    $key++;
                }
                continue;
            }
        }

        foreach ( $toDelete as $delete ) {
            unset($this->_tableCalls[$delete]);
        }
        $this->_tableCalls = array_values($this->_tableCalls);
    }

}
