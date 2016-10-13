<?php
/**
 * Accepts text and breaks it into tokens. Some optimisation to make the sure the
 * content is only scanned by the PHP regex parser once. Lexer modes must not start
 * with leading underscores.
 */
class WikiText_Lexer 
{
    private $_regexes;
    private $_handler;
    private $_mode;
    private $_modeHandlers;
    private $_case;

    const ENTER = 1;
    const MATCHED =2;
    const UNMATCHED = 3;
    const LEXIT = 4;
    const SPECIAL = 5;  
    /**
     * Sets up the lexer in case insensitive matching by default.
     * @param WikiText_Handler $handler handling strategy.
     * @param string $start starting handler.
     * @param boolean $case true for case sensitive.
     */
    public function __construct(WikiText_Handler $handler,$start="accept",$case=false) 
    {
        $this->_case = $case;
        $this->_regexes = array();
        $this->_handler = $handler;
        $this->_mode = new WikiText_Lexer_StateStack($start);
        $this->_mode_handlers = array();
    }

    /**
     * Adds a token search pattern for a particular parsing mode. 
     * The pattern does not change the current mode.
     * @param string $pattern Perl style regex, but ( and ) lose the usual meaning.
     * @param string $mode Should only apply this pattern when dealing with
     *  this type of input.
     */
    public function addPattern($pattern, $mode = "accept") 
    {
        if (! isset($this->_regexes[$mode])) {
            $this->_regexes[$mode] = new WikiText_Lexer_ParallelRegex($this->_case);
        }

        $this->_regexes[$mode]->addPattern($pattern);
    }

    /**
     * Adds a pattern that will enter a new parsing mode. Useful for entering 
     * parenthesis,  strings, tags, etc.
     * @param string $pattern Perl style regex, but ( and ) lose the usual meaning.
     * @param string $mode should only apply this pattern when dealing with this 
     *  type of input.
     * @param string $new_mode Change parsing to this new nested mode.
     */
    public function addEntryPattern($pattern, $mode, $new_mode) 
    {
        if (! isset($this->_regexes[$mode])) {
            $this->_regexes[$mode] = new WikiText_Lexer_ParallelRegex($this->_case);
        }

        $this->_regexes[$mode]->addPattern($pattern, $new_mode);
    }

    /**
     * Adds a pattern that will exit the current mode and re-enter the previous one.
     * @param string $pattern Perl style regex, but ( and ) lose the usual meaning.
     * @param string $mode Mode to leave.
     */
    public function addExitPattern($pattern, $mode) 
    {
        if (! isset($this->_regexes[$mode])) {
            $this->_regexes[$mode] = new WikiText_Lexer_ParallelRegex($this->_case);
        }

        $this->_regexes[$mode]->addPattern($pattern, "__exit");
    }

    /**
     * Adds a pattern that has a special mode. Acts as an entry and exit pattern in one go, 
     * effectively calling a special parser handler for this token only.
     * 
     * @param string $pattern Perl style regex, but ( and ) lose the usual meaning.
     * @param string $mode Should only apply this pattern when dealing with
     *  this type of input.
     * @param string $special use this mode for this one token.
     */
    public function addSpecialPattern($pattern, $mode, $special) 
    {
        if (! isset($this->_regexes[$mode])) {
            $this->_regexes[$mode] = new WikiText_Lexer_ParallelRegex($this->_case);
        }

        $this->_regexes[$mode]->addPattern($pattern, "_$special");
    }

    /**
     * Adds a mapping from a mode to another handler.
     * @param string $mode Mode to be remapped.
     * @param string $handler New target handler.
     */
    public function mapHandler($mode, $handler) 
    {
        $this->_modeHandlers[$mode] = $handler;
    }

    /**
     * Splits the page text into tokens. Will fail if the handlers report an error 
     * or if no content is consumed. If successful then each unparsed and parsed 
     * token invokes a call to the held listener.
     *
     * @param string $raw Raw text with a dokuwiki markup.
     * @return boolean true on success, else false.
     */
    public function parse($raw) {
        if (!isset($this->_handler)) {
            return false;
        }

        $initialLength = strlen($raw);
        $length = $initialLength;
        $pos = 0;

        //main parsing loop
        while (is_array($parsed = $this->_reduce($raw))) {
            list($unmatched, $matched, $mode) = $parsed;
            $currentLength = strlen($raw);
            $matchPos = $initialLength - $currentLength - strlen($matched);
            if (! $this->_dispatchTokens($unmatched, $matched, $mode, $pos, $matchPos)) {
                return false;
            }

            if ($currentLength == $length) {
                return false;
            }

            $length = $currentLength;
            $pos = $initialLength - $currentLength;
        }

        if (!$parsed) {
            return false;
        }

        return $this->_invokeHandler($raw,self::UNMATCHED, $pos);
    }

    /**
     * Sends the matched token and any leading unmatched text to the handler changing 
     * the lexer to a new mode if one is listed.
     *  
     * @param string $unmatched Unmatched leading portion.
     * @param string $matched Actual token match.
     * @param string $mode Mode after match. A boolean false mode causes no change.
     * @param int $pos Current byte index location in raw doc thats being parsed
     * @return boolean false if there was any error from the parser.
     */ 
    private function _dispatchTokens($unmatched,$matched,$mode=false,$initialPos,$matchPos) 
    {
        //has umatched data handler int the current mode?
        if (! $this->_invokeHandler($unmatched, self::UNMATCHED, $initialPos) )
        {
            return false;
        }

        //matched end for the current mode, leave from stack
        if ($this->_isModeEnd($mode)) {
            if (!$this->_invokeHandler($matched, self::LEXIT, $matchPos)) {
                return false;
            }
            return $this->_mode->leave();
        }

        //special modes (with _) modes with same start and end pattern
        if ($this->_isSpecialMode($mode)) {
            //mode add to the stack
            $this->_mode->enter($this->_decodeSpecial($mode));
            //make instruction via handler
            if (! $this->_invokeHandler($matched, self::SPECIAL, $matchPos)) {
                return false;
            }
            //leave mode
            return $this->_mode->leave();
        }

        //normal mode
        if (is_string($mode)) {
            //add to stack
            $this->_mode->enter($mode);
            //call handler
            return $this->_invokeHandler($matched, self::ENTER, $matchPos);
        }

        return $this->_invokeHandler($matched, self::MATCHED, $matchPos);
    }

    /**
     * Tests to see if the new mode is actually to leave the current mode and pop 
     * an item from the matching mode stack.
     * @param string $mode Mode to test.
     * @return boolean True if this is the exit mode.
     */
    private function _isModeEnd($mode) 
    {
        return ($mode === "__exit");
    }

    /**
     * Test to see if the mode is one where this mode is entered for this token only 
     * and automatically leaves immediately afterwoods.
     * @param string $mode Mode to test.
     * @return boolean True if this is the exit mode.
     */
    private function _isSpecialMode($mode) 
    {
        return (strncmp($mode, "_", 1) == 0);
    }

    /**
     * Strips the magic underscore marking single token modes.
     * @param string $mode Mode to decode.
     * @return string Underlying mode name.
     * @access private
     */
    private function _decodeSpecial($mode) 
    {
        return substr($mode, 1);
    }

    /**
     * Calls the handler method named after the current mode. Empty content will be 
     * ignored. The handler has a method for each mode in the lexer.
     *
     * @param string $content text parsed.
     * @param boolean $is_match token is recognised rather than unparsed data.
     * @param int $pos Current byte index location in raw doc thats being parsed
     */
    private function _invokeHandler($content, $is_match, $pos) 
    {
        if (($content === "") || ($content === false)) {
            return true;
        }

        $handler = $this->_mode->getCurrent();
        if (isset($this->_modeHandlers[$handler])) {
            $handler = $this->_modeHandlers[$handler];
        }

 /* for debug
 echo "content: $content\n";
 echo "handler: $handler\n\n";
  */

        return $this->_handler->$handler($content, $is_match, $pos);
    }

    /**
     * Tries to match a chunk of text and if successful removes the recognised 
     * chunk and any leading unparsed data. Empty strings will not be matched.
     *
     * @param string $raw The subject to parse. This is the content that will be 
     * eaten.
     *  
     * @return mixed
     *  array of three items:
     *  - list of unparsed content 
     *  - recognised token
     *  - the action the parser is to take, this is label for the mathed regexp
     *  true if no match, false if there is a parsing error.
     */
    private function _reduce(&$raw) 
    {
        if (! isset($this->_regexes[$this->_mode->getCurrent()])) {
            return false;
        }

        if ($raw === "") {
            return true;
        }

        if ($action=$this->_regexes[$this->_mode->getCurrent()]->split($raw, $split)) {
            list($unparsed, $match, $raw) = $split;
            return array($unparsed, $match, $action);
        }

        return true;
    }

    /**
     * Escapes regex characters other than (, ) and /
     * @param string $str
     * @return string
     */
    public static function escape($str) 
    {
        //$str = addslashes($str);
        $chars = array(
            '/\\\\/',
            '/\./',
            '/\+/',
            '/\*/',
            '/\?/',
            '/\[/',
            '/\^/',
            '/\]/',
            '/\$/',
            '/\{/',
            '/\}/',
            '/\=/',
            '/\!/',
            '/\</',
            '/\>/',
            '/\|/',
            '/\:/'
        );

        $escaped = array(
            '\\\\\\\\',
            '\.',
            '\+',
            '\*',
            '\?',
            '\[',
            '\^',
            '\]',
            '\$',
            '\{',
            '\}',
            '\=',
            '\!',
            '\<',
            '\>',
            '\|',
            '\:'
        );
        return preg_replace($chars, $escaped, $str);
    }


}
