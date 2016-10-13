<?php
//-------------------------------------------------------------------
class WikiText_Parser_Mode_FootNote extends WikiText_Parser_Mode {

    public function __construct()
    {
        $allModes = WikiText_Parser::getParserModes(); 

        $this->_allowedModes = array_merge (
            $allModes['container'],
            $allModes['formatting'],
            $allModes['substition'],
            $allModes['protected'],
            $allModes['disabled']
        );

        unset($this->_allowedModes[array_search('footnote', $this->_allowedModes)]);
    }

    function connectTo($mode) {
        $this->Lexer->addEntryPattern(
            '\x28\x28(?=.*\x29\x29)',$mode,'footnote'
        );
    }

    function postConnect() {
        $this->Lexer->addExitPattern(
            '\x29\x29','footnote'
        );
    }

}
