<?php
require_once 'product.php';

function getProduct() {
    return new CdProduct("Exile on Coldharbour Lane", "The", "Alabama 3", 10.99, 60.33);
}

$prod_class = new ReflectionClass('CdProduct');

function classData(ReflectionClass $class){
    $details = "";
    $name = $class->getName();
    
    if($class->isUserDefined()){
        $details .= "$name is user defined\n";
    }
    
    return $details;
}

class ReflectionUtil{
    static function getClassSource(ReflectionClass $class){
        $path = $class->getFileName();
        $lines = @file($path);
        $from = $class->getStartLine();
        $to = $class->getEndLine();
        $len = $to - $from + 1;
        return implode(\array_slice($lines, $from - 1, $len));
    }
}

class Person{
    public $name;
    
    public function __construct($name) {
        $this->name = $name;
    }
}

interface Module{
    public function execute();
}

class FtpModule implements Module{
    public function setHost($host){
        print "FtpModule::setHost(): $host\n";
    }
    
    public function setUser($user){
        print "FtpModule::setUser(): $user\n";
    }
    
    public function execute() {
        //do things
    }

}

class PersonModule implements Module{
    public function setPerson(Person $person){
        print "PersonModule::setPerson(): $person->name\n";
    }
    
    public function execute() {
        //do things
    }

}

class ModuleRunner{
    private $configData = ["PersonModule" => ['person' => 'bob'],
        'FtpModule' => ['host' => 'example.com', 'user' => 'anon']
    ];

    private $modules = [];
    
    public function init(){
        $interface = new ReflectionClass('Module');
        
        foreach($this->configData as $moduleName => $params){
            $module_class = new ReflectionClass($moduleName);
            
            if(!$module_class->isSubclassOf($interface)){
                throw new Exception("unknown module type: $modulename");
            }
            
            $module = $module_class->newInstance();
            
            foreach($module_class->getMethods() as $method){
                $this->handleMethod($module, $method, $params);
            }
            
            array_push($this->modules, $module);
        }
    }
    
    public function handleMethod(Module $module, ReflectionMethod $method, $params) {
        $name = $method->getName();
        $args = $method->getParameters();

        if (count($args) != 1 || substr($name, 0, 3) != "set") {
            return false;
        }
        
        $property = strtolower(substr($name, 3));
        
        if(!isset($params[$property])){
            return false;
        }
        
        $arg_class = $args[0]->getClass();
        
        if(empty($arg_class)){
            $method->invoke($module, $params[$property]);
        }else{
            $method->invoke($module, $arg_class->newInstance($params[$property]));
        }
    }

}

$test = new ModuleRunner();
$test->init();