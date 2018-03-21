<?php


class scanner
{
    
    protected $modelTable;
    
    public function __construct( $model )
    {
        
        $this->modelTable = $model;
        
        
        
        //mail('damir.enseleit@selfphp.de', 'Thread Scanner Class startet', "Go" );
        
        $start = microtime(true);
        for ($i = 1; $i <= 5; $i++) {
            //$this->modelTable->insertThread($i);
            $t[$i] = new AsyncOperation($i, $model);
            $t[$i]->start();
        }
        
    }
}

class AsyncOperation extends Thread
{
    
    protected $dbCon;
    
    public function __construct($threadId,$model)
    {
        //$this->model = $model;
        
        $this->threadId = $threadId;
        
        $this->connectDb();
        
        
    }
    
    private function connectDb(){
        
        $dbParams = array(
            'database'  => 'reifen_mueller_tyre24',
            'username'  => 'tire_manager',
            'password'  => 'Fkrr91!3',
            'hostname'  => 'localhost',
        );
        
        $pdo = new PDO('mysql:host=localhost;dbname=reifen_mueller_tyre24', 'tire_manager', 'Fkrr91!3');
        //$pdo->query("INSERT INTO `threads` (`scanner`) VALUES(22)");
        
        $statement = $pdo->prepare("INSERT INTO `threads` (`scanner`) VALUES(?)");
        $statement->execute(array(33));
                //$this->dbCon = mysqli_connect($dbParams['hostname'], $dbParams['username'], $dbParams['password'], $dbParams['database']);
        //"INSERT INTO `threads` (`scanner`) VALUES(?)"
        //mysqli_query($this->dbCon, "INSERT INTO `threads` (`scanner`) VALUES(1)");
        
    }

    public function run()
    {
        printf("T %s: Sleeping 3sec\n", $this->threadId);
        sleep(3);
        printf("T %s: Hello World\n", $this->threadId);
       // $this->model->insertThread($this->threadId);
    }
}