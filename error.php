<?php

class Conf{
    private $file;
    private $xml;
    private $lastmatch;
    
    function __construct($file) {
        $this->file = $file;
        if(!file_exists($file)){
            throw new FileException("file  '$file' does not exist");
        }
        $this->xml = simplexml_load_file($file, null, LIBXML_NOERROR);
        
        if(!is_object($this->xml)){
            throw new XmlException(libxml_get_last_error());
        }
        
        print gettype($this->xml);
        $matches = $this->xml->xpath("/conf");
        if(!count($matches)){
            throw new ConfException("could now find root element: conf");
        }
    }
    
    function write(){
        if(!is_writable($this->file)){
            throw new FileException("file '{$this->file}' is not writeable");
        }
        file_put_contents($this->file, $this->xml->asXML());
    }
    
    function get($str){
        $matches = $this->xml->xpath("/conf/item[@name=\"$str\"");
        if(count($matches)){
            $this->lastmatch = $matches[0];
            return (string)$matches;
        }
        return null;
    }
    
    function set($key, $value){
        if(!is_null($this->get($key))){
            $this->lastmatch[0] = $value;
            return;
        }
        
        $conf = $this->xml->conf;
        $this->xml->addChild('item', $value)->addAttribute('name', $key);
    }
}

class XmlException extends Exception{
    private $error;
    
    public function __construct(LibXMLError $error) {
        $shortfile = basename($error->file);
        $msg = "[{$shortfile}, line {$error->line}, col {$error->column}] {$error->message}";
        $this->error = $error;
        parent::__construct($msg, $error->code);
    }
    
    public function getLibXmlError(){
        return $this->error;
    }
}

class FileException extends Exception{}
class ConfException extends Exception{}

class Runner {
    public static function init() {
        try {
            $fh = fopen(__DIR__ . DIRECTORY_SEPARATOR ."log.txt", "a");
            fputs($fh, "start\n");
            $conf = new Conf(__DIR__ . DIRECTORY_SEPARATOR . "conf01.xml");
            print "user: " . $conf->get('user') . "\n";
            print "host: " . $conf->get('host') . "\n";
            $conf->set("pass", "newpass");
            $conf->write();
        } catch (FileException $e) {
            fputs($fh, "file exception\n");
            print_r($e->getMessage());
        } catch (XmlException $e){
            fputs($fh, "xml exception\n");
            print_r($e->getMessage());
        } catch (ConfException $e){
            fputs($fh, "conf exception\n");
            print_r($e->getMessage());
        } catch (Exception $e){
            fputs($fh, "general exception\n");
            print_r($e->getMessage());
        }  finally {
            fputs($fh, "end\n");
            fclose($fh);
        }
    }

}