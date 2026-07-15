<?php
class Database {
    private static $instance = null;
    private $db;

    private function __construct() {
        $dbPath = __DIR__ . '/../bdpay.db';
        try {
            $this->db = new PDO("sqlite:$dbPath");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->initTables();
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->db;
    }

    private function initTables() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                transaction_id TEXT UNIQUE NOT NULL,
                payer_name TEXT NOT NULL,
                payer_email TEXT NOT NULL,
                amount REAL NOT NULL,
                currency TEXT NOT NULL DEFAULT 'USD',
                payment_method TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                payment_details TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS bdcoin_wallet (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                wallet_address TEXT UNIQUE NOT NULL,
                balance REAL NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS bdcoin_transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tx_hash TEXT UNIQUE NOT NULL,
                from_address TEXT NOT NULL,
                to_address TEXT NOT NULL,
                amount REAL NOT NULL,
                type TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS api_keys (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                api_key TEXT UNIQUE NOT NULL,
                label TEXT NOT NULL,
                active INTEGER NOT NULL DEFAULT 1,
                merchant_id TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_used_at DATETIME DEFAULT NULL
            )
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS refunds (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                transaction_id TEXT NOT NULL,
                refund_id TEXT UNIQUE NOT NULL,
                amount REAL NOT NULL,
                reason TEXT,
                status TEXT NOT NULL DEFAULT 'processed',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id)
            )
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS swpe_wallet (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                wallet_address TEXT UNIQUE NOT NULL,
                balance REAL NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS swpe_transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tx_hash TEXT UNIQUE NOT NULL,
                from_address TEXT NOT NULL,
                to_address TEXT NOT NULL,
                amount REAL NOT NULL,
                type TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS exchange_orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id TEXT UNIQUE NOT NULL,
                from_asset TEXT NOT NULL,
                to_asset TEXT NOT NULL,
                from_address TEXT NOT NULL,
                to_address TEXT,
                from_amount REAL NOT NULL,
                to_amount REAL NOT NULL,
                rate REAL NOT NULL,
                fee REAL NOT NULL DEFAULT 0,
                status TEXT NOT NULL DEFAULT 'completed',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        try { $this->db->exec("ALTER TABLE api_keys ADD COLUMN merchant_id TEXT"); } catch (\PDOException $e) {}

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                name TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'admin',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME
            )
        ");

        $stmt = $this->db->query("SELECT COUNT(*) as count FROM admin_users");
        if ($stmt->fetch()['count'] == 0) {
            $stmt = $this->db->prepare("INSERT OR IGNORE INTO admin_users (email, password_hash, name, role) VALUES (?, ?, ?, 'superadmin')");
            $stmt->execute(['md.s.lincolm@gmail.com', password_hash('01715340463', PASSWORD_BCRYPT), 'Md S. Lincolm']);
        }

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS oauth_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                provider TEXT NOT NULL,
                provider_user_id TEXT NOT NULL,
                email TEXT NOT NULL,
                name TEXT NOT NULL,
                avatar_url TEXT,
                admin INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(provider, provider_user_id)
            )
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS merchants (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                merchant_id TEXT UNIQUE NOT NULL,
                business_name TEXT NOT NULL,
                contact_name TEXT NOT NULL,
                email TEXT NOT NULL,
                payment_method TEXT NOT NULL,
                wallet_address TEXT,
                status TEXT NOT NULL DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $stmt = $this->db->query("SELECT COUNT(*) as count FROM bdcoin_wallet");
        $row = $stmt->fetch();
        if ($row['count'] == 0) {
            $this->db->exec("INSERT INTO bdcoin_wallet (wallet_address, balance) VALUES ('BDC1A2B3C4D5E6F7G8H9I0J', 1000000)");
            $this->db->exec("INSERT INTO bdcoin_wallet (wallet_address, balance) VALUES ('BDC9I8U7Y6T5R4E3W2Q1P0', 500000)");
        }

        $stmt = $this->db->query("SELECT COUNT(*) as count FROM swpe_wallet");
        $row = $stmt->fetch();
        if ($row['count'] == 0) {
            $this->db->exec("INSERT INTO swpe_wallet (wallet_address, balance) VALUES ('SWPE1A2B3C4D5E6F7G8H9I0J', 5000000)");
            $this->db->exec("INSERT INTO swpe_wallet (wallet_address, balance) VALUES ('SWPE9I8U7Y6T5R4E3W2Q1P0', 2500000)");
        }
    }
}
