<?php
class WikiText_Parser_Mode_Entity extends WikiText_Parser_Mode
{
    private $_entities = array();
    private $_pattern = '';

    public function __construct() 
    {
        $this->_entities = array_keys(WikiText_Utils::getEntities()); 
    }

    public function preConnect()
    {
        if(!count($this->_entities) || $this->_pattern != '') return;

        $sep = '';
        foreach ( $this->_entities as $entity ) {
            $this->_pattern .= $sep.WikiText_Lexer::escape($entity);
            $sep = '|';
        }
    }

    public function connectTo($mode) 
    {
        if(!count($this->_entities)) return;

        if ( strlen($this->_pattern) > 0 ) {
            $this->Lexer->addSpecialPattern($this->_pattern,$mode,'entity');
        }
    }

}
 
