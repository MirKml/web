<?php
//-------------------------------------------------------------------
class WikiText_Parser_Mode_LineBreak extends WikiText_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\x5C{2}(?=\s)',$mode,'linebreak');
    }

}
