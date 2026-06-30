<?php

declare(strict_types=1);

namespace Maia\Services;

/**
 * Processa uploads de imagem: valida MIME, redimensiona e converte para WebP.
 * Requer extensão GD compilada com suporte a WebP (padrão no PHP 8.x).
 *
 * Estrutura de saída:
 *   public/uploads/{subdir}/large/{hash}.webp   (1200px)
 *   public/uploads/{subdir}/medium/{hash}.webp  (800px)  ← path armazenado no DB
 *   public/uploads/{subdir}/thumb/{hash}.webp   (400px)
 *   public/uploads/{subdir}/small/{hash}.webp   (200px)
 *   public/uploads/{subdir}/original/{hash}.jpg (arquivo original mantido)
 */
class ImageService
{
    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    private const SIZES = [
        'large'  => [1200, 1200],
        'medium' => [800,  800],
        'thumb'  => [400,  400],
        'small'  => [200,  200],
    ];

    private const WEBP_QUALITY = 82;

    /**
     * Processa arquivo do array $_FILES['field'].
     *
     * @param  array  $file    Entrada de $_FILES
     * @param  string $subdir  Subdiretório dentro de uploads/ (ex: 'products')
     * @return array  ['medium' => '/uploads/...', 'thumb' => '...', 'large' => '...', 'small' => '...']
     * @throws \RuntimeException em falha de validação ou processamento
     */
    public static function processUpload(array $file, string $subdir = 'products'): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Erro no upload: código ' . ($file['error'] ?? '?'));
        }

        $tmpPath = $file['tmp_name'] ?? '';
        if (!is_uploaded_file($tmpPath)) {
            throw new \RuntimeException('Arquivo inválido.');
        }

        // Valida MIME pelo conteúdo real — não pela extensão
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($tmpPath);

        if (!isset(self::ALLOWED_MIME[$mime])) {
            throw new \RuntimeException("Tipo de arquivo não permitido: {$mime}");
        }

        // Verifica suporte WebP no GD
        if (!function_exists('imagewebp')) {
            throw new \RuntimeException('GD não compilado com suporte a WebP.');
        }

        $hash = bin2hex(random_bytes(16));
        $ext  = self::ALLOWED_MIME[$mime];

        $uploadsBase = defined('ROOT_PATH')
            ? ROOT_PATH . '/public/uploads/' . $subdir
            : __DIR__ . '/../../public/uploads/' . $subdir;

        // Cria diretórios
        foreach (array_merge(['original'], array_keys(self::SIZES)) as $size) {
            $dir = $uploadsBase . '/' . $size;
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new \RuntimeException(
                        "Não foi possível criar diretório de upload: $dir. Verifique as permissões do servidor."
                    );
                }
            }
            @chmod($dir, 0755);
        }

        // Salva original (mantido para eventual reprocessamento)
        $originalPath = $uploadsBase . '/original/' . $hash . '.' . $ext;
        if (!move_uploaded_file($tmpPath, $originalPath)) {
            throw new \RuntimeException('Falha ao mover arquivo enviado.');
        }
        @chmod($originalPath, 0644);

        // Cria GdImage a partir do original
        $src = self::createFromFile($originalPath, $mime);
        if ($src === null) {
            throw new \RuntimeException('Não foi possível abrir imagem com GD.');
        }

        $paths = [];
        foreach (self::SIZES as $sizeName => [$maxW, $maxH]) {
            $resized = self::resize($src, $maxW, $maxH);
            $webpPath = $uploadsBase . '/' . $sizeName . '/' . $hash . '.webp';
            self::saveWebP($resized, $webpPath);
            imagedestroy($resized);

            $paths[$sizeName] = '/uploads/' . $subdir . '/' . $sizeName . '/' . $hash . '.webp';
        }

        imagedestroy($src);

        return $paths;
    }

    /**
     * Remove todas as variantes de tamanho de uma imagem pelo path do DB.
     * Aceita qualquer tamanho (medium, thumb, etc.) e apaga todos.
     */
    public static function deleteAll(string $dbPath): void
    {
        if ($dbPath === '' || !str_starts_with($dbPath, '/uploads/')) {
            return;
        }

        // Extrai subdir e hash a partir do path DB (/uploads/{subdir}/{size}/{hash}.webp)
        $parts = explode('/', ltrim($dbPath, '/'));
        // parts: [0]=uploads, [1]=subdir, [2]=size, [3]=file
        if (count($parts) < 4) {
            return;
        }

        $subdir   = $parts[1];
        $filename = $parts[3];
        $base     = defined('ROOT_PATH')
            ? ROOT_PATH . '/public/uploads/' . $subdir
            : __DIR__ . '/../../public/uploads/' . $subdir;

        foreach (array_keys(self::SIZES) as $size) {
            $file = $base . '/' . $size . '/' . $filename;
            if (is_file($file)) {
                unlink($file);
            }
        }

        // Apaga original (qualquer extensão)
        $hash = pathinfo($filename, PATHINFO_FILENAME);
        foreach (self::ALLOWED_MIME as $ext) {
            $orig = $base . '/original/' . $hash . '.' . $ext;
            if (is_file($orig)) {
                unlink($orig);
                break;
            }
        }
    }

    // ── Internos ──────────────────────────────────────────────────────────────

    private static function createFromFile(string $path, string $mime): ?\GdImage
    {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif'  => @imagecreatefromgif($path),
            default      => null,
        } ?: null;
    }

    private static function resize(\GdImage $src, int $maxW, int $maxH): \GdImage
    {
        $srcW = imagesx($src);
        $srcH = imagesy($src);

        $ratio  = min($maxW / $srcW, $maxH / $srcH, 1.0); // nunca amplia
        $dstW   = (int)round($srcW * $ratio);
        $dstH   = (int)round($srcH * $ratio);

        $dst = imagecreatetruecolor($dstW, $dstH);

        // Preserva transparência (PNG/WebP)
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $transparent);
        imagealphablending($dst, true);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        return $dst;
    }

    private static function saveWebP(\GdImage $img, string $path, int $quality = self::WEBP_QUALITY): void
    {
        imagewebp($img, $path, $quality);
        // Otimiza permissões
        @chmod($path, 0644);
    }

    public static function repairPublicPermissions(string $dbPath): void
    {
        if ($dbPath === '' || !str_starts_with($dbPath, '/uploads/')) {
            return;
        }

        $parts = explode('/', ltrim($dbPath, '/'));
        if (count($parts) < 4) {
            return;
        }

        $subdir = $parts[1];
        $filename = $parts[3];
        $base = defined('ROOT_PATH')
            ? ROOT_PATH . '/public/uploads/' . $subdir
            : __DIR__ . '/../../public/uploads/' . $subdir;

        foreach (array_merge(['original'], array_keys(self::SIZES)) as $dirName) {
            $dir = $base . '/' . $dirName;
            if (is_dir($dir)) {
                @chmod($dir, 0755);
            }
        }

        foreach (array_keys(self::SIZES) as $size) {
            $file = $base . '/' . $size . '/' . $filename;
            if (is_file($file)) {
                @chmod($file, 0644);
            }
        }

        $hash = pathinfo($filename, PATHINFO_FILENAME);
        foreach (self::ALLOWED_MIME as $ext) {
            $original = $base . '/original/' . $hash . '.' . $ext;
            if (is_file($original)) {
                @chmod($original, 0644);
                break;
            }
        }
    }

    public static function publicFileExists(string $dbPath): bool
    {
        $file = self::resolvePublicFile($dbPath);
        return $file !== null && is_file($file);
    }

    private static function resolvePublicFile(string $dbPath): ?string
    {
        if ($dbPath === '' || !str_starts_with($dbPath, '/uploads/')) {
            return null;
        }

        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($dbPath, '/'));
        return defined('ROOT_PATH')
            ? ROOT_PATH . '/public/' . $relative
            : __DIR__ . '/../../public/' . $relative;
    }
}
