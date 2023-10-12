 <?php

 // class to initiate database connection and necessary settings
 class Connection{
    private $db;
    private $connected = false;
    
    //constructor to load connection at a go
    function __construct()
    {
        $dbHost = getenv('DB_HOST');
        $dbPort = getenv('DB_PORT');
        $dbName = getenv('DB_DATABASE');
        //$dbUser = getenv('DB_USERNAMEE');
        $dbUser = 'root';
        //$dbPass = getenv('DB_PASSWORD');
        $dbPass = '';

        $this->db = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

        if($this->db->connect_error){
            die("Database Connection Failed: ". $this->db->connect_error);
        }else{
            $this->connected = true;
            //echo "Connected";
        }
    }

    public function getConnection(){
        return $this->db;
    }
    public function isConnected(){
        return $this->connected;
    }
 }
 
 ?>
