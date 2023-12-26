 <?php
    // class to initiate database connection and necessary settings
    class Connection
    {
        private $db;
        private $connected = false;

        //constructor to load connection at a go
        function __construct()
        {
            //$dbHost = getenv('DB_HOST');
            //$dbPort = getenv('DB_PORT');
            //$dbName = getenv('DB_DATABASE');
            //$dbUser = getenv('DB_USERNAMEE');
            //$dbPass = getenv('DB_PASSWORD');

            //------ Prod -----
            $dbHost = '62.72.26.181';
            $dbName = 'kojfj565fhgvgh87t8gyube';
            $dbPort = 3306;
            $dbUser = 'kobe';
            $dbPass = 'Kobe@Warehouse2023!#';

            //----- Dev ---------
            // $dbHost = '';
            // $dbName = 'kojfj565fhgvgh87t8gyube';
            // $dbPort = 3306;
            // $dbUser = 'root';
            // $dbPass = '';

            $this->db = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

            if ($this->db->connect_error) {
                die("Database Connection Failed: " . $this->db->connect_error);
            } else {
                $this->connected = true;
            }
        }

        public function getConnection()
        {
            return $this->db;
        }
        public function isConnected()
        {
            return $this->connected;
        }
    }

    ?>
