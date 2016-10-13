<?php
/**
 * Renderer for XHTML output
 */

/**
 * The Renderer
 */
class WikiText_Renderer_Xhtml extends WikiText_Renderer 
{
    /** line feed */ 
    const LF  ="\n";

    /** tabulator */
    const TAB ="\t";

    /**
     * will contain the whole document  
     * @var string
     */  
    protected $_doc = '';

    /**
     * table of content array  
     * @var string
     */  
    private $_toc = array();   

    /**
     * base URL, is used as prefix in the smiley url  
     * @var string
     */  
    private $_baseUrl; 

    private $_headers = array();
    private $_footnotes = array();

    /**
     * a suspensory string for document content when footnotes instructions 
     * are rendered
     * @var string
     */      
    private $_store;

    public function setBaseUrl($url)
    {
        $this->_baseUrl=$url;
    }

    public function getFormat()
    {
        return 'xhtml';
    }

    public function getOutput()
    {
        return $this->_doc;
    }

    public function document_start()
    {
        //reset some internals
        $this->_toc = array();
        $this->_headers = array();
    }

    public function document_end()
    {
        if ( count ($this->_footnotes) > 0 ) {
            $this->_doc .= '<div class="footnotes">'.self::LF;

            $id = 0;
            foreach ( $this->_footnotes as $footnote ) {
                $id++;   // the number of the current footnote

                // check its not a placeholder that indicates actual footnote text is elsewhere
                if (substr($footnote, 0, 5) != "@@FNT") {
                    // open the footnote and set the anchor and backlink
                    $this->_doc .= '<div class="fn">';
                    $this->_doc .= '<a href="#fnt__'.$id.'" id="fn__'.$id.'" name="fn__'.$id.'" class="fn_bot">';
                    $this->_doc .= $id.')</a> '.self::LF;

                    // get any other footnotes that use the same markup
                    $alt = array_keys($this->_footnotes, "@@FNT$id");

                    if (count($alt)) {
                        foreach ($alt as $ref) {
                            // set anchor and backlink for the other footnotes
                            $this->_doc .= ', <a href="#fnt__'.($ref+1).'" id="fn__'.($ref+1).'" name="fn__'.($ref+1).'" class="fn_bot">';
                            $this->_doc .= ($ref+1).')</a> '.self::LF;
                        }
                    }

                    // add footnote markup and close this footnote
                    $this->_doc .= $footnote;
                    $this->_doc .= '</div>' . self::LF;
                }
            }

            $this->_doc .= '</div>'.self::LF;
        }

        // prepend the TOC
        if ($this->_renderToc) {
            $this->_doc = $this->render_TOC($this->_toc).$this->_doc;
        }

        //make sure there are no empty paragraphs
        $this->_doc = preg_replace('#<p>\s*</p>#','',$this->_doc);
    }

    /**
     * Return the TOC rendered to XHTML
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function render_TOC($toc=null)
    {
        if(is_null($toc) && is_array($this->_toc)) $toc = $this->_toc;

        if(count($toc) < 3) return '';

        $out  = '<div class="toc">'.self::LF;
        $out .= '<div class="tocheader toctoggle" id="toc__header">';
        $out .= "table of content";
        $out .= '</div>'.self::LF;
        $out .= '<div id="toc__inside">'.self::LF;
        $out .= html_buildlist($toc,'toc',array(__CLASS__,'_tocitem'));
        $out .= '</div>'.self::LF.'</div>'.self::LF;
        return $out;
    }

    /**
     * Callback for html_buildlist
     */
    private function _tocitem($item)
    {
        return '<span class="li"><a href="#'.$item['hid'].'" class="toc">'.
            self::_xmlEntities($item['title']).'</a></span>';
    }

    public function toc_additem($id, $text, $level)
    {
        //handle TOC
        // the TOC is one of our standard ul list arrays ;-)
        $this->toc[] = array( 'hid'   => $id,
            'title' => $text,
            'type'  => 'ul',
            'level' => $level);
    }

    public function header($text, $level, $pos)
    {
        $hid = $this->_headerToLink("headerId",true);

        //only add items within configured levels
        $this->toc_additem($hid, $text, $level);

        // write the header
        $this->_doc .= self::LF.'<h'.$level.' class="heading'.$level.'"><a name="'.$hid.'" id="'.$hid.'">';
        $this->_doc .= self::_xmlEntities($text);
        $this->_doc .= "</a></h$level>".self::LF;
    }

    public function section_open($level)
    {
        $this->_doc .= "<div class=\"level$level\">".self::LF;
    }

    public function section_close()
    {
        $this->_doc .= self::LF.'</div>'.self::LF;
    }

    public function cdata($text)
    {
        $this->_doc .= self::_xmlEntities($text);
    }

    public function p_open()
    {
        $this->_doc .= self::LF.'<p>'.self::LF;
    }

    public function p_close()
    {
        $this->_doc .= self::LF.'</p>'.self::LF;
    }

    public function linebreak()
    {
        $this->_doc .= '<br/>'.self::LF;
    }

    public function hr()
    {
        $this->_doc .= '<hr />'.self::LF;
    }

    public function strong_open()
    {
        $this->_doc .= '<strong>';
    }

    public function strong_close()
    {
        $this->_doc .= '</strong>';
    }

    public function emphasis_open()
    {
        $this->_doc .= '<em>';
    }

    public function emphasis_close()
    {
        $this->_doc .= '</em>';
    }

    public function underline_open()
    {
        $this->_doc .= '<em class="u">';
    }

    public function underline_close()
    {
        $this->_doc .= '</em>';
    }

    public function monospace_open()
    {
        $this->_doc .= '<code>';
    }

    public function monospace_close()
    {
        $this->_doc .= '</code>';
    }

    public function subscript_open()
    {
        $this->_doc .= '<sub>';
    }

    public function subscript_close()
    {
        $this->_doc .= '</sub>';
    }

    public function superscript_open()
    {
        $this->_doc .= '<sup>';
    }

    public function superscript_close()
    {
        $this->_doc .= '</sup>';
    }

    public function deleted_open()
    {
        $this->_doc .= '<del>';
    }

    public function deleted_close()
    {
        $this->_doc .= '</del>';
    }


    /**
     * Callback for footnote start syntax
     *
     * All following content will go to the footnote instead of
     * the document. To achieve this the previous rendered content
     * is moved to $_store and $_doc is cleared
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function footnote_open()
    {
        // move current content to store and record footnote
        $this->_store = $this->_doc;
        $this->_doc   = '';
    }

    /**
     * Callback for footnote end syntax
     *
     * All rendered content is moved to the $_footnotes array and the old
     * content is restored from $_store again
     *
     * @author Andreas Gohr
     */
    public function footnote_close()
    {
        // recover footnote into the stack and restore old content
        $footnote = $this->_doc;
        $this->_doc = $this->_store;
        $this->_store = null;

        // check to see if this footnote has been seen before
        $i = array_search($footnote, $this->_footnotes);

        if ($i === false) {
            // its a new footnote, add it to the $_footnotes array
            $id = count($this->_footnotes)+1;
            $this->_footnotes[count($this->_footnotes)] = $footnote;
        } else {
            // seen this one before, translate the index to an id and save a placeholder
            $i++;
            $id = count($this->_footnotes)+1;
            $this->_footnotes[count($this->_footnotes)] = "@@FNT".($i);
        }

        // output the footnote reference and link
        $this->_doc .= '<a href="#fn__'.$id.'" name="fnt__'.$id.'" id="fnt__'.$id.'" class="fn_top">'.$id.')</a>';
    }

    public function listu_open()
    {
        $this->_doc .= '<ul>'.self::LF;
    }

    public function listu_close()
    {
        $this->_doc .= '</ul>'.self::LF;
    }

    public function listo_open()
    {
        $this->_doc .= '<ol>'.self::LF;
    }

    public function listo_close()
    {
        $this->_doc .= '</ol>'.self::LF;
    }

    public function listitem_open($level)
    {
        $this->_doc .= '<li class="level'.$level.'">';
    }

    public function listitem_close()
    {
        $this->_doc .= '</li>'.self::LF;
    }

    public function listcontent_open()
    {
        $this->_doc .= '<div class="li">';
    }

    public function listcontent_close()
    {
        $this->_doc .= '</div>'.self::LF;
    }

    public function unformatted($text)
    {
        $this->_doc .= '<code>'.self::_xmlEntities($text).'</code>';
    }

    /**
     * Insert HTML
     */
    public function html($text)
    {
        $this->_doc .= $text;
    }

    public function preformatted($text)
    {
        $this->_doc .= '<pre class="code">' . self::_xmlEntities($text) . '</pre>'. self::LF;
    }

    public function quote_open()
    {
        $this->_doc .= '<blockquote><div class="no">'.self::LF;
    }

    public function quote_close()
    {
        $this->_doc .= '</div></blockquote>'.self::LF;
    }

    /**
     * Callback for code text
     *
     * Uses GeSHi to highlight language syntax
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function code($text, $language = NULL)
    {
        if ( is_null($language) ) {
            $this->preformatted($text);
        } else {
            //strip leading and trailing blank line
            $text = preg_replace('/^\s*?\n/','',$text);
            $text = preg_replace('/\s*?\n$/','',$text);
            $codeHighlighter = new GeSHi_HighLighter($language);
            $this->_doc .= $codeHighlighter->getHighlightedCode($text);
        }
    }

    public function acronym($acronym)
    {
        if (array_key_exists($acronym,self::$_acronyms)) {
            $title = self::_xmlEntities(self::$_acronyms[$acronym]);
            $this->_doc.= '<acronym title="'.$title
                .'">'
                .self::_xmlEntities($acronym)
                .'</acronym>';
        } else {
            $this->_doc.=self::_xmlEntities($acronym);
        }
    }

    public function smiley($smiley)
    {
        if (array_key_exists($smiley,self::$_smileys)) {
            $title = self::_xmlEntities(self::$_smileys[$smiley]);
            $this->_doc.='<img src="'.$this->_baseUrl.'/images/smileys/'.self::$_smileys[$smiley]
                .'" alt="'
                .self::_xmlEntities($smiley)
                .'" />';
        } else {
            $this->_doc.=self::_xmlEntities($smiley);
        }
    }


    public function entity($entity)
    {
        if ( array_key_exists($entity, self::$_entities) ) {
            $this->_doc .= self::$_entities[$entity];
        } else {
            $this->_doc .= self::_xmlEntities($entity);
        }
    }

    public function multiplyentity($x, $y)
    {
        $this->_doc .= "$x&times;$y";
    }

    public function singlequoteopening()
    {
        $this->_doc .= '&sbquo;';
    }

    public function singlequoteclosing()
    {
        $this->_doc .= "&lsquo;";
    }

    public function apostrophe()
    {
        $this->_doc .= "&rsquo;";
    }

    public function doublequoteopening()
    {
        $this->_doc .= "&bdquo;";
    }

    public function doublequoteclosing()
    {
        $this->_doc .= "&bdquo;";
    }

    /**
     * Render an internal Wiki Link
     */
    public function internallink($id, $name = NULL)
    {
        //replace : with /
        $id=str_replace(":","/",$id);
        $name = $this->_getLinkTitle($name, $id, $isImage, $id);
        if ( !$isImage ) {
            $class='internallink';
        } else {
            $class='media';
        }

        //prepare for formating
        $link['target'] = "";
        $link['style']  = "";
        $link['pre']    = "";
        $link['suf']    = "";

        $link['more']   = "";
        $link['class']  = $class;
        $link['url']    = $this->_baseUrl.$id;
        $link['name']   = $name;
        $link['title']  = "";

        $this->_doc .= self::_formatLink($link);
    }

    public function externallink($url, $name = NULL)
    {
        $name = $this->_getLinkTitle($name, $url, $isImage);

        if ( !$isImage ) {
            $class='urlextern';
        } else {
            $class='media';
        }

        //prepare for formating
        $link['target'] = '';
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';
        $link['class']  = $class;
        $link['url']    = $url;

        $link['name']   = $name;
        $link['title']  = self::_xmlEntities($url);

        //output formatted
        $this->_doc .= self::_formatLink($link);
    }

    /**
     * renders interwiki link
     */
    public function interwikilink($match, $name = NULL, $wikiName, $wikiUri)
    {
        $link = array();
        $link['target'] = "";
        $link['pre']    = "";
        $link['suf']    = "";
        $link['more']   = "";
        $link['name']   = $this->_getLinkTitle($name, $wikiUri, $isImage);

        //get interwiki URL
        $url = self::_resolveInterWiki($wikiName,$wikiUri);

        if (!$isImage) {
            $class = preg_replace('/[^_\-a-z0-9]+/i','_',$wikiName);
            $link['class'] = "interwiki";
        } else {
            $link['class'] = 'media';
        }

        $link['url'] = $url;
        $link['title'] = "";

        //output formatted
        $this->_doc.=self::_formatLink($link);
    }

    /**
     * renders email link
     */
    public function emaillink($address, $name = NULL) 
    {
        //simple setup
        $link = array();
        $link['target'] = '';
        $link['pre']    = '';
        $link['suf']   = '';
        $link['style']  = '';
        $link['more']   = '';

        $name = $this->_getLinkTitle($name, '', $isImage);
        if ( !$isImage ) {
            $link['class']='mail';
        } else {
            $link['class']='media';
        }

        $address = self::_xmlEntities($address);
        //obfuscate mail address
        $obfuscate=array('@'=>' [at] ','.'=> ' [dot] ','-'=>' [dash] ');
        $address=strtr($address,$obfuscate);

        $title   = $address;

        if(empty($name)){
            $name = $address;
        }

        $address = rawurlencode($address);

        $link['url']   = 'mailto:'.$address;
        $link['name']  = $name;
        $link['title'] = $title;

        //output formatted
        $this->_doc .= self::_formatLink($link);
    }

    public function table_open()
    {
        $this->_doc .= '<table class="inline">'.self::LF;
    }

    public function table_close()
    {
        $this->_doc .= '</table>'.self::LF;
    }

    function tablerow_open()
    {
        $this->_doc .= self::TAB . '<tr>' . self::LF . self::TAB . self::TAB;
    }

    function tablerow_close()
    {
        $this->_doc .= self::LF . self::TAB . '</tr>' . self::LF;
    }

    public function tableheader_open($colspan = 1, $align = NULL)
    {
        $this->_doc .= '<th';
        if ( !is_null($align) ) {
            $this->_doc .= ' class="'.$align.'align"';
        }
        if ( $colspan > 1 ) {
            $this->_doc .= ' colspan="'.$colspan.'"';
        }
        $this->_doc .= '>';
    }

    public function tableheader_close()
    {
        $this->_doc .= '</th>';
    }

    public function tablecell_open($colspan = 1, $align = NULL)
    {
        $this->_doc .= '<td';
        if ( !is_null($align) ) {
            $this->_doc .= ' class="'.$align.'align"';
        }
        if ( $colspan > 1 ) {
            $this->_doc .= ' colspan="'.$colspan.'"';
        }
        $this->_doc .= '>';
    }

    public function tablecell_close()
    {
        $this->_doc .= '</td>';
    }

    /**
     * Build a link
     *
     * Assembles all parts defined in $link returns HTML for the link
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    private static function _formatLink($link)
    {
        //make sure the url is XHTML compliant (skip mailto)
        if(substr($link['url'],0,7) != 'mailto:'){
            $link['url'] = str_replace('&','&amp;',$link['url']);
            $link['url'] = str_replace('&amp;amp;','&amp;',$link['url']);
        }
        //remove double encodings in titles
        $link['title'] = str_replace('&amp;amp;','&amp;',$link['title']);

        // be sure there are no bad chars in url or title
        // (we can't do this for name because it can contain an img tag)
        $link['url']   = strtr($link['url'],array('>'=>'%3E','<'=>'%3C','"'=>'%22'));
        $link['title'] = strtr($link['title'],array('>'=>'&gt;','<'=>'&lt;','"'=>'&quot;'));

        $ret  = '';
        $ret .= $link['pre'];
        $ret .= '<a href="'.$link['url'].'"';
        if(!empty($link['class']))  $ret .= ' class="'.$link['class'].'"';
        if(!empty($link['target'])) $ret .= ' target="'.$link['target'].'"';
        if(!empty($link['title']))  $ret .= ' title="'.$link['title'].'"';
        if(!empty($link['style']))  $ret .= ' style="'.$link['style'].'"';
        if(!empty($link['more']))   $ret .= ' '.$link['more'];
        $ret .= '>';
        $ret .= $link['name'];
        $ret .= '</a>';
        $ret .= $link['suf'];
        return $ret;
    }


    public function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
        $height=NULL, $linking=NULL) 
    {
        $link = array();
        $link['class']  = 'media';
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';

        $link['title']  = self::_xmlEntities($src);
        $link['url']    = $src;
        $link['name']   = self::_media($src, $title, $align, $width, $height);
        $noLink = false;

        list($ext,$mime) = WikiText_Utils::mimetype($src);
        if(substr($mime,0,5) == 'image') {
            // link only jpeg images
            // if ($ext != 'jpg' && $ext != 'jpeg') $noLink = true;
        } elseif ($mime == 'application/x-shockwave-flash') {
            // don't link flash movies
            $noLink = true;
        } else {
            // add file icons
            $link['class'] .= ' mediafile mf_'.$ext;
        }

        //output formatted
        if ($linking == 'nolink' || $noLink) $this->_doc .= $link['name'];
        else $this->_doc .= self::_formatLink($link);
    }

    /**
     * Renders internal and external media
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    protected static function _media($src, $title=NULL, $align=NULL, $width=NULL, $height=NULL)
    {
        $ret = '';

        list($ext,$mime) = WikiText_Utils::mimetype($src);
        if(substr($mime,0,5) == 'image'){
            //add image tag
            $ret .= '<img src="'.$src.'"';
            $ret .= ' class="media'.$align.'"';

            if (!is_null($title)) {
                $ret .= ' title="'.self::_xmlEntities($title).'"';
                $ret .= ' alt="'.self::_xmlEntities($title).'"';
            } else{
                $ret .= ' alt=""';
            }

            if (!is_null($width)) $ret.=' width="'.self::_xmlEntities($width).'"';

            if (!is_null($height)) $ret .= ' height="'.self::_xmlEntities($height).'"';

            $ret.=' />';
        } elseif($mime == 'application/x-shockwave-flash'){
            $ret .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'
                .' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"';
            if ( !is_null($width) ) $ret .= ' width="'.self::_xmlEntities($width).'"';
            if ( !is_null($height) ) $ret .= ' height="'.self::_xmlEntities($height).'"';
            $ret .= '>'.self::LF;
            $ret .= '<param name="movie" value="'.ml($src).'" />'.self::LF;
            $ret .= '<param name="quality" value="high" />'.self::LF;
            $ret .= '<embed src="'.ml($src).'"'
                .' quality="high"';
            if ( !is_null($width) ) $ret .= ' width="'.self::_xmlEntities($width).'"';
            if ( !is_null($height) ) $ret .= ' height="'.self::_xmlEntities($height).'"';
            $ret .= ' type="application/x-shockwave-flash"'
                .' pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>'.self::LF;
            $ret .= '</object>'.self::LF;
        } elseif($title){
            // well at least we have a title to display
            $ret .= self::_xmlEntities($title);
        } else{
            // just show the sourcename
            $ret .= self::_xmlEntities(basename(noNS($src)));
        }

        return $ret;
    }

    /**
     * Creates a linkid from a headline
     * 
     * NOT FINISHED  
     *
     * @param string  $title   The headline title
     * @param boolean $create  Create a new unique ID?
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    protected function _headerToLink($title,$create=false) 
    {
        $title = str_replace(':','',$title);
        $title = ltrim($title,'0123456789._-');

        if($create){
            // make sure tiles are unique
            $num = '';
            while(in_array($title.$num,$this->_headers)){
                ($num) ? $num++ : $num = 1;
            }
            $title = $title.$num;
            $this->_headers[] = $title;
        }

        return $title;
    }

    protected static function _xmlEntities($string)
    {
        return htmlspecialchars($string,ENT_QUOTES,"UTF-8");
    }

    /**
     * Construct a title and handle images in titles
     */
    protected static function _getLinkTitle($title,$default,&$isImage) 
    {
        $isImage = false;
        if (is_null($title)) {
            return self::_xmlEntities($default);
        } elseif ( is_string($title) ) {
            return self::_xmlEntities($title);
        } elseif ( is_array($title) ) {
            $isImage = true;
            return self::_imageTitle($title);
        }
    }

    /**
     * Returns an HTML code for images used in link titles
     */
    protected static function _imageTitle($img) 
    {
        return self::_media($img['src'],
            $img['title'],
            $img['align'],
            $img['width'],
            $img['height']
        );
    }

}
