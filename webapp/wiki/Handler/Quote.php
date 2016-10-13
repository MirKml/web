<?php
class WikiText_Handler_Quote extends WikiText_Handler_ReWritter
{
    private $_quoteCalls = array();

    public function finalise() 
    {
        $last_call = end($this->_calls);
        $this->writeCall(array('quote_end',array(), $last_call[2]));

        $this->process();
        $this->_callWriter->finalise();
    }

    public function process() 
    {
        $quoteDepth = 1;

        foreach ( $this->_calls as $call ) {
            switch ($call[0]) {
            case 'quote_start':
                $this->_quoteCalls[] = array('quote_open',array(),$call[2]);
            case 'quote_newline':
                $quoteLength = self::_getDepth($call[1][0]);
                if ( $quoteLength > $quoteDepth ) {
                    $quoteDiff = $quoteLength - $quoteDepth;
                    for ( $i = 1; $i <= $quoteDiff; $i++ ) {
                        $this->_quoteCalls[] = array('quote_open',array(),$call[2]);
                    }
                } elseif ( $quoteLength < $quoteDepth ) {
                    $quoteDiff = $quoteDepth - $quoteLength;
                    for ( $i = 1; $i <= $quoteDiff; $i++ ) {
                        $this->_quoteCalls[] = array('quote_close',array(),$call[2]);
                    }
                } else {
                    if ($call[0] != 'quote_start') $this->_quoteCalls[] = array('linebreak',array(),$call[2]);
                }
                $quoteDepth = $quoteLength;

                break;

            case 'quote_end':
                if ( $quoteDepth > 1 ) {
                    $quoteDiff = $quoteDepth - 1;
                    for ( $i = 1; $i <= $quoteDiff; $i++ ) {
                        $this->_quoteCalls[] = array('quote_close',array(),$call[2]);
                    }
                }

                $this->_quoteCalls[] = array('quote_close',array(),$call[2]);
                $this->_callWriter->writeCalls($this->_quoteCalls);
                break;
            default:
                $this->_quoteCalls[] = $call;
                break;
            }
        }
    }

    private static function _getDepth($marker)
    {
        preg_match('/>{1,}/', $marker, $matches);
        $quoteLength = strlen($matches[0]);
        return $quoteLength;
    }

}
