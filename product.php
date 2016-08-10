<?php
interface Chargeable{
    public function getPrice();
}

interface IndentityObject{
    public function generateId();
}

trait PriceUtilities{
    private static $taxrate = 17;
    
    public function calculateTax($price){
        return $this->getTaxRate() / 100 * $price;
    }
    
    abstract public function getTaxRate();
}

trait IdentityTrait{
    public function generateId(){
        return uniqid();
    }
}

class ShopProduct implements Chargeable, IndentityObject{
    use PriceUtilities, IdentityTrait;
    
    private $id = 0;
    private $title;
    private $producerMainName;
    private $producerFirstName;
    protected $price = 0;
    private $discount = 0;

    public function __construct($title, $firstName, $mainName, $price) {
        $this->title = $title;
        $this->producerFirstName = $firstName;
        $this->producerMainName = $mainName;
        $this->price = $price;
    }
    
    public function getTitle() {
        return $this->title;
    }

    public function getProducerMainName() {
        return $this->producerMainName;
    }

    public function getProducerFirstName() {
        return $this->producerFirstName;
    }

    public function getPrice() {
        return ( $this->price - $this->discount );
    }

    public function getDiscount() {
        return $this->discount;
    }
    
    public function getTaxRate() {
        return 17;
    }
    
    public function setID($id){
        $this->id = $id;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setProducerMainName($producerMainName) {
        $this->producerMainName = $producerMainName;
    }

    public function setProducerFirstName($producerFirstName) {
        $this->producerFirstName = $producerFirstName;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

    public function setDiscount($discount) {
        $this->discount = $discount;
    }

    public function getProducer() {
        return "{$this->producerFirstName} {$this->producerMainName}";
    }

    public function getSummaryLine(){
        $base = "$this->title ({$this->producerMainName}, ";
        $base .= "{$this->producerFirstName})";
        return $base;
    }
    
    public static function getInstance( $id, PDO $pdo){
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $result = $stmt->execute(array( $id ));
        $row = $stmt->fetch();
        
        if( empty($row)){
            return null;
        }
        
        if ( $row['type'] == 'book' ){
            $product = new BookProduct($row['title'], $row['firstname'],
                    $row['mainname'], $row['price'], $row['numpages']);
        }elseif( $row['type'] == 'cd' ){
            $product = new CdProduct($row['title'], $row['firstname'],
                    $row['mainname'], $row['price'], $row['playlength']);
        }
        else{
            $product = new ShopProduct($row['title'], $row['firstname'],
                    $row['mainname'], $row['price']);
        }
        
        $product->setID( $row['id'] );
        $product->setDiscount( $row['discount'] );
        return $product;
    }
}

class CdProduct extends ShopProduct{
    private $playLength;
    
    public function __construct($title, $firstName, $mainName, $price, $playLength) {
        parent::__construct($title, $firstName, $mainName, $price);
        
        $this->playLength = $playLength;
    }
            
    public function getPlayLength(){
        return $this->playLength;
    }
    
    public function getSummaryLine() {
        $base = parent::getSummaryLine();
        $base .= ": playing time - {$this->playLength}";
        return $base;
    }
}

class BookProduct extends ShopProduct{
    private $numPages;
    
    public function __construct($title, $firstName, $mainName, $price, $numPages) {
        parent::__construct($title, $firstName, $mainName, $price);
        
        $this->numPages = $numPages;
    }
            
    public function getNumberOfPages(){
        return $this->numPages;
    }
    
    public function getSummaryLine() {
        $base = parent::getSummaryLine();
        $base .= ": page count - {$this->numPages}";
        return $base;
    }
    
    public function getPrice() {
        return $this->price;
    }
}

class Product{
    public $name;
    public $price;
    
    public function __construct($name, $price) {
        $this->name = $name;
        $this->price = $price;
    }
}

class ProcessSale{
    private $callbacks;
    
    public function registerCallback($callback){
        if(!is_callable($callback)){
            throw new Exception("callback not callable");
        }
        
        $this->callbacks[] = $callback;
    }
    
    public function sale(Product $product){
        print "{$product->name}: processing \n";
        foreach($this->callbacks as $callback){
            call_user_func($callback, $product);
        }
    }
}

class Mailer{
    public function doMail(Product $product){
        print "mailing ({$product->name})\n";
    }
}

class Totalizer{
    public static function warnAmount($amt){
        $count = 0;
        return function (Product $product) use ($amt, &$count){
            $count += $product->price;
            print "count: $count\n";
            if($count > $amt){
                print "reached high price: {$count}\n";
            }
        };
    }
}

$logger = function(Product $product){
    print "logging ({$product->name})\n";
};
$mailer = array(new Mailer(), "doMail");

$processor = new ProcessSale();
$processor->registerCallback(Totalizer::warnAmount(6));
//$processor->sale(new Product("shoes", 6));
//$processor->sale(new Product("coffee", 6));