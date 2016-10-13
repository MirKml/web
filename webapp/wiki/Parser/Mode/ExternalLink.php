<?php
class WikiText_Parser_Mode_ExternalLink extends WikiText_Parser_Mode
{

    private $_schemes = array('http','https','telnet','gopher','wais','ftp','ed2k','irc','ldap');
    private $_patterns = array();

    public function preConnect()
    {
        if(count($this->_patterns)) return;

        $ltrs = '\w';
        $gunk = '/\#~:.?+=&%@!\-';
        $punc = '.:?\-;,';
        $host = $ltrs.$punc;
        $any  = $ltrs.$gunk.$punc;

        foreach ( $this->_schemes as $scheme ) {
            $this->_patterns[] = '\b(?i)'.$scheme.'(?-i)://['.$any.']+?(?=['.$punc.']*[^'.$any.'])';
        }

        $this->_patterns[] = '\b(?i)www?(?-i)\.['.$host.']+?\.['.$host.']+?['.$any.']+?(?=['.$punc.']*[^'.$any.'])';
        $this->_patterns[] = '\b(?i)ftp?(?-i)\.['.$host.']+?\.['.$host.']+?['.$any.']+?(?=['.$punc.']*[^'.$any.'])';
    }

    public function connectTo($mode)
    {
        foreach ( $this->_patterns as $pattern ) {
            $this->Lexer->addSpecialPattern($pattern,$mode,'externallink');
        }
    }

}
