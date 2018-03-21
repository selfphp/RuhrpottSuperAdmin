<?php
namespace Cron\Controller;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Response as ConsoleResponse;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;
use Zend\Log\Formatter\Simple;
use simple_html_dom;
use pricecalc;
use scanner;
use Application\ConfigAwareInterface;

class Registertick {
    private $counter = 0;

    private $output;
    
    public function increase()
    {
        list($sec, $timestamp) = explode(" ",
            microtime());
        $debug = debug_backtrace();
        
        $this->output .= '--->Datei:'. $debug[0]['file'] . ' --->Zeile: '.$debug[0]['line'].'->'.$sec.' msec'."\n";
        
    }
    
    public function savedata(){
        $file = getcwd() . "/data/export/scanner_export/tick.txt";
        file_put_contents($file, $this->output);
    }
    public function startincrease()
    {
        //$file = getcwd() . "/data/export/scanner_export/tick.txt";
        $this->output .= "\n\n".'--->Start'."\n";
        //file_put_contents($file, $current, FILE_APPEND);
    }
    
    public function nextincrease($sek)
    {
        //$file = getcwd() . "/data/export/scanner_export/tick.txt";
        $this->output .= "\n\n".'--->Total: ' . $sek ."\n";
        //file_put_contents($file, $current, FILE_APPEND);
    }
   
}

class CronController extends AbstractActionController implements ConfigAwareInterface
{
    // * * * * * /usr/bin/php /path/to/your/domain.com/public/index.php cron
    // * * * * * /usr/bin/php /path/to/your/domain.com/public/index.php cron --param1=test1
    // * * * * * /usr/bin/php /path/to/your/domain.com/public/index.php cron --param1=test1 --param2=test2
    
    protected $lieferanten;
    protected $modelTable;
    protected $lieferantenTable;
    
    protected $configZfc;
    
    protected $uniqid;
    
    public function setConfig($config)
    {
        $this->configZfc = $config;
    }
    
    public function __construct($dbone)
    {
        $this->modelTable = $dbone;
    
        $this->uniqid = uniqid();
    
    }
    
    private function exportImportfilesArticles($folder){
        
        $files = $this->modelTable->getUploadFiles( );
        
        for($x=0;$x<count($files);$x++){
            $data = $this->modelTable->getUploadFilesData( $files[$x]['hashcode'] );
            $getRmArticleExport = array();
            for($y=0;$y<count($data);$y++){
                $entries = $this->modelTable->getUploadFilesDataEntries( $data[$y]['uniqid'], $data[$y]['rm_artikelnummer'] );
                //print_r($entries);
                // getUploadFilesDataAll
                $getRmArticleExport = $this->modelTable->getUploadFilesDataAll( $files[$x]['hashcode'] );
                //print_r($getRmArticleExport);
                
                $filename = $folder.$data[$y]['uniqid'].'.csv';
                $this->exportImportfilesArticlesCsv($filename,$getRmArticleExport);
                
                $this->modelTable->updateImportFilesDownload( $files[$x]['hashcode'] );
                
            }
            
            // getUploadFilesDataEntries( $uniqid, $rm_artikelnummer )
        }
        
        $ausgabe = print_r($files,true);
       // mail('damir.enseleit@selfphp.de', '$files', $ausgabe);
        
        
        
    }
    
    private function exportImportfilesArticlesCsv($file, $getRmArticleExport){
    
    
        $this->calculatePlugin = $this->CalculatePlugin();
        
    
        if( file_exists($file)){
            unlink($file);
        }
    
        // create a file pointer connected to the output stream
        $output = fopen($file, 'w');
    
        $lieferanten = $this->modelTable->getLieferantenExport( );
        // print_r($lieferanten);
        //$getRmArticleExport = $this->modelTable->getRmArticleExport( );
    
        fputcsv($output, array('ArtikelNr', 'T24ID', 'Price', 'Cheapest', 'Error'));
        //for($x=0;$x<10;$x++){
        for($x=0;$x<count($getRmArticleExport);$x++){
    
            $article = array();
            //$article = $this->modelTable->getRmArticle( false, $articlenumber[$x] );
            $article[0] = $getRmArticleExport[$x];
    
            // Es muss nach der Tyre Nummer gescannt werden wegen dem Handling, 0 ist der Original Artikel mit den Werten!
            $tyrearticle = true;
            $articlenumbers = $article[0]['tyre_artikelnummer'];
    
            $articleTyre = $this->modelTable->getTyreArticle( $tyrearticle, $articlenumbers );
    
            // Berechnet den Preis für Original-Artikel
            //$priceBoth = $this->calculatePrice($article, $articleTyre, $lieferanten);
            $priceBoth = $this->calculatePlugin->calculatePrice($article, $articleTyre, $lieferanten);
            $price = $priceBoth['price'];
            /*
            echo '$article -- 0'."\n";
            print_r($article);
            echo '$$articleTyre -- 1'."\n";
            print_r($articleTyre);
            echo '$$articleTyreSecond-- 2'."\n";
            print_r($lieferanten);
    */
            $error = 0;
    
            if( $price < 1 && $price != 'error'){
                $error = 1;
            }else if($price == 'error'){
                $error = 2;
            }
    
            $number = str_pad($article[0]['rm_artikelnummer'], 9 ,'0', STR_PAD_LEFT);
    
    
            //fputcsv($output, array(11, $article[0]['tyre_artikelnummer'], 12,13, 4));
            fputcsv($output, array($number, $article[0]['tyre_artikelnummer'], $price,$priceBoth['cheapest'], $error));
        }
    
    
        // Hash
        // 9c2cff44abd4cb2c74d3f6cb27af92f1728b9d96ee975374c541bffe31d8021b
        //$filehash = getcwd() . "/data/export/scanner_export/rmarticles_export_daily.sha256";
        //$hashcode = hash_file('sha256', $file);
        /*
        if( file_exists($filehash) ){
            unlink($filehash);
        }
        file_put_contents($filehash, $hashcode . ' rmarticles_export_daily.csv');
    
        echo "Export " . count($getRmArticleExport) . " articles with Hash-Code " . $hashcode . " at " . date("Y-m-d H:i:s");
        */
    }
    
    private function laufzeit_messen() {
        list($sec, $timestamp) = explode(" ",
            microtime());
        $debug = debug_backtrace();
        echo ' --->Zeile:
            '.$debug[0]['line'].'->'.$sec.' msec<br>';
    }
    
    private function exportArticlesDaily($file,$fileErrors){
        
        //register_tick_function('laufzeit_messen');
        
        // Fuer Testzwecke um einen einzelnen Artikel zu testen...
        $setTest = false;
        $articlenumber = false;
        if( $setTest == true ){
            $file = getcwd() . "/data/export/scanner_export/rmarticles_export_daily1_test.csv";
            $fileErrors = getcwd() . "/data/export/scanner_export/rmarticles_export_daily_errors1_test.csv";
            $articlenumber = "8037650";
        }
        
        if( file_exists($file)){
            unlink($file);
        }
        
        if( file_exists($fileErrors)){
            unlink($fileErrors);
        }
        
        // create a file pointer connected to the output stream
        $output = fopen($file, 'w');
        $outputErrors = fopen($fileErrors, 'w');
        
        $lieferanten = $this->modelTable->getLieferantenExport( );
       // print_r($lieferanten);
        $getRmArticleExport = $this->modelTable->getRmArticleExport( $articlenumber );
        
        fputcsv($output, array('ArtikelNr', 'T24ID', 'Price', 'Cheapest', 'Error'));
        fputcsv($outputErrors, array('ArtikelNr', 'T24ID', 'Price', 'Cheapest', 'Error', 'PUG', 'POG'));
        //for($x=0;$x<10;$x++){
        
        $obj = new Registertick();
        register_tick_function([&$obj, 'increase'], true);
        
        declare(ticks = 1);
        
        $this->calculatePlugin = $this->CalculatePlugin();
        $startTotal = microtime(true);
        for($x=0;$x<count($getRmArticleExport);$x++){
            
            $obj->startincrease();
            
            //do_tick('--start--');
            $start = microtime(true);
            $article = array();
            //$article = $this->modelTable->getRmArticle( false, $articlenumber[$x] );
            $article[0] = $getRmArticleExport[$x];
            
            // Es muss nach der Tyre Nummer gescannt werden wegen dem Handling, 0 ist der Original Artikel mit den Werten!
            $tyrearticle = true;
            $articlenumbers = $article[0]['tyre_artikelnummer'];
            
            $articleTyre = $this->modelTable->getTyreArticle( $tyrearticle, $articlenumbers );
            
            // Berechnet den Preis für Original-Artikel
            //$priceBoth['price'] = 0;
            //$priceBoth['cheapest'] = 0;
            
            $priceBoth = $this->calculatePlugin->calculatePrice($article, $articleTyre, $lieferanten);
            
            
            $price = $priceBoth['price'];
            
            $error = 0;
            
            if( $price < 1 && $price != 'error'){
                $error = 1;
            }else if($price == 'error'){
                $error = 2;
            }
            
            $number = str_pad($article[0]['rm_artikelnummer'], 9 ,'0', STR_PAD_LEFT);
            
            
            //fputcsv($output, array(11, $article[0]['tyre_artikelnummer'], 12,13, 4));
            
            
            // Überprüfe Preisfehler - Preis darf nich unter PUG oder über POG liegen!
            if( $price < $article[0]['pug'] || $price > $article[0]['pog'] ){
                fputcsv($outputErrors, array($number, $article[0]['tyre_artikelnummer'], $price,$priceBoth['cheapest'], $error,$article[0]['pug'], $article[0]['pog']));
            }else{
                fputcsv($output, array($number, $article[0]['tyre_artikelnummer'], $price,$priceBoth['cheapest'], $error));
            }
            
            $end = microtime(true);
            $laufzeit = $end-$start;
            $obj->nextincrease($laufzeit." Sekunden!");
            
            unset($priceBoth);
            
            
            
        }
        $endTotal = microtime(true);
        $laufzeit = $endTotal-$startTotal;
        $obj->nextincrease("Gesamt: ".$laufzeit." Sekunden!");
        
        $obj->savedata();
        
        // Hash
        // 9c2cff44abd4cb2c74d3f6cb27af92f1728b9d96ee975374c541bffe31d8021b
        $filehash = getcwd() . "/data/export/scanner_export/rmarticles_export_daily.sha256";
        $hashcode = hash_file('sha256', $file);
        if( file_exists($filehash) ){
            unlink($filehash);
        }        
        file_put_contents($filehash, $hashcode . ' rmarticles_export_daily.csv');
        
        echo "Export " . count($getRmArticleExport) . " articles with Hash-Code " . $hashcode . " at " . date("Y-m-d H:i:s");
    }
    
    private function updateLieferanten($file){
        
       
        
        if( file_exists($file)){
            //mail('damir.enseleit@selfphp.de', 'Import existiert', $file);
        }else{
            echo $file.' existiert nicht';
            mail('damir.enseleit@selfphp.de', 'Reifen Müller Lieferanten existiert nicht', $file);
            return;
        }
        
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $setLieferant = $this->modelTable->updateLieferanten( $data[1], $data[3] );
            }
        }
        
    }
    
    private function importArticles($file){
        
        /*
        mail('damir.enseleit@selfphp.de', 'Import 38090130', "");
        echo 'hallo welt';
        return;
        */
        
        
        if( file_exists($file)){
            //mail('damir.enseleit@selfphp.de', 'Import existiert', $file);
        }else{
            echo $file.' existiert nicht';
            mail('damir.enseleit@selfphp.de', 'Reifen Müller Import existiert nicht', $file);
            return;
        }
        
        // Check Hash File
        $filehash = getcwd() . "/data/export/rm_import/rmarticles_import_daily.sha256";
        if( file_exists($filehash) ){
            $hashstring = file_get_contents($filehash);
            $hashcode = explode(' ',$hashstring);
            
            $hashcodeFile = hash_file('sha256', $file);
            
            if( $hashcodeFile == $hashcode[0] ){
                echo 'Fehler: Bereits importierte Datei';
                return;
            }
            
        }
        
        $row = 0;
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                
                $article = array();
                
                
                // round(1.95583, 2)
                
                $article['rm_artikelnummer'] = $data[0] * 1; // Führende Nullen entfernen
                $article['desc_1'] = $data[1];
                $article['desc_2'] = $data[2];
                $article['tyre_artikelnummer'] = $data[3];
                $article['pog'] = floatval(str_replace(',', '.', str_replace('.', '', $data[4])));
                $article['pog'] = round($article['pog'], 2);
                $article['pug'] = floatval(str_replace(',', '.', str_replace('.', '', $data[5])));
                $article['pug'] = round($article['pug'], 2);
                $article['menge'] = $data[6];
                $article['handling'] = $data[7];
                $article['faktor'] = floatval(str_replace(',', '.', str_replace('.', '', $data[8])));
                $article['original'] = $data[9];
                $article['prio'] = $data[10];
                
                if( $article['rm_artikelnummer'] == "4090202"){
                    $send = print_r($article,true);
                    //mail('damir.enseleit@selfphp.de', 'Import 4090202', $send);
                }
                
                // Erste Zeile auslassen
                if( count($data) == 11 && $row > 0 ){
                    $setArticle = $this->modelTable->insertArticle($article);
                }
                
                $row++;
                
            }
            fclose($handle);
        }
        
        
        $hashcode = hash_file('sha256', $file);
        if( file_exists($filehash) ){
            unlink($filehash);
        }
        file_put_contents($filehash, $hashcode . ' rmarticles_import_daily.csv');
        
        echo "Import " . $row . " articles with Hash-Code " . $hashcode . " at " . date("Y-m-d H:i:s");
        
    }
    
    
    /*
     *  -Artikelnummer = Reifen Müller-Artikelnummer
     -Beschreibung = Alphanumerische Hersteller- und Größenbeschreibung mit Load- und Speedindex
     -Beschreibung 2 = Alphanumerische Profilbeschreibung und ggf. Herstellerartikelnummer
     -T24ID = in RM-Stammdaten zugeordnete T24-Artikelnummer
     -POG = Preisobergrenze -> Höchster Preis des Artikels, zu dem RM diesen anbieten möchte
     -PUG = Preisobergrenze -> Niedrigster Preis des Artikels, zu dem RM diesen anbieten möchte
     -REF Menge = Mindestmenge, zu dem ein Wettbewerbsangebot als Referenz herangezogen werden darf
     -HANDLING = Beschreibt, was als Preisvorschlag durch den Kalkulator berechnet werden soll (0=Angleichen an Ref-Preis wenn möglich; 1=Unterbieten des Ref-Preis wenn möglich; 2=Überbieten des Ref-Preis wenn möglich)
     -FAKTOR = Dient zur Berechnung des Ref-Preis (gefundener Ref-Preis * eingetragener Faktor = Ref-Preis zur Berechnung des Preisvorschlags). Standard wäre hier Faktor 1 wenn nichts anderes gefüllt ist – könnte im Export auch so eingefügt werden.
     -ORIGINAL = Handelt es sich bei der RM-Artikelnummer in Kombination mit der T24-ID um den Originalartikel oder um einen Ersatzartikel der zur Preisfindung herangezogen werden soll
     -Bestand = aktueller realer Bestand des Artikels bei Reifen Müller
     -Prio = Priorität zum Scan des Artikels
     */
    private function calculatePrice($article, $articleTyre, $lieferanten){
         
        //print_r($articleTyre);
         
         
         
        $price = 0;
        $isCalculated = false;
         
        $backPrice = array();
        $backPrice['cheapest'] = 0;
        $backPrice['price'] = 0;
         
        $cheapest = -1;
        $expensive = -1;
         
        // Reifen Mueller WID
        $wid = 1102;
         
        // Referenz-Menge
        $menge = $article[0]['menge'];
        //print_r($article);
        //print_r($articleTyre);
         
        for( $y=0;$y<count($articleTyre);$y++ ){
            $widHaendler = $articleTyre[$y]['wid'];
            if( $wid != $widHaendler && $lieferanten[$widHaendler]['active'] == "true"){
                // echo $articleTyre[$y]['price'] . "\n";
                if( $backPrice['cheapest'] == 0 || $articleTyre[$y]['price'] <= $backPrice['cheapest'] ){
                    $backPrice['cheapest'] = $articleTyre[$y]['price'];
                }
            }
        }
        //echo $backPrice['cheapest'];
         
         
        for( $x=0;$x<count($articleTyre);$x++ ){
            $widHaendler = $articleTyre[$x]['wid'];
             
            if (!array_key_exists($widHaendler, $lieferanten)) {
                return 'error';
            }
             
            if( $wid != $widHaendler && $lieferanten[$widHaendler]['active'] == "true"){
                if( $articleTyre[$x]['stock'] >= $menge ){
                    $newPrice = $articleTyre[$x]['price'] - 0.02;
                     
                    if( $cheapest == -1 || $articleTyre[$x]['price'] < $cheapest ){
                        $cheapest = $articleTyre[$x]['price'];
                    }
                     
                    if( $expensive == -1 || $articleTyre[$x]['price'] > $expensive ){
                        $expensive = $articleTyre[$x]['price'];
                    }
                     
                     
                     
                    if( $newPrice <= $article[0]['pog'] && $newPrice >= $article[0]['pug'] ){
                        if($isCalculated == false){
                            $price = $newPrice;
                            $isCalculated = true;
                        }
                    }
                     
                }
            }
        }
         
        // RM hat selber kein Preis bei Tyre 24, daher den Preis anhand der PUG / POG festmachen 
        if( $price == 0 ){
            if ( $article[0]['pog'] < $cheapest || $article[0]['pug'] < $cheapest ){
                $backPrice['price'] = $article[0]['pog'];
                //$backPrice['price'] = $cheapest - 0.02;
                return $backPrice;
                return $cheapest - 0.02;
            }
             
            if ( $article[0]['pog'] > $expensive || $article[0]['pug'] > $expensive ){
                $backPrice['price'] = $article[0]['pug'];
                //$backPrice['price'] = $cheapest + 0.02;
                return $backPrice;
                return $expensive + 0.02;
            }
             
            $backPrice['price'] = $cheapest;
            return $backPrice;
            return $cheapest;
        }else{
            $backPrice['price'] = $price;
            return $backPrice;
            return $price;
        }
         
        // error
        
    }
    
    private function setPrio(){
        
        $data = $this->modelTable->getAllArticle( );
        
        for($x=0;$x<count($data);$x++){
            $prices = $this->modelTable->getPrioPrices( $data[$x]['rm_artikelnummer'] );
        }
        
        print_r($data);
        print_r($prices);
        
        //mail('damir.enseleit@selfphp.de', 'Artikel', $send);
    }
    
    private function curlStartElastic($domain, $vari = false, $user = false, $pass = false, $cronID, $cookiePfad, $lic){
    
        unset($message);
        $fp = curl_init($domain);
    
    
        // Maximale Ausfuehrungszeit pro CronJob
        //curl_setopt($fp,CURLOPT_TIMEOUT,1800);
        curl_setopt($fp,CURLOPT_TIMEOUT,1800);
    
        // Durchlauf abbrechen wenn Server mit einem ERROR > 300 antwortet
        curl_setopt($fp,CURLOPT_FAILONERROR,1);
    
        // TRUE um den Tranfer als String zur�ckzuliefern, anstatt ihn direkt auszugeben.
        curl_setopt($fp, CURLOPT_RETURNTRANSFER, 1);
    
        // TRUE um jedem "Location: "-Header zu folgen, den der Server als Teil der HTTP-Header zur�ckgibt.
        curl_setopt($fp, CURLOPT_FOLLOWLOCATION, 1);
    
        // Wieviel Header er max. folgen soll - gibt man es nicht an sind es max. 20
        curl_setopt($fp, CURLOPT_MAXREDIRS, 20);
    
    
        // Wird ben�tigt um mit Session bzw. Cookies auf der Zielseite arbeiten zu k�nnen.
        // Problem war immer das wenn die erste Datei eine Session gestartet hat und auf die zweite Seite
        // mit einem Header weitergeleitet hat, das auf der zweiten Seite die Session verloren ging.
        //curl_setopt ($fp, CURLOPT_COOKIEJAR, $cookiePfad);
        //curl_setopt ($fp, CURLOPT_COOKIEFILE, $cookiePfad);
    
    
    
        // Passwort uebermitteln (htaccess)
        if($user != false || $pass != false)
            curl_setopt($fp,CURLOPT_USERPWD,$user.":".$pass);
    
            // Variablen uebermitteln
            if($vari != false){
                curl_setopt($fp,CURLOPT_POST,1);
                curl_setopt ($fp, CURLOPT_POSTFIELDS, $vari);
            }
    
            // 2009-03-11 20:31:02
            $message['start_time'] = date("Y-m-d H:i:s");
            $output = curl_exec($fp);
            $message['end_time'] = date("Y-m-d H:i:s");
    
            $info = curl_getinfo($fp);
    
            // letzte Fehlermeldung
            if(curl_errno($fp) != 0){
                $message['curl_errno'] = FALSE;
                $message['curl_error'] = curl_error($fp);
            }
            else{
                $message['curl_errno'] = TRUE;
                $message['curl_error'] = "+OK";
            }
    
            $message['http_code'] = $info['http_code'];
            $message['total_time'] = $info['total_time'];
    
            $message['namelookup_time'] = $info['namelookup_time'];
            $message['connect_time'] = $info['connect_time'];
            $message['pretransfer_time'] = $info['pretransfer_time'];
            $message['starttransfer_time'] = $info['starttransfer_time'];
            $message['redirect_count'] = $info['redirect_count'];
            $message['redirect_time'] = $info['redirect_time'];
    
            //$message['licVars'] = $this->maxTime . '-' . $this->maxRoute . '-' . $this->maxHeader; // $output;
    
            $output = trim($output);
            $message['output'] = $output; // $output;
    
            curl_close($fp);
    
            //unlink ( $cookiePfad );
    
            return $message;
    }
    
    private function elasticsearch(){
        
       // $out = $this->curlStartElastic("https://tm01.qozido.com:8080", false, "selfphp", "!tzw.27.vku?", "", "", "");
       // print_r($out);
       // return;
        $data = $this->modelTable->getElasticArticle(1,10);
        
        for($x=0;$x<count($data);$x++){
            
            $url = 'https://selfphp:!tzw.27.vku?@tm01.qozido.com:9243/tyre24/articles -d \'{"type": "reifen","number": "'.$data[$x]['tyre_artikelnummer'].'","wid" : "'.$data[$x]['wid'].'","price" : "'.$data[$x]['price'].'","stock" : '.$data[$x]['stock'].',"scan" : "'.$data[$x]['last_scan'].'"}\'';
            //$url = 'https://tm01.qozido.com:8080/tyre24/articles -d \'{"type": "reifen","number": "'.$data[$x]['tyre_artikelnummer'].'","wid" : "'.$data[$x]['wid'].'","price" : "'.$data[$x]['price'].'","stock" : '.$data[$x]['stock'].',"scan" : "'.$data[$x]['last_scan'].'"}\'';
            $out = $this->curlStartElastic($url, false, "selfphp", "!tzw.27.vku?", "", "", "");
            print_r($out);
        
            
        }
        //print_r($data);
    }
    
    private function deleteOldPrices(){
        $data = $this->modelTable->countArticle(1);
        print_r($data);
    }
    
    public function indexAction()
    {
        $request = $this->getRequest();
        	
        if (!$request instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }
        
        $import = $request->getParam('param1', null);
        
        
        if( $import == "elasticsearch" ){
            //mail('damir.enseleit@selfphp.de', 'Thread Scanner startet', "Go" );
        
            $this->elasticsearch();
        
            //$threadScanner = new scanner( $this->modelTable );
        }
        
        if( $import == "delete_old_prices" ){
            //mail('damir.enseleit@selfphp.de', 'Thread Scanner startet', "Go" );
            $this->deleteOldPrices();
            
            
        }
        
        if( $import == "thread_scanner" ){
            //mail('damir.enseleit@selfphp.de', 'Thread Scanner startet', "Go" );
            
            
            //$threadScanner = new scanner( $this->modelTable );
        }
        
        if( $import == "begin_set_prio" ){
            $this->setPrio();
            return;
        }
        
        if( $import == "begin_export_daily" ){
            $file = getcwd() . "/data/export/scanner_export/rmarticles_export_daily1.csv";
            $fileErrors = getcwd() . "/data/export/scanner_export/rmarticles_export_daily_errors1.csv";
            $this->exportArticlesDaily($file,$fileErrors);
            return;
        }
        
        if( $import == "begin_import" ){
            $file = getcwd() . "/data/export/rm_import/rmarticles_import_daily.csv";
            
            // Check 
            $this->importArticles($file);
            return;
        }
        
        if( $import == "begin_lieferantupdate" ){
            $file = getcwd() . "/data/export/lieferanten/lieferanten-rm.csv";
        
            // Check
            $this->updateLieferanten($file);
            return;
        }
        
        if( $import == "begin_importfiles" ){
            $folder = getcwd() . "/data/export/csv_imports/";
        
            // Check
            $this->exportImportfilesArticles($folder);
            return;
        }
        
        
        
        return;
        
        $scanner = $request->getParam('param1', null) + 0;
        
        $options = $this->modelTable->getScannerOptions($scanner);
        
        // Falls Scanner keine Option, benachrichtigen
        if( $options == false || empty($options['accountid']) || empty($options['passwd'])){
            if( $options['accountid'] == "true" ){
                $send = print_r($options,true);
                mail('damir.enseleit@selfphp.de', 'Scanner_'.$scanner . ' nicht bereit', $send);
            }            
            return;
        }
        //return;
        
        // Check if scanner in use
        $canUse = $this->modelTable->getScanner($scanner);

        if($canUse){
            //mail('damir.enseleit@selfphp.de', 'Scanner_'.$scanner, "Kann benutzt werden");
            $inUse = $this->modelTable->setScanner($scanner,"true");
            
        }else{
            //mail('damir.enseleit@selfphp.de', 'Scanner_'.$scanner, "Kann nicht benutzt werden");
            return;
        }
        
        try {
        
            // Set article for scan
            mt_srand(time());
            $counts = mt_rand($options['article_count_min'],$options['article_count_max']);
            $setArticle = $this->modelTable->setArticleScan($this->uniqid,$counts);
        
            // Get article for scan
            $getArticle = $this->modelTable->getArticleScan($this->uniqid);
        
        
            // No article for scan
            if( count($getArticle) == 0 ){
                return;
            }
        
            // Scanner date
            $mysql_date_now = date("Y-m-d H:i:s");
        
            $dom = new simple_html_dom();
        
            $crawler = new pricecalc();
        
            $back = $crawler->get_page("https://www.tyre24.com/de/de/user/login/page/", true, $options['accountid'], $options['passwd']);
        
            for( $x=0; $x<count($getArticle);$x++ ){
                $idTyre = 'T' . $getArticle[$x]['tyre_artikelnummer'];
            
                $back = $crawler->get_page("https://www.tyre24.com/de/de/item/details/id/".$idTyre."//alcar//carManufacturer//carModel//carType/");
                $save = print_r($back['pricescanner'], true);
                
                if($scanner == 7 && $x < 7){
                    //mail('damir.enseleit@selfphp.de', 'Scanner '.$scanner . ' Prozess', $save );
                }
            
                for( $y=0; $y<count($back['pricescanner']);$y++ ){
                    $this->modelTable->insertLieferant($back['pricescanner'][$y]['wid'],$back['pricescanner'][$y]['name'],$back['pricescanner'][$y]['language']);
                
                    if( $back['pricescanner'][$y]['price'][0] > 0 ){
                     $this->modelTable->insertTyrePrice($getArticle[$x]['rm_artikelnummer'], $getArticle[$x]['tyre_artikelnummer'], $back['pricescanner'][$y]['wid'], $back['pricescanner'][$y]['price'][0], $this->uniqid, $mysql_date_now, $back['pricescanner'][$y]['stock'], $scanner);
                    }
                }
                
                
                
                // lastActivityScanner
                $lastActivityScanner = $this->modelTable->lastActivityScanner($scanner);
            
                mt_srand(time());
                $pause = mt_rand($options['article_pause_min'],$options['article_pause_max']);
           
                sleep($pause);
            }
        
            for( $x=0; $x<count($getArticle);$x++ ){
                $updateArticle = $this->modelTable->updateArticleScan($mysql_date_now, $getArticle[$x]['id']);
            }
        
            $mysql_date_finished = date("Y-m-d H:i:s");
        
            // Pause fuer Scanner einrichten zwischen 5-15 Minuten
            mt_srand(time());
            $pause = mt_rand($options['scan_pause_min'],$options['scan_pause_max']) * 60;
            sleep($pause);
        
            // Log scanner
            $logScanner = $this->modelTable->logScanner($scanner, $this->uniqid, $mysql_date_now , $mysql_date_finished , $counts , $pause);
        
            // Set scanner inactive
            $inUse = $this->modelTable->setScanner($scanner,"false");
        
        } catch (\Exception $e) {
            $inUse = $this->modelTable->setScanner($scanner,"false");
            mail('damir.enseleit@selfphp.de', 'Scanner '.$scanner . ' Fehler', $e->getMessage() );
            return false;
        }
        
    }
    
    private function getLieferanten(){
        $all = $this->modelTable->getLieferanten();
        
        for( $x=0; $x<count($all); $x++ ){
            $this->lieferanten[$all[$x]['wid']] = $all[$x]['name'];
        }
    }
    
    private function checkScannerInUse(){
        
    }
    
    private function checkLieferanten( $wid ){
        
        
    }
    
   
    
}