<?php
class WikiText_Parser_Mode_Acronym extends WikiText_Parser_Mode
{
    private $_acronyms = array();
    private $_pattern = '';

    public function __construct()
    {
        $this->_acronyms = array_keys(WikiText_Utils::getAcronyms());
    }

    public function preConnect()
    {
        if(!count($this->_acronyms)) return;

        $bound = '[\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]';
        $acronyms = array_map(array('WikiText_Lexer','escape'),$this->_acronyms);
        $this->_pattern = '(?<=^|'.$bound.')(?:'.join('|',$acronyms).')(?='.$bound.')';
    }

    public function connectTo($mode)
    {
        if(!count($this->_acronyms)) return;

        if ( strlen($this->_pattern) > 0 ) {
            $this->Lexer->addSpecialPattern($this->_pattern,$mode,'acronym');
        }
    }

}
