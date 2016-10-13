<?php
/**
 * States for a stack machine.
 */
class WikiText_Lexer_StateStack 
{
    private $_stack;

    /** 
     * Constructor. Starts in named state.
     * @param string $start Starting state name.
     */
    public function __construct($start) 
    {
        $this->_stack = array($start);
    }

    /**
     * Accessor for current state.
     * @return string
     */ 
    public function getCurrent() 
    {
        return $this->_stack[count($this->_stack) - 1];
    }

    /**
     * Adds a state to the stack and sets it to be the current state.
     * @param string $state New state.
     */
    public function enter($state) 
    {
        array_push($this->_stack, $state);
    }

    /**
     * Leaves the current state and reverts to the previous one.
     * @return boolean False if we drop off the bottom of the list.
     */
    public function leave() 
    {
        if (count($this->_stack) == 1) {
            return false;
        }
        array_pop($this->_stack);

        return true;
    }

}
