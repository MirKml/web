<?php
abstract class WikiText_Renderer
{
    protected $_renderToc = false;

    //keep some config options
    protected static $_acronyms = array();
    protected static $_smileys = array();
    protected static $_entities = array();
    protected static $_interwiki = array();

    public function __construct()
    {
        $this->_loadInterwiki();
        $this->_loadSmileys();
        $this->_loadAcronyms();
        $this->_loadEntities();
    }

    private function _loadInterwiki()
    {
        self::$_interwiki=WikiText_Utils::confToHash(WikiText_Utils::getConfDir().'interwiki.conf',true);
    }

    private function _loadSmileys()
    {
        self::$_smileys=WikiText_Utils::getSmileys();
    }

    private function _loadAcronyms()
    {
        self::$_acronyms=WikiText_Utils::getAcronyms();
    }

    private function _loadEntities()
    {
        self::$_entities=WikiText_Utils::getEntities();
    }

    /**
     * Returns the format produced by this renderer.
     */
    abstract public function getFormat();

    /**
     * handle nested render instructions
     * this method (and nest_close method) should not be overloaded in actual renderer output classes
     */
    public function nest($instructions)
    {
        foreach ( $instructions as $instruction ) {
            //execute the callback against ourself
            call_user_func_array(array(&$this, $instruction[0]),$instruction[1]);
        }
    }

    abstract public function document_start();

    abstract public function document_end();

    abstract public function render_TOC();

    abstract public function toc_additem($id, $text, $level);

    abstract public function header($text, $level, $pos);

    abstract public function section_open($level);

    abstract public function section_close();

    abstract public function cdata($text);

    abstract public function p_open();

    abstract public function p_close();

    abstract public function linebreak();

    abstract public function hr();

    abstract public function strong_open();

    abstract public function strong_close();

    abstract public function emphasis_open();

    abstract public function emphasis_close();

    abstract public function underline_open();

    abstract public function underline_close();

    abstract public function monospace_open();

    abstract public function monospace_close();

    abstract public function subscript_open();

    abstract public function subscript_close();

    abstract public function superscript_open();

    abstract public function superscript_close();

    abstract public function deleted_open();

    abstract public function deleted_close();

    abstract public function footnote_open();

    abstract public function footnote_close();

    abstract public function listu_open();

    abstract public function listu_close();

    abstract public function listo_open();

    abstract public function listo_close();

    abstract public function listitem_open($level);

    abstract public function listitem_close();

    abstract public function listcontent_open();

    abstract public function listcontent_close();

    abstract public function unformatted($text);

    abstract public function html($text);

    abstract public function preformatted($text);

    abstract public function quote_open();

    abstract public function quote_close();

    abstract public function code($text, $lang = NULL);

    abstract public function acronym($acronym);

    abstract public function smiley($smiley);

    abstract public function entity($entity);

    abstract public function multiplyentity($x, $y);

    abstract public function singlequoteopening();

    abstract public function singlequoteclosing();

    abstract public function apostrophe();

    abstract public function doublequoteopening();

    abstract public function doublequoteclosing();

    /** the instruction for the internal link rendering  
     * @param string $link link like 'wiki:syntax'
     * @param array $title media info
     */ 
    abstract public function internallink($link, $title = NULL);

    /** the handler for the internal link rendering  
     * @param string $link link like 'http://www.domain.net/path'
     * @param array $title media info
     */ 
    abstract public function externallink($link, $title = NULL);

    /**
     * the handler for the interwiki link rendering
     * @param string $link 
     * @param string $wikiName is an indentifier for the wiki
     * @param string $wikiUri the URL fragment to append to some known URL 
     * @param array $title media info
     */ 
    abstract public function interwikilink($link, $title = NULL, $wikiName, $wikiUri);

    abstract public function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
        $height=NULL, $linking=NULL);

    abstract public function emaillink($address, $name = NULL);

    abstract public function table_open();

    abstract public function table_close();

    abstract public function tablerow_open();

    abstract public function tablerow_close();

    abstract public function tableheader_open($colspan = 1, $align = NULL);

    abstract public function tableheader_close();

    abstract public function tablecell_open($colspan = 1, $align = NULL);

    abstract public function tablecell_close();

    /**
     * Resolve an interwikilink
     */
    protected static function _resolveInterWiki(&$shortcut,$reference)
    {
        //get interwiki URL
        if ( isset(self::$_interwiki[$shortcut]) ) {
            $url = self::$_interwiki[$shortcut];
        } else {
            // Default to Google I'm feeling lucky
            $url = 'http://www.google.com/search?q={URL}&amp;btnI=lucky';
            $shortcut = 'go';
        }

        //replace placeholder
        if(preg_match('#\{(URL|NAME|SCHEME|HOST|PORT|PATH|QUERY)\}#',$url)){
        //use placeholders
        $url = str_replace('{URL}',rawurlencode($reference),$url);
        $url = str_replace('{NAME}',$reference,$url);
        $parsed = parse_url($reference);

        if(!$parsed['port']) $parsed['port'] = 80;

        $url = str_replace('{SCHEME}',$parsed['scheme'],$url);
        $url = str_replace('{HOST}',$parsed['host'],$url);
        $url = str_replace('{PORT}',$parsed['port'],$url);
        $url = str_replace('{PATH}',$parsed['path'],$url);
        $url = str_replace('{QUERY}',$parsed['query'],$url);
        } else {
            //default
            $url = $url.rawurlencode($reference);
        }
        return $url;
    }

}
