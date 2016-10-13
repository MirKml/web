<?php
//-------------------------------------------------------------------
class WikiText_Parser_Mode_ListBlock extends WikiText_Parser_Mode 
{

    public function __construct() 
    {
        $allModes = WikiText_Parser::getParserModes(); 

        $this->_allowedModes = array_merge (
            $allModes['formatting'],
            $allModes['substition'],
            $allModes['disabled'],
            $allModes['protected'] #XXX new
        );

        //this->allowedModes[] = 'footnote';
    }

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('\n {2,}[\-\*]',$mode,'listblock');
        $this->Lexer->addEntryPattern('\n\t{1,}[\-\*]',$mode,'listblock');

        $this->Lexer->addPattern('\n {2,}[\-\*]','listblock');
        $this->Lexer->addPattern('\n\t{1,}[\-\*]','listblock');

    }

    function postConnect() {
        $this->Lexer->addExitPattern('\n','listblock');
    }

}
