<?php
/**
 * Parser class ipmlementation/
 *
 * @package Wikitext
 * @author Miroslav Kubelik (koubel@volny.cz)
 * @license http://opensource.org/licenses/gpl-license.php GNU GPL
 */

/**
 * Parser class. Main entry point for the application, connects Lexer, Handler
 * and Renderer together.
 *
 * @package Wikitext
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Miroslav Kubelik <koubel@volny.cz>
 * @license http://opensource.org/licenses/gpl-license.php GNU GPL
 */
class WikiText_Parser
{

    /**
     * Various types of modes used by the parser - they are used to populate
     * the list of modes another mode accepts
     * @var array
     */
    private static $_parserModes = array (
        // containers are complex modes that can contain many other modes
        // hr breaks the principle but they shouldn't be used in tables / lists
        // so they are put here
        'container'    => array('listblock','table','quote','hr'),

        // some mode are allowed inside the base mode only
        'baseonly'     => array('header'),

        // modes for styling text -- footnote behaves similar to styling
        'formatting'   => array('strong', 'emphasis', 'underline', 'monospace',
        'subscript', 'superscript', 'deleted', 'footnote'),

        // modes where the token is simply replaced - they can not contain any
        // other modes
        'substition'   => array('acronym','smiley','wordblock','entity',
        'camelcaselink', 'internallink','media',
        'externallink','linebreak','emaillink',
        'windowssharelink','filelink','notoc',
        'nocache','multiplyentity','quotes','rss'),

        // modes which have a start and end token but inside which
        // no other modes should be applied
        'protected'    => array('preformatted','code','file','php','html'),

        // inside this mode no wiki markup should be applied but lineendings
        // and whitespace isn't preserved
        'disabled'     => array('unformatted'),

        // used to mark paragraph boundaries
        'paragraphs'   => array('eol')
    );

    /**
     * Handler for the instructions generating
     * @var WikiText_Handler
     */
    private $_handler;

    /**
     * Lexer for scanning the text
     * @var WikiText_Lexer
     */
    private $_lexer;

    /**
     * Modes for a lexer. Each mode has name (label) and sets of regular expression
     * patterns. Mode is registred in the Lexer.
     * @var array
     */
    private $_modes = array();

    /**
     * Modes can be nested, one mode can contain other. This is archieved via
     * connectModes method. This flag determine that modes was connected.
     * @var array
     */
    private $_connected = false;

    public function setHandler(WikiText_Handler $handler)
    {
        $this->_handler=$handler;
    }

    public function getHandler()
    {
        return $this->_handler;
    }

    public static function getParserModes()
    {
        return self::$_parserModes;
    }

    public function addBaseMode(WikiText_Parser_Mode_Base $baseMode) 
    {
        $this->_modes['base'] = $baseMode;
        if (!isset($this->_lexer)) $this->_lexer=new WikiText_Lexer($this->_handler,'base',true);

        $this->_modes['base']->Lexer=$this->_lexer;
    }

    /**
     * PHP preserves order of associative elements
     * Mode sequence is important
     */
    public function addMode($name,WikiText_Parser_Mode $mode) 
    {
        if ( !isset($this->_modes['base']) ) {
            $this->addBaseMode(new WikiText_Parser_Mode_Base());
        }
        $mode->Lexer = $this->_lexer;
        $this->_modes[$name] = $mode;
    }

    public function connectModes() 
    {
        if ($this->_connected) return;

        foreach ( array_keys($this->_modes) as $mode ) {
            // Base isn't connected to anything
            if ($mode == 'base') continue;

            $this->_modes[$mode]->preConnect();

            foreach(array_keys($this->_modes) as $cm) {
                if ($this->_modes[$cm]->accepts($mode) ) {
                    $this->_modes[$mode]->connectTo($cm);
                }
            }

            $this->_modes[$mode]->postConnect();
        }//endforeach

        $this->_connected=true;
    }

    /** Main entry point for the parser
     *  - Connects modes
     *  - padds document 
     * @param string $doc document with the wiki markup for the parsing
     * @return array array with the instructions from the handler for the document
     */
    public function parse($doc) {
        if (isset($this->_lexer)) {
            $this->connectModes();
            // Normalize CRs and pad doc
            $doc = "\n".str_replace("\r\n","\n",$doc)."\n";
            $this->_lexer->parse($doc);

            $this->_handler->finalise();
            return $this->_handler->getCalls();
        } else {
            return false;
        }
    }

}



