<?php
interface WikiText_Handler_IWriteCalls
{
    /** Writes one call into the call array
     */
    public function writeCall($call);

    /** Writes more calls call into the call array
     */
    public function writeCalls($calls);

    /** finalizes the writer process
     */
    public function finalise();

    #END INTERFACE
}
