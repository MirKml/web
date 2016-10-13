<?php
class WikiText_Handler_CallWriter implements WikiText_Handler_IWriteCalls
{
    private $_calls;

    public function __construct() 
    {
        $this->clear();
    }

    public function clear() 
    {
        $this->_calls = array();
    }

    public function getCalls() 
    {
        return $this->_calls;
    }

    public function writeCall($call) 
    {
        $this->_calls[] = $call;
    }

    public function writeOnTop($call) 
    {
        array_unshift($this->_calls,$call);
    }

    public function writeCalls($calls) 
    {
        $this->_calls = array_merge($this->_calls, $calls);
    }

    /**
     * Gets the last instruction call from the call array.
     * Also sets the internal array of calls pointer into the end of array.
     * @return array 
     */
    public function getLastCall() 
    {
        return end($this->_calls);
    }

    /**
     * Processes all blocks (set of instructions delimited by eol instruction). 
     * Rewrites calls into the p_open/p_close instruction blocks.
     */
    public function rewriteBlocks() 
    {
        $block = new WikiText_Handler_BlockReWriter();
        $this->_calls = $block->process($this->_calls);
    }

    /**
     * Finalises, empty.
     */ 
    public function finalise() 
    {
    }

}
