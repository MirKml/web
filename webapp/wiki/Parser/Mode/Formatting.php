<?php
//-------------------------------------------------------------------
class WikiText_Parser_Mode_Formatting extends WikiText_Parser_Mode {
    var $type;

    var $formatting = array (
        'strong' => array (
            'entry'=>'\*\*(?=.*\*\*)',
            'exit'=>'\*\*',
            'sort'=>70
        ),

        'emphasis'=> array (
            'entry'=>'//(?=[^\x00]*[^:]//)', //hack for bug #384
            'exit'=>'//',
            'sort'=>80
        ),

        'underline'=> array (
            'entry'=>'__(?=.*__)',
            'exit'=>'__',
            'sort'=>90
        ),

        'monospace'=> array (
            'entry'=>'\x27\x27(?=.*\x27\x27)',
            'exit'=>'\x27\x27',
            'sort'=>100
        ),

        'subscript'=> array (
            'entry'=>'<sub>(?=.*</sub>)',
            'exit'=>'</sub>',
            'sort'=>110
        ),

        'superscript'=> array (
            'entry'=>'<sup>(?=.*</sup>)',
            'exit'=>'</sup>',
            'sort'=>120
        ),

        'deleted'=> array (
            'entry'=>'<del>(?=.*</del>)',
            'exit'=>'</del>',
            'sort'=>130
        ),
    );

    public function __construct($type) {
        $allModes = WikiText_Parser::getParserModes(); 

        if ( !array_key_exists($type, $this->formatting) ) {
            trigger_error('Invalid formatting type '.$type, E_USER_WARNING);
        }

        $this->type = $type;

        // formatting may contain other formatting but not it self
        $modes = $allModes['formatting'];
        $key = array_search($type, $modes);
        if ( is_int($key) ) {
            unset($modes[$key]);
        }

        $this->_allowedModes = array_merge (
            $modes,
            $allModes['substition'],
            $allModes['disabled']
        );
    }

    function connectTo($mode) {

        // Can't nest formatting in itself
        if ( $mode == $this->type ) {
            return;
        }

        $this->Lexer->addEntryPattern(
            $this->formatting[$this->type]['entry'],
            $mode,
            $this->type
        );
    }

    function postConnect() {

        $this->Lexer->addExitPattern(
            $this->formatting[$this->type]['exit'],
            $this->type
        );

    }

}
