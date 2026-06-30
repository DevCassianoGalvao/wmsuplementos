<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;

class UploadDiagnosticController extends BaseController
{
    public function index(array $params = []): void
    {
        Auth::requireAdminRole();

        $uploadsBase = ROOT_PATH . '/public/uploads/products';
        $info = [];

        // ROOT_PATH
        $info['ROOT_PATH'] = defined('ROOT_PATH') ? ROOT_PATH : '(não definido)';

        // PHP limits
        $info['upload_max_filesize'] = ini_get('upload_max_filesize');
        $info['post_max_size']       = ini_get('post_max_size');
        $info['memory_limit']        = ini_get('memory_limit');

        // GD
        $gdInfo = function_exists('gd_info') ? gd_info() : [];
        $info['gd_enabled']        = function_exists('imagecreate') ? 'Sim' : 'Não';
        $info['gd_webp_encode']    = !empty($gdInfo['WebP Support']) ? 'Sim' : 'Não';
        $info['imagewebp_exists']  = function_exists('imagewebp') ? 'Sim' : 'Não';

        // Diretórios
        $sizes = ['original', 'large', 'medium', 'thumb', 'small'];
        $dirs  = [];
        foreach ($sizes as $size) {
            $path = $uploadsBase . '/' . $size;
            $dirs[$size] = [
                'path'     => $path,
                'exists'   => is_dir($path),
                'writable' => is_writable($path),
                'files'    => is_dir($path) ? count(glob($path . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: []) : 0,
            ];
        }

        // Últimas 10 imagens no DB
        try {
            $recent = db()->query(
                'SELECT pi.id, pi.product_id, pi.filename_webp, pi.filename, pi.is_main, pi.created_at,
                        p.name AS product_name
                   FROM product_images pi
                   LEFT JOIN products p ON p.id = pi.product_id
                  ORDER BY pi.id DESC
                  LIMIT 10'
            )->fetchAll();
        } catch (\Throwable) {
            $recent = [];
        }

        // Para cada imagem recente, verifica se arquivo existe em disco
        foreach ($recent as &$row) {
            $path = (string)($row['filename_webp'] ?? $row['filename'] ?? '');
            if ($path !== '' && str_starts_with($path, '/uploads/')) {
                $full = ROOT_PATH . '/public' . $path;
                $row['_disk_exists'] = is_file($full);
                $row['_disk_path']   = $full;
            } else {
                $row['_disk_exists'] = false;
                $row['_disk_path']   = '(caminho inválido: ' . $path . ')';
            }
        }
        unset($row);

        $this->render('admin/diagnostic/uploads', [
            'pageTitle' => 'Diagnóstico de Uploads | Admin',
            'info'      => $info,
            'dirs'      => $dirs,
            'recent'    => $recent,
        ], 'admin');
    }
}
