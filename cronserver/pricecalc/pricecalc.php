<?php

class pricecalc
{
    
    protected $curlConnect;
    protected $thisDirectory;
    
    public function __construct()
    {
        
        $this->curlConnect = curl_init();
        
        // actuell directory
        $this->thisDirectory = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        
    }
    
    public function get_page($url, $login = false, $userid = "", $password = ""){
    
        //$backdata = array();
        
        
        //global $proxy;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
        //curl_setopt($ch, CURLOPT_PROXY, $proxy);
        //curl_setopt($ch, CURLOPT_PROXYPORT, $proxyport);
    
        curl_setopt($ch, CURLOPT_HEADER, 0); // return headers 0 no 1 yes
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return page 1:yes
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); // http request timeout 20 seconds
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects, need this if the url changes
        curl_setopt($ch, CURLOPT_MAXREDIRS, 20); //if http server gives redirection responce
        //curl_setopt($ch, CURLOPT_USERAGENT,	"Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)");
        curl_setopt($ch, CURLOPT_USERAGENT,	"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7");
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt"); // cookies storage / here the changes have been made
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https
        curl_setopt($ch, CURLOPT_ENCODING, "gzip"); // the page encoding
    
        if( $login == true ){
            $postinfo = "userid=".$userid."&password=".$password;
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postinfo);
        }
        
        $data = curl_exec($ch); // execute the http request
        
        $lieferanten = array();
        
        if( $login == false ){
            
            $dom = new simple_html_dom();
            // Load HTML from a string
            $data2 = $dom->load($data);
            
            $x = 0;
            
            foreach($data2->find('.table-retailer-row') as $t){
                //$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pricelist1.txt';
                //file_put_contents($file, $t."\n\n\n\n\n\n", FILE_APPEND);
                
                $ausgabe = array();
                $saveData = false;
                foreach($t->find('input') as $t2){
                    //$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pricelist2.txt';
                    $wid = $t2->name;
                    //file_put_contents($file, $wid . ' - ' . $t2->value ."\n", FILE_APPEND);
                    
                    if( $t2->name == "wid"){
                        $lieferanten[$x]['wid'] = $t2->value;
                        $ausgabe['wid'] = $t2->value;
                        $saveData = true;
                    }
                    if( $t2->name == "itemId"){
                        $lieferanten[$x]['itemId'] = $t2->value;
                        $ausgabe['itemId'] = $t2->value;
                        $saveData = true;
                    }
                    if( $t2->name == "name"){
                        $lieferanten[$x]['name'] = $t2->value;
                        $ausgabe['name'] = $t2->value;
                        $saveData = true;
                    }
                    
                    
                }
                
                //$price = $t->find('div.price-row',0)->innertext;
                //$ausgabe['price'] = $price;
                //$price = $t->find('.price-row div');
                
                
                foreach($t->find('td.dealer-stock-block') as $k){
                    
                    $spliter = explode(";", $k->innertext);
                    if( count ($spliter) > 1 ){
                        $var = count ($spliter) - 1;
                        $ausgabe['stock'] = trim($spliter[$var]);
                    }else{
                        $ausgabe['stock'] = trim($k->innertext);
                    }
                    
                    $saveData = true;
                }
                
                
                foreach($t->find('.language-flag') as $ts){
                
                    $ausgabe['language'] = $ts->class;
                }
                
                foreach($t->find('div.price-row div') as $e){
                    $split = explode(" ", $e->innertext);
                    $ausgabe['price'][] = trim($split[0]);
                    $saveData = true;
                }
                
                if(  $saveData == true ){
                    //$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pricelist22.txt';
                    //$save = print_r($ausgabe, true);
                    //file_put_contents($file, $save ."\n", FILE_APPEND);
                    
                    $backdata['pricescanner'][] = $ausgabe;
                }
                
                $x++;
            }
            
            
            
        }else{
            
        }
        
       
        $info = curl_getinfo($ch);
    
        $backdata['info'] = $info;
        $backdata['data'] = $data;
        
        //s$backdata['xml'] = $data2;
        
        
        curl_close($ch); // close the connection
        return $backdata;
    }
    
    public function curlClose(){
        
        curl_close( $this->curlConnect );
        
    }
    
}

