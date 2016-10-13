<?php

if (!defined('rfc2822_atext')) define('rfc2822_atext',"0-9A-Za-z!#$%&'*+/=?^_`{|}~-");
if (!defined('preg_pattern_valid_email')) define('preg_pattern_valid_email', '['.rfc2822_atext.']+(?:\.['.rfc2822_atext.']+)*@(?:[0-9A-Za-z][0-9A-Za-z-]*\.)+[A-Za-z]{2,4}');

//-------------------------------------------------------------------
class WikiText_Parser_Mode_EmailLink extends WikiText_Parser_Mode
{
    // patterns for use in email detection and validation
    // NOTE: there is an unquoted '/' in RFC2822_ATEXT, it must remain unquoted to be used in the parser
    //       the pattern uses non-capturing groups as captured groups aren't allowed in the parser
    //       select pattern delimiters with care!
    const PREG_PATTERN_VALID_EMAIL = preg_pattern_valid_email;

    function connectTo($mode) {
        // pattern below is defined in inc/mail.php
        $this->Lexer->addSpecialPattern('<'.self::PREG_PATTERN_VALID_EMAIL.'>',$mode,'emaillink');
    }

}
