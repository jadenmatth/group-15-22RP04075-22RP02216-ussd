<?php
// Global constants using define
define("GO_BACK", "98");
define("GO_TO_MAIN_MENU", "99");
define("HOST", "localhost");
define("DBNAME", "mini_momo");
define("USERNAME", "root");
define("PASSWORD", "");

define("USER_BALANCE", 400);
define("TRANSACTION_FEE", 100);
define("AGENT_COMMISSION", 50);

// Africa's Talking SMS settings
define("AT_USERNAME", "sandbox");
define("AT_API_KEY", "atsk_03e3118aed5d43a2ca2cfc11963c8c734ffa5503b3296ea2a1ccbd156477c61d4f7237ee");
define("SMS_SENDER", "51001");

class Util {
    private $pdo;

    public function __construct() {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => true,
        ];

        try {
            $this->pdo = new PDO(
                "mysql:host=" . HOST . ";dbname=" . DBNAME,
                USERNAME,
                PASSWORD,
                $options
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("System temporarily unavailable. Please try again later.");
        }
    }

    public function getConnection() {
        return $this->pdo;
    }

    public static function hashPin($pin) {
        return password_hash($pin, PASSWORD_BCRYPT);
    }

    public static function verifyPin($inputPin, $hashedPin) {
        return password_verify($inputPin, $hashedPin);
    }

    public static function generateReference() {
        return uniqid('TX-');
    }

    public static function formatAmount($amount) {
        return number_format($amount, 2);
    }
}
?>
