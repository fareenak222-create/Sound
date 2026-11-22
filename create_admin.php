<?php
// Command-line tool: php create_admin.php
// Creates (or promotes) an administrator with a password you type; nothing is stored in plain text.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Run this script from the command line.'); }
require __DIR__ . '/includes/config.php';
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
function ask(string $p, bool $hide = false): string {
    echo $p; if ($hide && DIRECTORY_SEPARATOR === '/') system('stty -echo');
    $v = trim((string)fgets(STDIN)); if ($hide && DIRECTORY_SEPARATOR === '/') { system('stty echo'); echo "\n"; } return $v;
}
$uid = ask('User ID (4-30 letters/numbers/_/.): '); $name = ask('Full name: '); $email = ask('Email: '); $phone = ask('Phone: '); $addr = ask('Address: ');
$pw = ask('Password (min 12 chars recommended): ', true); $pw2 = ask('Confirm password: ', true);
if (!preg_match('/^[A-Za-z0-9_.]{4,30}$/', $uid) || strlen($name) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL)) exit("Invalid User ID, name or email.\n");
if (strlen($pw) < 8 || $pw !== $pw2) exit("Passwords must match and be at least 8 characters.\n");
$ex = $pdo->prepare('SELECT id FROM users WHERE user_id=? OR email=?'); $ex->execute([$uid, $email]);
if ($row = $ex->fetch(PDO::FETCH_ASSOC)) exit("User ID or email already exists (id {$row['id']}). Choose different values.\n");
$pdo->prepare("INSERT INTO users (user_id, full_name, address, phone, email, password_hash, role) VALUES (?,?,?,?,?,?, 'admin')")
    ->execute([$uid, $name, $addr ?: 'N/A', $phone ?: '0000000', $email, password_hash($pw, PASSWORD_DEFAULT)]);
echo "Administrator '$uid' created. Log in at /login.php. Remember to delete the sample 'admin_demo' account in Admin > Users.\n";
