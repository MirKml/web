<?php
class WikiText_Parser_Mode_Quote extends WikiText_Parser_Mode 
{
    public function __construct() 
    {
        $allModes = WikiText_Parser::getParserModes();

        $this->allowedModes = array_merge (
            $allModes['formatting'],
            $allModes['substition'],
            $allModes['disabled'],
            $allModes['protected']
        );
    }

    public function connectTo($mode) 
    {
        $this->Lexer->addEntryPattern('\n>{1,}',$mode,'quote');
    }

    public function postConnect() 
    {
        $this->Lexer->addPattern('\n>{1,}','quote');
        $this->Lexer->addExitPattern('\n','quote');
    }

} 
