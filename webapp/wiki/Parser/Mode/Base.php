<?php
class WikiText_Parser_Mode_Base extends WikiText_Parser_Mode 
{

    public function __construct()
    {
        $allModes = WikiText_Parser::getParserModes();

        $this->_allowedModes = array_merge (
            $allModes['container'],
            $allModes['baseonly'],
            $allModes['paragraphs'],
            $allModes['formatting'],
            $allModes['substition'],
            $allModes['protected'],
            $allModes['disabled']
        );
    }

}
