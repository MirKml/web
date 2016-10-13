<?php
/**
 * Wrapper around around GeSHi syntax highlighter, it is Zend_Loader aware.
 */
class GeSHi_HighLighter
{

    /**
     * @var GeSHi
     */
    private $_geshi;

    /**
     * Initializes main geshi class for the specific $lang syntax
     * @param string $lang language identifier, which is supported by geshi
     */
    public function __construct($lang) {
        $this->_geshi = new GeSHi("", $lang);
        $this->_geshi->set_encoding('utf-8');
        $this->_geshi->enable_classes();
        $this->_geshi->set_header_type(GESHI_HEADER_PRE);
        $this->_geshi->set_overall_class("codeOutput");
    }

    /**
     * returns highlighted code
     * @param string $src source code
     * @return string
     */
    public function getHighlightedCode($src) {
        $this->_geshi->set_source($src);
        return $this->_geshi->parse_code();
    }
}
