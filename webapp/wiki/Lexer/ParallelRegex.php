<?php
/**
 * Compounded regular expression. Any of the contained patterns could match and
 * when one does it's label is returned.
 */
class WikiText_Lexer_ParallelRegex 
{
    private $_patterns;
    private $_labels;
    private $_regex;
    private $_case;

    /**
     * Constructor. Starts with no patterns.
     * @param bool $case True for case sensitive, false for insensitive.
     */
    public function __construct($case) 
    {
        $this->_case = $case;
        $this->_patterns = array();
        $this->_labels = array();
        $this->_regex = null;
    }

    /**
     * Adds a pattern with an optional label.
     * 
     * @param mixed $pattern Perl style regex. Must be UTF-8 encoded. If its a string, the (, )
     *  lose their meaning unless they form part of a lookahead or lookbehind 
     *  assertation.
     * @param string $label Label of regex to be returned on a match. Label must 
     *  be ASCII
     */
    public function addPattern($pattern, $label = true) 
    {
        $count = count($this->_patterns);
        $this->_patterns[$count] = $pattern;
        $this->_labels[$count] = $label;
        $this->_regex = null;
    }

    /** Attempts to match all patterns at once against a string.
     * @param string $subject String to match against.
     * @param string $match First matched portion of subject.
     * @return bool True on success.
     */
    public function match($subject, &$match) 
    {
        if (count($this->_patterns) == 0) {
            return false;
        }
        if (! preg_match($this->_getCompoundedRegex(), $subject, $matches)) {
            $match = "";
            return false;
        }

        $match = $matches[0];
        $size = count($matches);
        for ($i = 1; $i < $size; $i++) {
            if ($matches[$i] && isset($this->_labels[$i - 1])) {
                return $this->_labels[$i - 1];
            }
        }

        return true;
    }

    /** Attempts to split the string against all patterns at once
     * @param string $subject String to match against.
     * @param array $split The split result: array containing, pre-match, 
     *  match & post-match strings
     * @return boolean True on success.
     * @author Christopher Smith <chris@jalakai.co.uk>
     */
    public function split($subject, &$split) 
    {
        if (count($this->_patterns) == 0) {
            return false;
        }

        if (! preg_match($this->_getCompoundedRegex(), $subject, $matches)) {
            $split = array($subject, "", "");
            return false;
        }

        $idx = count($matches)-2;

        list($pre, $post) = preg_split($this->_patterns[$idx].$this->_getPerlMatchingFlags(), $subject, 2);
        $split = array($pre, $matches[0], $post);
        return isset($this->_labels[$idx]) ? $this->_labels[$idx] : true;
    }

    /**
     * Compounds the patterns into a single regular expression separated with the
     * "or" operator. 
     * 
     * Caches the regex.  Will automatically escape (, ) and / tokens.
     * @param array $patterns List of patterns in order.
     */  
    public function _getCompoundedRegex() 
    {
        if ($this->_regex == null) {
            $cnt = count($this->_patterns);
            for ($i = 0; $i < $cnt; $i++) {
                // Replace lookaheads / lookbehinds with marker
                $m = "\1\1";
                $pattern = preg_replace(
                    array (
                        '/\(\?(i|m|s|x|U)\)/U',
                        '/\(\?(\-[i|m|s|x|U])\)/U',
                        '/\(\?\=(.*)\)/sU',
                        '/\(\?\!(.*)\)/sU',
                        '/\(\?\<\=(.*)\)/sU',
                        '/\(\?\<\!(.*)\)/sU',
                        '/\(\?\:(.*)\)/sU',
                    ),
                    array (
                        $m.'SO:\\1'.$m,
                        $m.'SOR:\\1'.$m,
                        $m.'LA:IS:\\1'.$m,
                        $m.'LA:NOT:\\1'.$m,
                        $m.'LB:IS:\\1'.$m,
                        $m.'LB:NOT:\\1'.$m,
                        $m.'GRP:\\1'.$m,
                    ),
                    $this->_patterns[$i]
                );
                // Quote the rest
                $pattern = str_replace(
                    array('/', '(', ')'),
                    array('\/', '\(', '\)'),
                    $pattern
                );

                // Restore lookaheads / lookbehinds
                $pattern = preg_replace(
                    array (
                        '/'.$m.'SO:(.{1})'.$m.'/',
                        '/'.$m.'SOR:(.{2})'.$m.'/',
                        '/'.$m.'LA:IS:(.*)'.$m.'/sU',
                        '/'.$m.'LA:NOT:(.*)'.$m.'/sU',
                        '/'.$m.'LB:IS:(.*)'.$m.'/sU',
                        '/'.$m.'LB:NOT:(.*)'.$m.'/sU',
                        '/'.$m.'GRP:(.*)'.$m.'/sU',
                    ),
                    array (
                        '(?\\1)',
                        '(?\\1)',
                        '(?=\\1)',
                        '(?!\\1)',
                        '(?<=\\1)',
                        '(?<!\\1)',
                        '(?:\\1)',
                    ),
                    $pattern
                );
                $this->_patterns[$i] = '('.$pattern.')';
            }
            $this->_regex = "/" . implode("|", $this->_patterns) . "/" . $this->_getPerlMatchingFlags();
        }//endfor
        return $this->_regex;
    }

    /**
     * Accessor for perl regex mode flags to use.
     * @return string Perl regex flags.
     */
    private function _getPerlMatchingFlags() 
    {
        return ($this->_case ? "msS" : "msSi");
    }

}

