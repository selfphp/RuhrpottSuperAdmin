<?php

// Get Thread Class
include_once getcwd() . '/httpdocs/cronserver/AsyncOperation.php';

/**
 * Database Connection data
 * @var unknown
 */
$dbParams = array(
    'database'  => 'reifen_mueller_tyre24',
    'username'  => 'tire_manager',
    'password'  => 'Fkrr91!3',
    'hostname'  => 'localhost',
);

function setArticles($pdo, $uniqid, $limit = 5){
    
    $sql = "UPDATE artikel dest, (SELECT * FROM artikel where `in_scan`='false' AND `tyre_artikelnummer` > 0 ORDER BY last_scan DESC LIMIT ?) src SET dest.in_scan = 'true', dest.set_uniqid = ? where dest.id=src.id";
    
    $statement = $pdo->prepare($sql);
    $statement->execute(array($limit, $uniqid));
    
    return true;
    
}

function getArticles($pdo, $uniqid){
    
    $sql = "SELECT * FROM `artikel` WHERE `set_uniqid`=?";
    
    $statement = $pdo->prepare($sql);
    $statement->execute(array($uniqid));
    
    $articles = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    return $articles;
    
}

function getScanArticles($pdo, $limit){

    // SELECT * FROM `artikel` WHERE `in_scan`='false' AND TRIM(tyre_artikelnummer) <> '' ORDER BY `last_scan` ASC, `prio` DESC LIMIT
    // TRIM(tyre_artikelnummer) <> ''
    $sql = "SELECT * FROM `artikel` WHERE `in_scan`='false' AND TRIM(tyre_artikelnummer) <> '' ORDER BY `last_scan` ASC LIMIT ".$limit;
    //mail('damir.enseleit@selfphp.de', '$$sql', $sql);
    $statement = $pdo->prepare($sql);
    $statement->execute(array($limit));

    $articles = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $articles;

}

function setScanArticles($pdo, $uniqid, $id){
    $sql = "UPDATE `artikel` SET `in_scan` = 'true', `set_uniqid` = ? WHERE `id`=?";
    //mail('damir.enseleit@selfphp.de', '$$sql', $sql);
    $statement = $pdo->prepare($sql);
    $statement->execute(array($uniqid, $id));
    
    return true;
}

function getUploadsScanArticles($pdo){

    $sql = "SELECT * FROM `csv_upload_files` WHERE `status`='wait' LIMIT 1";
    //mail('damir.enseleit@selfphp.de', '$$sql', $sql);
    $statement = $pdo->prepare($sql);
    $statement->execute(array());

    $articles = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $articles;

}

function getUploadsScanArticlesEntries($pdo, $hashcode){

    $sql = "SELECT * FROM `csv_upload_articles` WHERE `uniqid` = ?";
    
    $statement = $pdo->prepare($sql);
    $statement->execute(array($hashcode));

    $articles = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $articles;

}

function setUploadsScanArticlesProgress($pdo, $id){

    $sql = "UPDATE `csv_upload_files` SET `status` = 'progress' WHERE `id`=?";
    
    $statement = $pdo->prepare($sql);
    $statement->execute(array($id));

    return true;

}

function getScanArticleForUpload($pdo, $article){

    $sql = "SELECT * FROM `artikel` WHERE `rm_artikelnummer` = ?";
    //mail('damir.enseleit@selfphp.de', '$$sql', $sql);
    $statement = $pdo->prepare($sql);
    $statement->execute(array($article));

    $articles = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $articles;

}


// Hole alle nutzbaren Scanner
$scannerUse = "SELECT * FROM `scanner` WHERE `active` = 'true' AND `in_use` = 'false' AND `accountid` IS NOT NULL AND `passwd` IS NOT NULL";
//$scannerUse = "SELECT * FROM `scanner` WHERE `active` = 'true' AND `in_use` = 'false' AND `accountid` IS NOT NULL AND `passwd` IS NOT NULL LIMIT 1";

$pdo = new PDO('mysql:host=localhost;dbname='.$dbParams['database'], $dbParams['username'], $dbParams['password']);
$statement = $pdo->prepare($scannerUse);
$statement->execute(array());

$scanner = array();

$scanner = $statement->fetchAll(PDO::FETCH_ASSOC);

/*
$sql = "UPDATE artikel SET in_scan = 'false', set_uniqid = '' WHERE 1";
$statement = $pdo->prepare($sql);
$statement->execute(array());
*/
$ausgabe = print_r($scanner,true);
//mail('damir.enseleit@selfphp.de', 'Thread Scanner $premium', $ausgabe);

//return;
$start = microtime(true);


// Start Premium Costumer
for ($i = 0; $i < count($scanner); $i++) {
    
    $scanner[$i]['uniqid'] = uniqid();
    
    $scanner[$i]['startId'] = $start;
    
    mt_srand();
    $scanner[$i]['counts'] = mt_rand($scanner[$i]['article_count_min'],$scanner[$i]['article_count_max']);
    
    $scanner[$i]['pauseScan'] = mt_rand($scanner[$i]['scan_pause_min'],$scanner[$i]['scan_pause_max']) * 60;
    
    $scanner[$i]['pauseArticle'] = mt_rand($scanner[$i]['article_pause_min'],$scanner[$i]['article_pause_max']);
    
    
    // Ueberpruefe ob uploads vorgezogen werden muessen
    $uploadFiles = getUploadsScanArticles($pdo);
    if( count( $uploadFiles ) == 1 ){
        $scanner[$i]['uniqid'] = $uploadFiles[0]['hashcode'];
        // set progress
        setUploadsScanArticlesProgress($pdo, $uploadFiles[0]['id']);
        
        // get articles from upload
        $articlesnumbers = getUploadsScanArticlesEntries($pdo, $uploadFiles[0]['hashcode']);
        
        $artUpload = array();
        for($v=0;$v<count($articlesnumbers);$v++){
            $backArticle = getScanArticleForUpload($pdo, $articlesnumbers[$v]['rm_artikelnummer']);
            if(count($backArticle)>0){
                $artUpload[] = $backArticle[0];
            }
        }
        
        $scanArticlesGet = $artUpload;
        $ausgabe = print_r($artUpload,true);
        //mail('damir.enseleit@selfphp.de', '$artUpload', $ausgabe);
    }else{
    
        $scanArticlesGet = getScanArticles($pdo, $scanner[$i]['counts']);
    }
    $ausgabe = print_r($scanArticlesGet,true);
    //mail('damir.enseleit@selfphp.de', 'Thread Scanner $premium', $ausgabe);
    
    for ($t = 0; $t < count($scanArticlesGet); $t++) {
        $scanArticlesGet[$t]['set_uniqid'] = $scanner[$i]['uniqid'];
        //setScanArticles($pdo, $scanner[$i]['uniqid'], $scanner[$i]['id']);
        setScanArticles($pdo, $scanner[$i]['uniqid'], $scanArticlesGet[$i]['id']);
    }
    
    // Set scanner
    $inUse = "true";
    $sql = "UPDATE `scanner` SET `in_use`=? WHERE `id`=?";
    $statement = $pdo->prepare($sql);
    $statement->execute(array($inUse, $scanner[$i]['id']));
    
    //setScanArticles($pdo, $uniqid, $id)
    //mail('damir.enseleit@selfphp.de', '$scanArticlesGet', print_r($scanArticlesGet,true));
    
    // Set articles
    /*
    $sql = "UPDATE `artikel dest`, (SELECT * FROM `artikel` WHERE `in_scan`='false' AND `tyre_artikelnummer` > 0 ORDER BY `last_scan` DESC LIMIT :limit) src SET dest.in_scan = 'true', dest.set_uniqid = :uniqid where dest.id=src.id";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(':limit', $scanner[$i]['counts'], PDO::PARAM_INT);
    $statement->bindParam(':uniqid', $scanner[$i]['uniqid'], PDO::PARAM_STR);
    
    $statement->execute(array());
    */
    // Get article for scan
    //$getArticle = getArticles($pdo, $scanner[$i]['uniqid']);
    //mail('damir.enseleit@selfphp.de', 'Thread Scanner $premium', print_r($getArticle,true));
    $scanner[$i]['articles'] = $scanArticlesGet;
    //mail('damir.enseleit@selfphp.de', 'Thread Scanner $premium', print_r($scanner,true));
    
    $scanSet = array();
    $scanSet = $scanner[$i];
    
    //$t[$i] = new AsyncOperation($i,$scanSet);
    $t = new AsyncOperation($i,$scanSet);
    $t->start();
}


echo microtime(true) - $start . "\n";
echo "end\n";