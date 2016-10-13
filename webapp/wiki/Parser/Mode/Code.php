<?php
class WikiText_Parser_Mode_Code extends WikiText_Parser_Mode 
{
    public function connectTo($mode) 
    {
        $this->Lexer->addEntryPattern('<code(?=.*</code>)',$mode,'code');
    }

    public function postConnect() 
    {
        $this->Lexer->addExitPattern('</code>','code');
    }

} 
