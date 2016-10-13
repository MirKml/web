<?php
//-------------------------------------------------------------------
class WikiText_Parser_Mode_InternalLink extends WikiText_Parser_Mode {

    function connectTo($mode) {
        // Word boundaries?
        $this->Lexer->addSpecialPattern("\[\[.+?\]\]",$mode,'internallink');
    }

}
