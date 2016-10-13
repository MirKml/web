<?php
class WikiText_Utils
{
    private static $_confDir;
    private static $_mimeTypes=array();
    private static $_smileys=array();
    private static $_acronyms=array();
    private static $_entities=array();

    public static function getConfDir() 
    {
        if (!isset(self::$_confDir)) self::$_confDir=realpath(dirname(__FILE__))."/conf/"; 
        return self::$_confDir;
    }

    public static function getMimeTypes() 
    {
        if (empty(self::$_mimeTypes)) { 
            self::$_mimeTypes=self::confToHash(self::getConfDir().'mime.conf');
        }  
        return self::$_mimeTypes;
    }

    public static function getSmileys()
    {
        if (empty(self::$_smileys)) {
            self::$_smileys=self::confToHash(self::getConfDir().'smileys.conf');
        }
        return self::$_smileys;
    }

    public static function getAcronyms()
    {
        if (empty(self::$_acronyms)) {
            self::$_acronyms=self::confToHash(self::getConfDir().'acronyms.conf');
        }
        return self::$_acronyms;
    }

    public static function getEntities()
    {
        if (empty(self::$_entities)) {
            self::$_entities=self::confToHash(self::getConfDir().'entities.conf');
        }
        return self::$_entities;
    }

    /**
     * Builds a hash from a configfile
     *
     * If $lower is set to true all hash keys are converted to
     * lower case.
     *
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public static function confToHash($file,$lower=false) 
    {
        $conf = array();
        $lines = @file( $file );
        if ( !$lines ) return $conf;

        foreach ( $lines as $line ) {
            //ignore comments
            $line = preg_replace('/(?<!&)#.*$/','',$line);
            $line = trim($line);
            if(empty($line)) continue;
            $line = preg_split('/\s+/',$line,2);
            // Build the associative array
            if($lower){
                $conf[strtolower($line[0])] = $line[1];
            } else {
                $conf[$line[0]] = $line[1];
            }
        }

        return $conf;
    }

    public static function mimeType($file)
    {
        $ret    = array(false,false); // return array
        $mtypes = self::getMimeTypes();     // known mimetypes
        $exts   = join('|',array_keys($mtypes));  // known extensions (regexp)
        if(preg_match('#\.('.$exts.')$#i',$file,$matches)){
            $ext = strtolower($matches[1]);
        }

        if($ext && $mtypes[$ext]){
            $ret = array($ext, $mtypes[$ext]);
        }

        return $ret;
    }

    public static function removeEntities($text)
    {
        foreach (array_values(WikiText_Utils::getEntities())
            as $entity) {
            $text = str_ireplace($entity,"",$text);
        }
        return $text;   
    }
}
