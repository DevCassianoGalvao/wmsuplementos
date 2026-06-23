<?php
/**
 * Cria usuários de teste com hashes bcrypt gerados no servidor.
 * Execute UMA vez via: cassianogalvao.com.br/maiasuplementos/create_test_users.php
 * DELETE este arquivo após usar!
 */

// Bloqueia acesso após execução marcada
$lockFile = __DIR__ . '/.users_created.lock';
if (file_exists($lockFile)) {
    http_response_code(403);
    exit('Usuários já criados. Delete este arquivo do servidor.');
}

// Carrega .env
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        putenv(trim($k) . '=' . trim($v, " \t\"'"));
    }
}

// Conecta ao banco
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST') ?: 'localhost',
        getenv('DB_PORT') ?: '3306',
        getenv('DB_NAME') ?: ''
    );
    $pdo = new PDO($dsn, getenv('DB_USER') ?: '', getenv('DB_PASS') ?: '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    exit('Erro de conexão: ' . htmlspecialchars($e->getMessage()));
}

$users = [
    ['João da Silva',  'joao@teste.com',  'Teste@123', '(11) 98765-4321'],
    ['Maria Oliveira', 'maria@teste.com', 'Teste@123', '(21) 99876-5432'],
];

$stmt = $pdo->prepare(
    'INSERT IGNORE INTO users (name, email, password_hash, phone, active) VALUES (?, ?, ?, ?, 1)'
);

$results = [];
foreach ($users as [$name, $email, $pass, $phone]) {
    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt->execute([$name, $email, $hash, $phone]);
    $results[] = "$name ($email) — " . ($pdo->lastInsertId() > 0 ? 'criado' : 'já existia');
}

// Cria lock para impedir re-execução
file_put_contents($lockFile, date('c'));

echo '<pre>Usuários processados:' . PHP_EOL;
foreach ($results as $r) echo '  ' . $r . PHP_EOL;
echo PHP_EOL . 'IMPORTANTE: Delete o arquivo database/create_test_users.php do servidor agora!</pre>';
