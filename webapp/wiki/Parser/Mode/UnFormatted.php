<?php
//-------------------------------------------------------------------
class WikiText_Parser_Mode_UnFormatted extends WikiText_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<nowiki>(?=.*</nowiki>)',$mode,'unformatted');
        $this->Lexer->addEntryPattern('%%(?=.*%%)',$mode,'unformattedalt');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('</nowiki>','unformatted');
        $this->Lexer->addExitPattern('%%','unformattedalt');
        $this->Lexer->mapHandler('unformattedalt','unformatted');
    }

}
