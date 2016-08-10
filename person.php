<?php

class OldPerson {
    private $_name;
    private $_age;
    private $writer;
    
    public function __construct(PersonWriter $writer) {
        $this->writer = $writer;
    }
    
    public function __call($methodname, $arguments) {
        if(method_exists($this->writer, $methodname)){
            return $this->writer->$methodname($this);
        }
    }

    public function __get($property) {
        $method = "get{$property}";
        if(method_exists($this, $method)){
            return $this->$method();
        }
    }
    
    public function __isset($property){
        $method = "get{$property}";
        return method_exists($this, $method);
    }

    public function getName(){
        return $this->_name;
    }
    
    public function getAge(){
        return $this->_age;
    }
    
    public function __set($property, $value) {
        $method = "set{$property}";
        if(method_exists($this, $method)){
            return $this->$method($value);
        }
    }
    
    public function __unset($property){
        $method = "set{$property}";
        if(method_exists($this, $method)){
            return $this->$method(null);
        }
    }
    
    public function setName($name){
        $this->_name = $name;
        if(!is_null($name)){
            $this->_name = strtoupper($this->_name);
        }
    }
    
    public function setAge($age){
        $this->_age = strtoupper($age);
    }
    
    public function __destruct() {
        print "saving person\n";
    }
}

class PersonWriter{
    public function writeName(Person $p){
        print $p->getName() . "\n";
    }
    
    public function writeAge(Person $p){
        print $p->getAge() . "\n";
    }
}

class Address{
    private $number;
    private $street;
    
    public function __construct($maybenumber, $maybestreet = null) {
        if(is_null($maybestreet)){
            $this->streetaddress = $maybenumber;
        }else{
            $this->number = $maybenumber;
            $this->street = $maybestreet;
        }
    }
    
    public function __set($property, $value) {
        if($property == 'streetaddress'){
            if(preg_match("/^(\d+.*?)[\s,]+(.+)$/", $value, $matches)){
                $this->number = $matches[1];
                $this->street = $matches[2];
            }else{
                throw new Exception("unable to parse street address: '{$value}'");
            }
        }
    }
    
    public function __get($property){
        if($property === 'streetaddress'){
            return $this->number . " " . $this->street;
        }
    }
}

class Person{
    private $name;
    private $age;
    private $id;
    public $account;


    public function __construct($name, $age, Account $account) {
        $this->name = $name;
        $this->age = $age;
        $this->account = $account;
    }
    
    public function setId($id){
        $this->id = $id;
    }
    
    public function __clone() {
        $this->id = 0;
        $this->account = clone $this->account;
    }
    
    public function __toString() {
        return "person";
    }
}

class Account{
    public $balance;
    public function __construct($balance) {
        $this->balance = $balance;
    }
}

$person = new Person("bob", 44, new Account(200));
$person->setId(343);
$person2 = clone $person;
$person->account->balance += 10;