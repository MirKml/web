<?php
//-------------------------------------------------------------------
class WikiText_Parser_Mode_Table extends WikiText_Parser_Mode {

    function __construct() {
        $allModes = WikiText_Parser::getParserModes(); 

        $this->_allowedModes = array_merge (
            $allModes['formatting'],
            $allModes['substition'],
            $allModes['disabled'],
            $allModes['protected']
        );
    }

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('\n\^',$mode,'table');
        $this->Lexer->addEntryPattern('\n\|',$mode,'table');
    }

    function postConnect() {
        $this->Lexer->addPattern('\n\^','table');
        $this->Lexer->addPattern('\n\|','table');
        #$this->Lexer->addPattern(' {2,}','table');
        $this->Lexer->addPattern('[\t ]+','table');
        $this->Lexer->addPattern('\^','table');
        $this->Lexer->addPattern('\|','table');
        $this->Lexer->addExitPattern('\n','table');
    }

}
