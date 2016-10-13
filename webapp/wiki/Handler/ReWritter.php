<?php
class WikiText_Handler_ReWritter implements WikiText_Handler_IWriteCalls
{
    protected $_callWriter;

    /** array for the calls, which are related to footnotes
     * @var array
     */
    protected $_calls = array();

    public function __construct(WikiText_Handler_IWriteCalls $callWriter)
    {
        $this->_callWriter = $callWriter;
    }

    public function getCallWriter()
    {
        return $this->_callWriter;
    }

    public function writeCall($call)
    {
        $this->_calls[] = $call;
    }

    public function writeCalls($calls)
    {
        $this->_calls = array_merge($this->_calls, $calls);
    }

    public function finalise()
    {
    }

}
