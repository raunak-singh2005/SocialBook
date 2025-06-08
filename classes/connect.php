<?php
// Database connection and query handling class
class Database {

    // Database credentials
    private $host = 'localhost';
    private $username = 'raunak';
    private $password = 'pcxzygFv45fw!';
    private $database = 's4402977_SocialBook';

    /**
     * Establishes and returns a connection to the database.
     * 
     */
    function getConnectionToDB() {
        $connection = mysqli_connect($this->host, $this->username, $this->password, $this->database);

        if (!$connection) {
            die("Connection failed: " . mysqli_connect_error());
        }

        return $connection;
    }

    /**
     * Executes a SELECT query and returns the result as an array.
     */
    function read($query) {
        $connection = $this->getConnectionToDB();
        $result = mysqli_query($connection, $query);

        if (!$result) {
            die("Query failed: " . mysqli_error($connection));
            return false;
        }

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);

        return $data;
    }

    /**
     * Executes an INSERT, UPDATE, or DELETE query.
     */
    function write($query) {
        $connection = $this->getConnectionToDB();
        $result = mysqli_query($connection, $query);

        if (!$result) {
            die("Query failed: " . mysqli_error($connection));
            return false;
        }

        return true;
    }
}
