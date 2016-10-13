<?php
/**
 * This class and all the subclasses below are used to reduce the effort
 * required to register modes with the Lexer. For performance these  could all
 * be eliminated later perhaps, or the Parser could be serialized to a file once
 * all modes are registered.
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
class WikiText_Parser_Mode 
{
    public $Lexer;

    protected $_allowedModes = array();

    // Called before any calls to connectTo
    function preConnect() {}

    // Connects the mode
    function connectTo($mode) {}

    // Called after all calls to connectTo
    function postConnect() {}

    function accepts($mode) {
        return in_array($mode, $this->_allowedModes);
    }
}
