<?php
class WikiText_Parser_Mode_Smiley extends WikiText_Parser_Mode
{
    private $_smileys = array();
    private $_pattern = '';

    public function __construct()
    {
        $this->_smileys = array_keys(WikiText_Utils::getSmileys());
    }

    function preConnect()
    {
        if(!count($this->_smileys) || $this->_pattern != '') return;

        $sep = '';
        foreach ( $this->_smileys as $smiley ) {
            $this->_pattern .= $sep.WikiText_Lexer::escape($smiley);
            $sep = '|';
        }
    }

    public function connectTo($mode)
    {
        if(!count($this->_smileys)) return;

        if (strlen($this->_pattern)>0) {
            $this->Lexer->addSpecialPattern($this->_pattern,$mode,'smiley');
        }
    }

}
