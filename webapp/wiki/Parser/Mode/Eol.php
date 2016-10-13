<?php
//-------------------------------------------------------------------
class WikiText_Parser_Mode_Eol extends WikiText_Parser_Mode 
{

    function connectTo($mode) {
        $badModes = array('listblock','table');
        if ( in_array($mode, $badModes) ) {
            return;
        }
        $this->Lexer->addSpecialPattern('\n',$mode,'eol');
    }

}
