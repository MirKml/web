<?php
//-------------------------------------------------------------------
class WikiText_Parser_Mode_Preformatted extends WikiText_Parser_Mode {

    function connectTo($mode) {
        // Has hard coded awareness of lists...
        $this->Lexer->addEntryPattern('\n  (?![\*\-])',$mode,'preformatted');
        $this->Lexer->addEntryPattern('\n\t(?![\*\-])',$mode,'preformatted');

        // How to effect a sub pattern with the Lexer!
        $this->Lexer->addPattern('\n  ','preformatted');
        $this->Lexer->addPattern('\n\t','preformatted');

    }

    function postConnect() {
        $this->Lexer->addExitPattern('\n','preformatted');
    }

}
