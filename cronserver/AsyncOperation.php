<?php

date_default_timezone_set('Europe/Berlin');

// Get Thread Class
include_once getcwd() . '/httpdocs/cronserver/pricecalc/pricecalc.php';
include_once getcwd() . '/httpdocs/cronserver/htmldom/simple_html_dom.php';

class AsyncOperation extends Thread
{
    
        
    //protected $threadId;
    //protected $scanner;
    
   
    
    public function __construct($threadId, $scannerValue)
    {
        
        $this->threadId = $threadId;
        
        $this->scanner = $scannerValue;
        
    }
    
   
    
    public function run()
    {
        
        $dbParams = array(
            'database'  => 'reifen_mueller_tyre24',
            'username'  => 'tire_manager',
            'password'  => 'Fkrr91!3',
            'hostname'  => 'localhost',
        );
        
        
        
        // Scanner date
        $mysql_date_now = date("Y-m-d H:i:s");
        
        $dom = new simple_html_dom();
        
        $crawler = new pricecalc();
        
       
        
        $scan = array();
        
        $scan = $this->scanner;
        
        // Scanner date
        $scan['ScanStart'] = date("Y-m-d H:i:s");
        
        $back = $crawler->get_page("https://www.tyre24.com/de/de/user/login/page/", true, $scan['accountid'], $scan['passwd']);
        
        $getArticle = $scan['articles'];
        
        // $scanner[$i]['uniqid']
        $uniqid = $scan['uniqid'];
        //$uniqid = uniqid();
        
        $lieferantPrices = array();
        
        $finished = 1;
        $allValues = count($getArticle);
        
        $startTime = time();
        
        for( $x=0; $x<count($getArticle);$x++ ){
        //for( $x=0; $x<20;$x++ ){
            
            $char = $getArticle[$x]['tyre_artikelnummer'];
            
            if( $char[0] == "A" || $char[0] == "a" ){
                $idTyre = $getArticle[$x]['tyre_artikelnummer'];
            }else{
                $idTyre = 'T' . $getArticle[$x]['tyre_artikelnummer'];
            }
            
            
            $back = $crawler->get_page("https://www.tyre24.com/de/de/item/details/id/".$idTyre."//alcar//carManufacturer//carModel//carType/");
            
            //$back['pricescanner']['rm_artikelnummer'] = $getArticle[$x]['rm_artikelnummer'];
            //$back['pricescanner']['tyre_artikelnummer'] = $getArticle[$x]['tyre_artikelnummer'];
            
            $lieferantPrices[$x] = $back['pricescanner'];
            
            // LOG
            $filepath = getcwd() . "/httpdocs/data/log/tyre_status/" . $uniqid . '.log';
            $valueWrite = $finished . ';' . $allValues . ';' . $startTime . ';' . time();
            file_put_contents($filepath, $valueWrite);
            $finished++;
            
            $pause = mt_rand($scan['article_pause_min'],$scan['article_pause_max']);
            sleep($pause);
            
        }
        
        
        $dateTime = new \DateTime();
        $dateTime->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $dateTime->setTimestamp(time());
        $mysql_date_finished = $dateTime->format('Y-m-d H:i:s');
        
        //$mysql_date_finished = date("Y-m-d H:i:s");
        
        // Save Data
        $pdo = new PDO('mysql:host=localhost;dbname='.$dbParams['database'], $dbParams['username'], $dbParams['password']);
        
        for( $y=0; $y<count($lieferantPrices);$y++ ){
            
            $sql = "UPDATE `artikel` SET `in_scan`='false', `set_uniqid` = NULL, `last_scan` = ? WHERE `id`=?";
            $statement = $pdo->prepare($sql);
            $statement->execute(array($mysql_date_finished, $getArticle[$y]['id']));
            
            for( $x=0; $x<count($lieferantPrices[$y]);$x++ ){
                
                // Save Lieferanten
                try {
                    
                    $wid = $lieferantPrices[$y][$x]['wid'];
                    $name = $lieferantPrices[$y][$x]['name'];
                    $language = $lieferantPrices[$y][$x]['language'];
                    
                    $sql = "INSERT INTO `lieferanten` (`wid`, `name`, `language`) VALUES(?,?,?) ON DUPLICATE KEY UPDATE `insertupdate` = NOW(), `language` = ?";
                    //mail('damir.enseleit@selfphp.de', '$$sql', $sql);
                    $statement = $pdo->prepare($sql);
                    $statement->execute(array($wid, $name, $language, $language));
                    
                   
                    //return true;
                } catch (\Exception $e) {
                    //return false;
                    mail('damir.enseleit@selfphp.de', 'Reifen Mueller Error Lieferanten Save ', $e->getMessage());
                }
                
                // Save Price
                if( $lieferantPrices[$y][$x]['price'][0] > 0 ){
                    
                    $rm_artikelnummer = $getArticle[$y]['rm_artikelnummer'];
                    $tyre_artikelnummer = $getArticle[$y]['tyre_artikelnummer'];
                    $wid = $lieferantPrices[$y][$x]['wid'];
                    $price = $lieferantPrices[$y][$x]['price'][0];
                    $last_scan = $mysql_date_finished;
                    $stock = $lieferantPrices[$y][$x]['stock'];
                    
                    try {
                        $sql = "INSERT INTO `artikel_tyre_price` (`rm_artikelnummer`, `tyre_artikelnummer`, `wid`, `price`, `uniqid`, `last_scan`, `stock`) VALUES(?,?,?,?,?,?,?)";
                        $statement = $pdo->prepare($sql);
                        $statement->execute(array($rm_artikelnummer, $tyre_artikelnummer, $wid, $price, $uniqid, $last_scan, $stock));
                                            
                        
                    } catch (\Exception $e) {
                    
                        $sql = "INSERT INTO `artikel_tyre_price_logerror` (`errormessage`, `errorvalues`, `scanner`, `uniqid`) VALUES(?,?,?,?)";
                        $statement = $pdo->prepare($sql);
                        $values = $rm_artikelnummer . "\n" . $tyre_artikelnummer . "\n" . $wid . "\n" . $price . "\n" . $uniqid . "\n" . $last_scan . "\n" . $stock;
                        
                        $statement->execute(array($e->getMessage(), $values, $scan['id'], $uniqid));                                                
                    
                    }
                }
                
            }
        }
        
        // Pause fuer Scanner einrichten zwischen 5-15 Minuten
        mt_srand(time());
        $pause = mt_rand($scan['scan_pause_min'],$scan['scan_pause_max']) * 60;
        
        
        // Last activity scanner
        $inUse = "false";
        $sql = "UPDATE `scanner` SET `last_activity` = NOW(), `in_use`=?, `last_run` = NOW() WHERE `id`=?";
        $statement = $pdo->prepare($sql);
        $statement->execute(array($inUse, $scan['id']));
        
        
        // Log scanner
        $counts = count($lieferantPrices);
        $sql = "INSERT INTO `scanner_log` (`scanner`, `uniqid`, `start`, `finished`, `articles`, `pause`) VALUES(?,?,?,?,?,?)";
        $statement = $pdo->prepare($sql);
        $statement->execute(array($scan['id'], $uniqid, $mysql_date_now , $mysql_date_finished , $counts , $pause));
        
        $sql = "UPDATE `csv_upload_files` SET `status` = 'finish' WHERE `hashcode` = ?";
        $statement = $pdo->prepare($sql);
        $statement->execute(array($uniqid));
        
        
        
        // Close Connection
        $statement->closeCursor(); // this is not even required
        $statement = null; // doing this is mandatory for connection to get closed
        $pdo = null;
        
        
        // Sleep scan        
        sleep($pause);
        
        
        
        
       
    }
    
    
}