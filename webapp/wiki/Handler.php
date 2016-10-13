<?php
/**
 * Main handler ipmlementation
 *
 * @package Wikitext
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Miroslav Kubelik (koubel@volny.cz)
 * @license http://opensource.org/licenses/gpl-license.php GNU GPL
 */

/**
 * The block rewritter.
 *
 * This the handler for the blocks. It isn't a handler in the sense like
 * WikiText_Handler, because it doesn't receive tokens from the lexer. It takes
 * a whole stack of calls via the $calls parameter on the process() method. Then
 * searches all occurences for the 'oel' calls and replace this calls and calls
 * for the correct block 'p_open', 'p_close' calls.
 *
 * @package Wikitext
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Miroslav Kubelik <koubel@volny.cz>
 * @license http://opensource.org/licenses/gpl-license.php GNU GPL
 */
class WikiText_Handler
{
    /** the callwriter, it is responsible for a writing calls into the array
     * array of calls.
     * @var WikiText_Handler_CallWriter
     */
    private $_callWriter;

    /**
     * status array, used for the section status if we are in the section
     * $_status["section"] is true
     * @see reset()  
     * @var array
     */
    private $_status;

    /**
     * Initializes callWriter and call reset().
     */
    public function __construct()
    {
        $this->_callWriter = new WikiText_Handler_CallWriter();
        $this->reset();
    }

    /**
     * Resets the handler into the start state.
     *  - clear callWriter array  
     *  - set status array for the default states
     */ 
    public function reset()
    {
        $this->_callWriter->clear();

        $this->_status = array(
            'section' => false
        );
    }

    /**
     * Returns a whole current array of calls
     * @return array
     */   
    public function getCalls()
    {
        return $this->_callWriter->getCalls();
    }

    /**
     * Adds call into the calls array via the callWriter
     * @param string $handler instruction name
     * @param array $args instruction parameters
     * @param int $pos byte offset for the corresponding tokens in the source text
     */
    private function _addCall($handler, $args, $pos)
    {
        $call = array($handler,$args, $pos);
        $this->_callWriter->writeCall($call);
    }

    /**
     * Finalizes handler process and writes some end instructions
     * Is also processes eol instructions via callWriter rewriteBlocks() call.
     */
    public function finalise()
    {
        $this->_callWriter->finalise();

        //add "en of section" instruction if we are in the section
        if ( $this->_status['section'] ) {
            $lastCall = $this->_callWriter->getLastCall();
            $this->_callWriter->writeCall(array('section_close',array(), $lastCall[2]));
        }

        //try to find blocks (sets of instructions delimited with eol instructions)
        //and rewrite it into the p_open/p_close blocks
        $this->_callWriter->rewriteBlocks();

        //write "document_start" at the top of the instruction array
        $this->_callWriter->writeOnTop( array('document_start',array(),0) );

        //write "document_end" at the end of the instruction array 
        $lastCall = $this->_callWriter->getLastCall();
        $this->_callWriter->writeCall(array('document_end',array(), $lastCall[2]));
    }

    /** base mode handler
     * @return boolean
     */
    public function base($match, $state, $pos)
    {
        switch ( $state ) {
        case WikiText_Lexer::UNMATCHED:
            $this->_addCall('cdata',array($match), $pos);
            return true;
            break;
        }
    }

    /** header mode handler
     * @return boolean
     */
    public function header($match, $state, $pos)
    {
        // get level and title
        $title = trim($match);
        $level = 7 - strspn($title,'=');
        if($level < 1) $level = 1;
        $title = trim($title,'=');
        $title = trim($title);

        if ($this->_status['section']) $this->_addCall('section_close',array(),$pos);

        $this->_addCall('header',array($title,$level,$pos), $pos);

        $this->_addCall('section_open',array($level),$pos);
        $this->_status['section'] = true;
        return true;
    }

    /** notoc  handler
     * @return boolean
     */
    public function notoc($match, $state, $pos)
    {
        $this->_addCall('notoc',array(),$pos);
        return true;
    }

    /** linebreak handler
     * @return boolean
     */
    public function linebreak($match, $state, $pos)
    {
        $this->_addCall('linebreak',array(),$pos);
        return true;
    }

    /** Eol handler.
     * Eol calls are rewritten in the finalise() 
     * @return boolean
     */
    function eol($match, $state, $pos) {
        $this->_addCall('eol',array(),$pos);
        return true;
    }

    /** hr handler
     * @return boolean
     */
    public function hr($match, $state, $pos)
    {
        $this->_addCall('hr',array(),$pos);
        return true;
    }

    /** A nandler for the special tags, which are nested.
     * E.g. for the bold makes bold_open, bold_cdata, for the emphasis makes
     * emphasis_open, emphasis_cdata etc
     */
    private function _nestingTag($match, $state, $pos, $name) {
        switch ( $state ) {
        case WikiText_Lexer::ENTER:
            $this->_addCall($name.'_open', array(), $pos);
            break;
        case WikiText_Lexer::LEXIT:
            $this->_addCall($name.'_close', array(), $pos);
            break;
        case WikiText_Lexer::UNMATCHED:
            $this->_addCall('cdata',array($match), $pos);
            break;
        }
    }

    /** strong handler
     * Makes instruction via _nestingTag()
     * @return boolean
     */
    public function strong($match, $state, $pos)
    {
        $this->_nestingTag($match, $state, $pos, 'strong');
        return true;
    }

    /** strong handler
     * Makes instruction via _nestingTag()
     * @return boolean
     */
    public function emphasis($match, $state, $pos)
    {
        $this->_nestingTag($match, $state, $pos, 'emphasis');
        return true;
    }

    /** underline handler
     * Makes instruction via _nestingTag()
     * @return boolean
     */
    public function underline($match, $state, $pos)
    {
        $this->_nestingTag($match, $state, $pos, 'underline');
        return true;
    }

    /** monospace handler
     * Makes instruction via _nestingTag()
     * @return boolean
     */
    public function monospace($match, $state, $pos)
    {
        $this->_nestingTag($match, $state, $pos, 'monospace');
        return true;
    }

    /** subscript handler
     * Makes instruction via _nestingTag()
     * @return boolean
     */
    function subscript($match, $state, $pos)
    {
        $this->_nestingTag($match, $state, $pos, 'subscript');
        return true;
    }

    /** superscript handler
     * Makes instruction via _nestingTag()
     * @return boolean
     */
    public function superscript($match, $state, $pos)
    {
        $this->_nestingTag($match, $state, $pos, 'superscript');
        return true;
    }

    /** a handler for a deleted text
     * Makes instruction via _nestingTag()
     * @return boolean
     */
    public function deleted($match, $state, $pos)
    {
        $this->_nestingTag($match, $state, $pos, 'deleted');
        return true;
    }

    /** footnote handler
     * little-bit hackish
     * @return boolean
     */
    public function footnote($match, $state, $pos)
    {
        switch ( $state ) {
        case WikiText_Lexer::ENTER:
            //makes new nested level of list
            $reWriter = new WikiText_Handler_Footnote($this->_callWriter);
            $this->_callWriter = $reWriter;
            $this->_addCall('footnote_open', array(), $pos);
            break;
        case WikiText_Lexer::LEXIT:
            $this->_addCall('footnote_close', array(), $pos);
            $this->_callWriter->process();
            //end of the current levet, repair old callWriter
            $reWriter = $this->_callWriter;
            $this->_callWriter = $reWriter->getCallWriter();
            break;
        case WikiText_Lexer::MATCHED:
            break;
        case WikiText_Lexer::UNMATCHED:
            $this->_addCall('cdata', array($match), $pos);
            break;
        }
        return true;
    }

    /** listblock handler
     * @return boolean
     */
    public function listblock($match, $state, $pos)
    {
        switch ( $state ) {
        case WikiText_Lexer::ENTER:
            //makes new nested level of list
            $reWriter = new WikiText_Handler_List($this->_callWriter);
            $this->_callWriter = $reWriter;
            $this->_addCall('list_open', array($match), $pos);
            break;
        case WikiText_Lexer::LEXIT:
            $this->_addCall('list_close', array(), $pos);
            $this->_callWriter->process();
            //end of the current levet, repair old callWriter
            $reWriter = $this->_callWriter;
            $this->_callWriter = $reWriter->getCallWriter();
            break;
        case WikiText_Lexer::MATCHED:
            $this->_addCall('list_item', array($match), $pos);
            break;
        case WikiText_Lexer::UNMATCHED:
            $this->_addCall('cdata', array($match), $pos);
            break;
        }
        return true;
    }

    /** handler for the unformatted text
     * @return boolean
     */
    public function unformatted($match, $state, $pos)
    {
        if ( $state == WikiText_Lexer::UNMATCHED ) {
            $this->_addCall('unformatted',array($match), $pos);
        }
        return true;
    }

    /** handler for the direct html text
     * @return boolean
     */
    public function html($match, $state, $pos)
    {
        if ( $state == WikiText_Lexer::UNMATCHED ) {
            $this->_addCall('html',array($match), $pos);
        }
        return true;
    }

    /** handler for the preformatted text
     * @return boolean
     */
    public function preformatted($match, $state, $pos)
    {
        switch ( $state ) {
        case WikiText_Lexer::ENTER:
            $reWriter = new WikiText_Handler_Preformatted($this->_callWriter);
            $this->_callWriter = $reWriter;
            $this->_addCall('preformatted_start',array(), $pos);
            break;
        case WikiText_Lexer::LEXIT:
            $this->_addCall('preformatted_end',array(), $pos);
            $this->_callWriter->process();
            $reWriter = $this->_callWriter;
            $this->_callWriter = $reWriter->getCallWriter();
            break;
        case WikiText_Lexer::MATCHED:
            $this->_addCall('preformatted_newline',array(), $pos);
            break;
        case WikiText_Lexer::UNMATCHED:
            $this->_addCall('preformatted_content',array($match), $pos);
            break;
        }

        return true;
    }

    /** handler for the quoted text
     * @return boolean
     */
    public function quote($match, $state, $pos)
    {
        switch ( $state ) {
        case WikiText_Lexer::ENTER:
            $reWriter = new WikiText_Handler_Quote($this->_callWriter);
            $this->_callWriter = $reWriter;
            $this->_addCall('quote_start',array($match), $pos);
            break;
        case WikiText_Lexer::LEXIT:
            $this->_addCall('quote_end',array(), $pos);
            $this->_callWriter->process();
            $reWriter = $this->_callWriter;
            $this->_callWriter = $reWriter->getCallWriter();
            break;
        case WikiText_Lexer::MATCHED:
            $this->_addCall('quote_newline',array($match), $pos);
            break;
        case WikiText_Lexer::UNMATCHED:
            $this->_addCall('cdata',array($match), $pos);
            break;
        }

        return true;
    }

    /** handler for the source code text
     * @return boolean
     */
    public function code($match, $state, $pos)
    {
        switch ( $state ) {
        case WikiText_Lexer::UNMATCHED:
            $matches = preg_split('/>/u',$match,2);
            $matches[0] = trim($matches[0]);
            if ( trim($matches[0]) == '' ) $matches[0] = NULL;
            # $matches[0] contains name of programming language
            # if available, We shortcut html here.
            if($matches[0] == 'html') $matches[0] = 'html4strict';
            $this->_addCall('code',array($matches[1],$matches[0]),$pos);
            break;
        }

        return true;
    }

    /** handler for the acronyms
     * @return boolean
     */
    public function acronym($match, $state, $pos)
    {
        $this->_addCall('acronym',array($match), $pos);
        return true;
    }

    /** handler for the smiles
     * @return boolean
     */
    public function smiley($match, $state, $pos)
    {
        $this->_addCall('smiley',array($match), $pos);
        return true;
    }

    public function entity($match, $state, $pos)
    {
        $this->_addCall('entity',array($match), $pos);
        return true;
    }

    public function multiplyentity($match, $state, $pos)
    {
        preg_match_all('/\d+/',$match,$matches);
        $this->_addCall('multiplyentity',array($matches[0][0],$matches[0][1]), $pos);
        return true;
    }

    public function singlequoteopening($match, $state, $pos)
    {
        $this->_addCall('singlequoteopening',array(), $pos);
        return true;
    }

    public function singlequoteclosing($match, $state, $pos)
    {
        $this->_addCall('singlequoteclosing',array(), $pos);
        return true;
    }

    public function apostrophe($match, $state, $pos)
    {
        $this->_addCall('apostrophe',array(), $pos);
        return true;
    }

    function doublequoteopening($match, $state, $pos)
    {
        $this->_addCall('doublequoteopening',array(), $pos);
        return true;
    }

    public function doublequoteclosing($match, $state, $pos)
    {
        $this->_addCall('doublequoteclosing',array(), $pos);
        return true;
    }

    public function internallink($match, $state, $pos)
    {
        // Strip the opening and closing markup
        $link = preg_replace(array('/^\[\[/','/\]\]$/u'),'',$match);

        // Split title from URL
        $link = preg_split('/\|/u',$link,2);
        if ( !isset($link[1]) ) {
            $link[1] = NULL;
        } elseif ( preg_match('/^\{\{[^\}]+\}\}$/',$link[1]) ) {
            //If the title is an image, convert it to an array containing the image details
            $link[1] = self::ParseMedia($link[1]);
        }
        $link[0] = trim($link[0]);

        //decide which kind of link it is
        if ( preg_match('/^[a-zA-Z\.]+>{1}.*$/u',$link[0]) ) {
            // Interwiki
            $interwiki = preg_split('/>/u',$link[0]);
            $this->_addCall('interwikilink',array($link[0],$link[1],strtolower($interwiki[0]),$interwiki[1]),
                $pos);
        } elseif ( preg_match('#^([a-z0-9\-\.+]+?)://#i',$link[0]) ) {
            // external link (accepts all protocols)
            $this->_addCall('externallink',array($link[0],$link[1]),$pos);
        } elseif ( preg_match('<'.WikiText_Parser_Mode_EmailLink::PREG_PATTERN_VALID_EMAIL.'>',$link[0]) ) {
            // E-Mail (pattern above is defined in inc/mail.php)
            $this->_addCall('emaillink',array($link[0],$link[1]),$pos);
        } else {
            // internal link
            $this->_addCall('internallink',array($link[0],$link[1]),$pos);
        }

        return true;
    }

    public function externallink($match, $state, $pos)
    {
        $url   = $match;
        $title = null;

        // add protocol on simple short URLs
        if(substr($url,0,3) == 'ftp' && (substr($url,0,6) != 'ftp://')){
            $title = $url;
            $url   = 'ftp://'.$url;
        }
        if(substr($url,0,3) == 'www' && (substr($url,0,7) != 'http://')){
            $title = $url;
            $url = 'http://'.$url;
        }

        $this->_addCall('externallink',array($url, $title), $pos);
        return true;
    }

    public function media($match, $state, $pos)
    {
        $p = self::ParseMedia($match);

        $this->_addCall($p['type'],
            array($p['src'], $p['title'], $p['align'], $p['width'], $p['height'],
            NULL, $p['linking']),$pos);

        return true;
    }

    public function emaillink($match, $state, $pos)
    {
        $email = preg_replace(array('/^</','/>$/'),'',$match);
        $this->_addCall('emaillink',array($email, NULL), $pos);
        return true;
    }

    /**
     * Table handler.
     * Uses WikiText_Handler_Table for a table handling.
     */
    public function table($match, $state, $pos)
    {
        switch ( $state ) {

        case WikiText_Lexer::ENTER:

            $reWriter = new WikiText_Handler_Table($this->_callWriter);
            $this->_callWriter = $reWriter;

            $this->_addCall('table_start', array(), $pos);
            //$this->_addCall('table_row', array(), $pos);
            if ( trim($match) == '^' ) {
                $this->_addCall('tableheader', array(), $pos);
            } else {
                $this->_addCall('tablecell', array(), $pos);
            }
            break;

        case WikiText_Lexer::LEXIT:
            $this->_addCall('table_end', array(), $pos);
            $this->_callWriter->process();
            $reWriter = $this->_callWriter;
            $this->_callWriter = $reWriter->getCallWriter();
            break;

        case WikiText_Lexer::UNMATCHED:
            if ( trim($match) != '' ) {
                $this->_addCall('cdata',array($match), $pos);
            }
            break;

        case WikiText_Lexer::MATCHED:
            if ( $match == ' ' ){
                $this->_addCall('cdata', array($match), $pos);
            } else if ( preg_match('/\t+/',$match) ) {
                $this->_addCall('table_align', array($match), $pos);
            } else if ( preg_match('/ {2,}/',$match) ) {
                $this->_addCall('table_align', array($match), $pos);
            } else if ( $match == "\n|" ) {
                $this->_addCall('table_row', array(), $pos);
                $this->_addCall('tablecell', array(), $pos);
            } else if ( $match == "\n^" ) {
                $this->_addCall('table_row', array(), $pos);
                $this->_addCall('tableheader', array(), $pos);
            } else if ( $match == '|' ) {
                $this->_addCall('tablecell', array(), $pos);
            } else if ( $match == '^' ) {
                $this->_addCall('tableheader', array(), $pos);
            }
            break;
        }
        return true;
    }


    /**
     * Parses whole text which is responsible for the media line.
     * @param string $match whole line for the media token e.g. {{http://some.net/img.png?200x50 |caption}}
     *
     * @return array with the media options
     */
    public static function ParseMedia($match)
    {
        // Strip the opening and closing markup
        $link = preg_replace(array('/^\{\{/','/\}\}$/u'),'',$match);

        // Split title from URL
        $link = preg_split('/\|/u',$link,2);

        // Check alignment
        $ralign = (bool)preg_match('/^ /',$link[0]);
        $lalign = (bool)preg_match('/ $/',$link[0]);

        // Logic = what's that ;)...
        if ( $lalign & $ralign ) {
            $align = 'center';
        } else if ( $ralign ) {
            $align = 'right';
        } else if ( $lalign ) {
            $align = 'left';
        } else {
            $align = NULL;
        }

        // The title...
        if ( !isset($link[1]) ) {
            $link[1] = NULL;
        }

        //remove aligning spaces
        $link[0] = trim($link[0]);

        //split into src and parameters (using the very last questionmark)
        $pos = strrpos($link[0], '?');
        if($pos !== false){
            $src   = substr($link[0],0,$pos);
            $param = substr($link[0],$pos+1);
        }else{
            $src   = $link[0];
            $param = '';
        }

        //parse width and height
        if(preg_match('#(\d+)(x(\d+))?#i',$param,$size)){
            ($size[1]) ? $w = $size[1] : $w = NULL;
            ($size[3]) ? $h = $size[3] : $h = NULL;
        } else {
            $w = NULL;
            $h = NULL;
        }

        //get linking command
        if(preg_match('/nolink/i',$param)){
            $linking = 'nolink';
        }elseif(preg_match('/direct/i',$param)){
            $linking = 'direct';
        }else{
            $linking = 'details';
        }

        // Check whether this is a local or remote image
        if ( preg_match('#^(https?|ftp)#i',$src) ) {
            $call = 'externalmedia';
        } else {
            $call = 'internalmedia';
        }

        $params = array(
            'type'=>$call,
            'src'=>$src,
            'title'=>$link[1],
            'align'=>$align,
            'width'=>$w,
            'height'=>$h,
            'cache'=>null,
            'linking'=>$linking,
        );

        return $params;
    }
}
