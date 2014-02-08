<?php
global $db_host, $db_name, $db_user, $db_password, $db_error, $database, $db;

// Begin user-configurable section.
$db_host = 'localhost';
$db_name = '';
$db_user = '';
$db_password = '';
$db_error = '{"result":0,"message":"Database error!"}';
// End user-configurable section.

$dsn = 'mysql:host='.$db_host.';port=3306;dbname='.$db_name;

Class SafePDO extends PDO {
	public static function exception_handler($exception) {
		// Output the exception details
		die('Uncaught exception: '. $exception->getMessage());
	}

	public function __construct($dsn, $username='', $password='', $driver_options=array(PDO::ATTR_PERSISTENT => true)) {
		// Temporarily change the PHP exception handler while we . . .
		set_exception_handler(array(__CLASS__, 'exception_handler'));

		// . . . create a PDO object
		parent::__construct($dsn, $username, $password, $driver_options);

		// Change the exception handler back to whatever it was before
		restore_exception_handler();
	}
}

// Connect to the database with defined constants
$database = new SafePDO($dsn, $db_user, $db_password);
$database->query("SET NAMES 'utf8'");
$database->setAttribute(PDO::ATTR_PERSISTENT, true);

// Function for printing a database querying error, when one arises.
function db_print_error() {
	global $database;
	$db_error = $database->errorInfo();
	exit_with_error('Error: An error has occurred while querying database - <p/>'.$db_error[2]);
}

/*
Example Info:
        Basic PDO Query (Pre-Sanitized or No Data Sanitation Required):
        $query = $database->query("SELECT * FROM ... WHERE ...");
        if ($query) {
                $data = $query->fetchAll();
                $num_rows = count($data);
        } else { db_print_error(); }

        Prepared PDO Query (Protection from SQL Injections Built-in):
        $query = $database->prepare("SELECT * FROM ... WHERE thing = :whatsit AND person = :whosit");
        $query->execute(array('whatsit' => 'burger', 'whosit' => 'John'));
        if ($query) {
                $data = $query->fetchAll();
                $num_rows = count($data);
        } else { db_print_error(); }

        For Transactions:
        $database->beginTransaction();
        ...statement code...
        $database->commit();

        Detecting Last Insert ID:
        $database->lastInsertId();

        Detecting Rows Deleted:
        $query->rowCount();

        Explanation of Querying Errors:
        $db_error = $query->errorInfo();
        print $db_error[2];
*/

?>
