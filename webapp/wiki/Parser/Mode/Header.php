<?php
//-------------------------------------------------------------------
class WikiText_Parser_Mode_Header extends WikiText_Parser_Mode 
{

    function preConnect() {
        //we're not picky about the closing ones, two are enough
        $this->Lexer->addSpecialPattern(
            '[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)',
            'base',
            'header'
        );
    }

}
