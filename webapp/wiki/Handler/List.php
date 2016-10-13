<?php
class WikiText_Handler_List extends WikiText_Handler_ReWritter
{
    private $_listCalls = array();
    private $_listStack = array();

    public function finalise()
    {
        $last_call = end($this->_calls);
        $this->writeCall(array('list_close',array(), $last_call[2]));

        $this->process();
        $this->_callWriter->finalise();
    }

    public function process()
    {
        foreach ( $this->_calls as $call ) {
            switch ($call[0]) {
            case 'list_item':
                $this->_listOpen($call);
                break;
            case 'list_open':
                $this->_listStart($call);
                break;
            case 'list_close':
                $this->_listEnd($call);
                break;
            default:
                $this->_listContent($call);
                break;
            }
        }

        $this->_callWriter->writeCalls($this->_listCalls);
    }

    private function _listStart($call)
    {
        $depth = self::_interpretSyntax($call[1][0], $listType);

        $this->initialDepth = $depth;
        $this->_listStack[] = array($listType, $depth);

        $this->_listCalls[] = array('list'.$listType.'_open',array(),$call[2]);
        $this->_listCalls[] = array('listitem_open',array(1),$call[2]);
        $this->_listCalls[] = array('listcontent_open',array(),$call[2]);
    }

    private function _listEnd($call)
    {
        $closeContent = true;

        while ( $list = array_pop($this->_listStack) ) {
            if ( $closeContent ) {
                $this->_listCalls[] = array('listcontent_close',array(),$call[2]);
                $closeContent = false;
            }
            $this->_listCalls[] = array('listitem_close',array(),$call[2]);
            $this->_listCalls[] = array('list'.$list[0].'_close', array(), $call[2]);
        }
    }

    private function _listOpen($call)
    {
        $depth = self::_interpretSyntax($call[1][0], $listType);
        $end = end($this->_listStack);

        // Not allowed to be shallower than initialDepth
        if ( $depth < $this->initialDepth ) {
            $depth = $this->initialDepth;
        }
        if ( $depth == $end[1] ) {
            // Just another item in the list...
            if ( $listType == $end[0] ) {
                $this->_listCalls[] = array('listcontent_close',array(),$call[2]);
                $this->_listCalls[] = array('listitem_close',array(),$call[2]);
                $this->_listCalls[] = array('listitem_open',array($depth-1),$call[2]);
                $this->_listCalls[] = array('listcontent_open',array(),$call[2]);

                // Switched list type...
            } else {
                $this->_listCalls[] = array('listcontent_close',array(),$call[2]);
                $this->_listCalls[] = array('listitem_close',array(),$call[2]);
                $this->_listCalls[] = array('list'.$end[0].'_close', array(), $call[2]);
                $this->_listCalls[] = array('list'.$listType.'_open', array(), $call[2]);
                $this->_listCalls[] = array('listitem_open', array($depth-1), $call[2]);
                $this->_listCalls[] = array('listcontent_open',array(),$call[2]);

                array_pop($this->_listStack);
                $this->_listStack[] = array($listType, $depth);
            }

            // Getting deeper...
        } elseif ( $depth > $end[1] ) {

            $this->_listCalls[] = array('listcontent_close',array(),$call[2]);
            $this->_listCalls[] = array('list'.$listType.'_open', array(), $call[2]);
            $this->_listCalls[] = array('listitem_open', array($depth-1), $call[2]);
            $this->_listCalls[] = array('listcontent_open',array(),$call[2]);

            $this->_listStack[] = array($listType, $depth);

            // Getting shallower ( $depth < $end[1] )
        } else {
            $this->_listCalls[] = array('listcontent_close',array(),$call[2]);
            $this->_listCalls[] = array('listitem_close',array(),$call[2]);
            $this->_listCalls[] = array('list'.$end[0].'_close',array(),$call[2]);

            // Throw away the end - done
            array_pop($this->_listStack);
            while (1) {
                $end = end($this->_listStack);
                if ( $end[1] <= $depth ) {
                    // Normalize depths
                    $depth = $end[1];
                    $this->_listCalls[] = array('listitem_close',array(),$call[2]);
                    if ( $end[0] == $listType ) {
                        $this->_listCalls[] = array('listitem_open',array($depth-1),$call[2]);
                        $this->_listCalls[] = array('listcontent_open',array(),$call[2]);
                    } else {
                        // Switching list type...
                        $this->_listCalls[] = array('list'.$end[0].'_close', array(), $call[2]);
                        $this->_listCalls[] = array('list'.$listType.'_open', array(), $call[2]);
                        $this->_listCalls[] = array('listitem_open', array($depth-1), $call[2]);
                        $this->_listCalls[] = array('listcontent_open',array(),$call[2]);

                        array_pop($this->_listStack);
                        $this->_listStack[] = array($listType, $depth);
                    }

                    break;

                    // Haven't dropped down far enough yet.... ( $end[1] > $depth )
                } else {
                    $this->_listCalls[] = array('listitem_close',array(),$call[2]);
                    $this->_listCalls[] = array('list'.$end[0].'_close',array(),$call[2]);
                    array_pop($this->_listStack);
                }
            }
        }
    }

    private function _listContent($call)
    {
        $this->_listCalls[] = $call;
    }

    private static function _interpretSyntax($match, &$type)
    {
        if ( substr($match,-1) == '*' ) {
            $type = 'u';
        } else {
            $type = 'o';
        }

        return count(explode('  ',str_replace("\t",'  ',$match)));
    }

}
