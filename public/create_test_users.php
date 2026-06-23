<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Carrega .env do diretório raiz do projeto (um nível acima de public/)
$root   = dirname(__DIR__);
$envFile = $root . '/.env';

if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        if ($k !== '') {
            putenv("$k=$v");
        }
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$name = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';

echo "<pre>Conectando ao banco: $host:$port / $name\n\n";

if ($name === '') {
    die("ERRO: DB_NAME vazio — verifique o arquivo .env\n");
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Conexão OK.\n\n";
} catch (Exception $e) {
    die('Falha na conexão: ' . $e->getMessage() . "\n");
}

// Gera hashes corretos no servidor
$hashTeste = password_hash('Teste@123',     PASSWORD_BCRYPT, ['cost' => 12]);
$hashAdmin = password_hash('Admin@Maia2024', PASSWORD_BCRYPT, ['cost' => 12]);

echo "Hashes gerados.\n\n";

// Atualiza usuários de teste
$pdo->prepare("UPDATE users SET password_hash = ? WHERE email = 'joao@teste.com'")->execute([$hashTeste]);
echo "joao@teste.com atualizado.\n";
$pdo->prepare("UPDATE users SET password_hash = ? WHERE email = 'maria@teste.com'")->execute([$hashTeste]);
echo "maria@teste.com atualizado.\n\n";

// Atualiza senha do admin para Admin@Maia2024
$pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE email = 'cassianogalvao2020@gmail.com'")->execute([$hashAdmin]);
echo "Admin atualizado.\n\n";

echo "=== PRONTO ===\n\n";
echo "Admin:   cassianogalvao2020@gmail.com / Admin@Maia2024\n";
echo "Cliente: joao@teste.com               / Teste@123\n";
echo "Cliente: maria@teste.com              / Teste@123\n\n";
echo "IMPORTANTE: Delete o arquivo public/create_test_users.php do servidor!\n</pre>";
