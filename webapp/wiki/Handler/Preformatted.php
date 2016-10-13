<?php
class WikiText_Handler_Preformatted extends WikiText_Handler_ReWritter
{
    private $_pos;
    private $_text ='';

    public function finalise()
    {
        $last_call = end($this->_calls);
        $this->writeCall(array('preformatted_end',array(), $last_call[2]));

        $this->process();
        $this->_callWriter->finalise();
    }

    public function process()
    {
        foreach ( $this->_calls as $call ) {
            switch ($call[0]) {
            case 'preformatted_start':
                $this->_pos = $call[2];
                break;
            case 'preformatted_newline':
                $this->_text .= "\n";
                break;
            case 'preformatted_content':
                $this->_text .= $call[1][0];
                break;
            case 'preformatted_end':
                $this->_callWriter->writeCall(array('preformatted',array($this->_text),$this->_pos));
                break;
            }
        }
    }

}
