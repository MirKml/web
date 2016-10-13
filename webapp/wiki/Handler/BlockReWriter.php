<?php
/**
 * The block rewriter implementation.
 *
 * @package koubel homepage
 * @author Miroslav Kubelik (koubel@volny.cz)
 * @copyright Copyright (c) 2007-2010 Miroslav Kubelik
 * @license http://opensource.org/licenses/gpl-license.php GNU GPL
 */

/**
 * The block rewriter.
 *
 * This the handler for the blocks. It isn't a handler in the sense like 
 * WikiText_Handler, because it doesn't receive tokens from the lexer. It takes
 * a whole stack of calls via the $calls parameter on the process() method. Then
 * searches all occurences for the 'oel' calls and replace this calls and calls
 * for the correct block 'p_open', 'p_close' calls.
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Miroslav Kubelik <koubel@volny.cz> 
 * @license http://opensource.org/licenses/gpl-license.php GNU GPL
 */  
class WikiText_Handler_BlockReWriter
{
    /**
     * array with the final set of calls
     * @var array
     */     
    private $_calls = array();

    /**
     * stack for calls for the one block
     * @var array
     */     
    private $_blockStack = array();

    /**
     * "we are in paragraph" flag
     * @var bool
     */     
    private $_inParagraph = false;

    /**
     * flag for the start of the paragraph
     * @var bool
     */     
    private $_atStart = true;

    /**
     * the marker for the instruction skipping
     * @var int
     */     
    private $_skipEolKey = -1;

    /**
     * blocks these should not be inside paragraphs - opening instructions
     * @var array
     */    
    private $_blockOpen = array(
        'header',
        'listu_open','listo_open','listitem_open','listcontent_open',
        'table_open','tablerow_open','tablecell_open','tableheader_open',
        'quote_open',
        'section_open', // Needed to prevent p_open between header and section_open
        'code','file','hr','preformatted','rss',
    );

    /**
     * blocks these should not be inside paragraphs - closing instructions
     * @var array
     */    
    private $_blockClose = array(
        'header',
        'listu_close','listo_close','listitem_close','listcontent_close',
        'table_close','tablerow_close','tablecell_close','tableheader_close',
        'quote_close',
        'section_close', // Needed to prevent p_close after section_close
        'code','file','hr','preformatted','rss',
    );

    /**
     * blocks these can be inside paragraphs - opening instructions
     * @var array
     */    
    private $_stackOpen = array(
        'footnote_open','section_open',
    );

    /**
     * blocks these can be inside paragraphs - closing instructions
     * @var array
     */    
    private $_stackClose = array(
        'footnote_close','section_close',
    );


    /**
     * Close a paragraph if needed. 
     * 
     * This function makes sure there are no empty paragraphs on the stack
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    private function _closeParagraph($pos)
    {
        // look back if there was any content - we don't want empty paragraphs
        $content = '';
        for($i=count($this->_calls)-1; $i>=0; $i--) {
            if ($this->_calls[$i][0] == 'p_open') {
                break;
            } elseif($this->_calls[$i][0] == 'cdata'){
                $content .= $this->_calls[$i][1][0];
            } else {
                $content = 'found markup';
                break;
            }
        }

        if(trim($content)==''){
            //remove the whole paragraph
            array_splice($this->_calls,$i);
        } else {
            $this->_calls[] = array('p_close',array(), $pos);
        }

        $this->_inParagraph = false;
    }

    /**
     * Processes the whole instruction stack for the wiki text document for handlind 
     * for the opening and closing paragraphs. 
     * 
     * Fills the $this->_calls array woth the instructions 
     *
     * @param array $calls a whole wiki document instruction stack
     * @return array a new document stack
     *     
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @author Andreas Gohr <andi@splitbrain.org>
     * @todo This thing is really messy and should be rewritten
     */
    public function process($calls)
    {
        foreach ( $calls as $key => $call ) {
            $callName = $call[0];

            // Process blocks which are stack like... (contain linefeeds)
            if ( in_array($callName,$this->_stackOpen ) ) {

                $this->_calls[] = $call;

                // Hack - footnotes shouldn't immediately contain a p_open
                if ( $callName != 'footnote_open' ) {
                    $this->_addToStack();
                } else {
                    $this->_addToStack(false);
                }
                continue;
            }

            if ( in_array($callName,$this->_stackClose ) ) {

                if ( $this->_inParagraph ) {
                    $this->_closeParagraph($call[2]);
                }
                $this->_calls[] = $call;
                $this->_removeFromStack();
                continue;
            }

            if ( !$this->_atStart ) {
                if ( $callName == 'eol' ) {
                    // Check this isn't an eol instruction to skip...
                    if ( $this->_skipEolKey != $key ) {
                        // Look to see if the next instruction is an EOL
                        if ( isset($calls[$key+1]) && $calls[$key+1][0] == 'eol' ) {
                            if ( $this->_inParagraph ) {
                                //$this->_calls[] = array('p_close',array(), $call[2]);
                                $this->_closeParagraph($call[2]);
                            }

                            $this->_calls[] = array('p_open',array(), $call[2]);
                            $this->_inParagraph = true;


                            // Mark the next instruction for skipping
                            $this->_skipEolKey = $key+1;

                        } else {
                            //if this is just a single eol make a space from it
                            $this->_calls[] = array('cdata',array(" "), $call[2]);
                        }
                    }
                } else {
                    $storeCall = true;
                    if ( $this->_inParagraph && (in_array($callName, $this->_blockOpen)) ) {
                        $this->_closeParagraph($call[2]);
                        $this->_calls[] = $call;
                        $storeCall = false;
                    }
                    if ( in_array($callName, $this->_blockClose) ) {
                        if ( $this->_inParagraph ) {
                            $this->_closeParagraph($call[2]);
                        }
                        if ( $storeCall ) {
                            $this->_calls[] = $call;
                            $storeCall = false;
                        }

                        //This really sucks and suggests this whole class sucks but...
                        if ( isset($calls[$key+1])) {
                            $cname_plusone = $calls[$key+1][0];
                            $plugin_plusone = false;

                            if ((!in_array($cname_plusone, $this->_blockOpen) && !in_array($cname_plusone, $this->_blockClose)) ||
                                ($plugin_plusone && $plugin_test)
                            ) {

                                $this->_calls[] = array('p_open',array(), $call[2]);
                                $this->_inParagraph = true;
                            }
                        }
                    }

                    if ( $storeCall ) {
                        $this->_calls[] = $call;
                    }
                }
            } else {
                // Unless there's already a block at the start, start a paragraph
                if ( !in_array($callName,$this->_blockOpen) ) {
                    $this->_calls[] = array('p_open',array(), $call[2]);
                    if ( $call[0] != 'eol' ) {
                        $this->_calls[] = $call;
                    }
                    $this->_atStart = false;
                    $this->_inParagraph = true;
                } else {
                    $this->_calls[] = $call;
                    $this->_atStart = false;
                }

            }
        }//end foreach

        if ( $this->_inParagraph ) {
            if ( $callName == 'p_open' ) {
                // Ditch the last call
                array_pop($this->_calls);
            } else if ( !in_array($callName, $this->_blockClose) ) {
                //$this->_calls[] = array('p_close',array(), $call[2]);
                $this->_closeParagraph($call[2]);
            } else {
                $last_call = array_pop($this->_calls);
                //$this->_calls[] = array('p_close',array(), $call[2]);
                $this->_closeParagraph($call[2]);
                $this->_calls[] = $last_call;
            }
        }

        return $this->_calls;
    }

    private function _addToStack($newStart = true)
    {
        $this->_blockStack[] = array($this->_atStart, $this->_inParagraph);
        $this->_atStart = $newStart;
        $this->_inParagraph = false;
    }

    private function _removeFromStack()
    {
        $state = array_pop($this->_blockStack);
        $this->_atStart = $state[0];
        $this->_inParagraph = $state[1];
    }

}
