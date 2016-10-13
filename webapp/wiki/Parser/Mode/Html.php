<?php
//-------------------------------------------------------------------
class WikiText_Parser_Mode_Html extends WikiText_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<html>(?=.*</html>)',$mode,'html');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('</html>','html');
    }

}
