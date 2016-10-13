<?php
/**
 * Handler for the footnotes implementation.
 *
 * @package Wikitext
 * @author Miroslav Kubelik (koubel@volny.cz)
 * @license http://opensource.org/licenses/gpl-license.php GNU GPL
 */

/**
 * Handler for the footnotes implementation
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
class WikiText_Handler_Footnote extends WikiText_Handler_ReWritter
{
    public function finalise()
    {
        $last_call = end($this->_calls);
        $this->writeCall(array('footnote_close',array(),$last_call[2]));

        $this->process();
        $this->_callWriter->finalise();
    }

    public function process()
    {
        $first_call = reset($this->_calls);
        $this->_callWriter->writeCall(array("nest", array($this->_calls), $first_call[2]));
    }

}
