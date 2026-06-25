<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;

class ScriptController extends BaseController
{
    // Chaves gerenciadas e suas descrições
    private const KEYS = [
        'ga_id'       => ['label' => 'Google Analytics 4 (Measurement ID)', 'placeholder' => 'G-XXXXXXXXXX'],
        'gtm_id'      => ['label' => 'Google Tag Manager (Container ID)',    'placeholder' => 'GTM-XXXXXXX'],
        'pixel_id'    => ['label' => 'Meta Pixel (Facebook Pixel ID)',       'placeholder' => '123456789012345'],
        'custom_head' => ['label' => 'Código personalizado <head>',          'placeholder' => '<!-- scripts adicionais no <head> -->'],
        'custom_body' => ['label' => 'Código personalizado após <body>',     'placeholder' => '<!-- scripts adicionais logo após <body> -->'],
    ];

    public function index(array $params = []): void
    {
        Auth::requireAdminRole();

        $stmt = db()->query('SELECT `key`, `value`, `active` FROM scripts_config ORDER BY id ASC');
        $rows = $stmt->fetchAll();

        $scripts = [];
        foreach ($rows as $row) {
            $scripts[$row['key']] = $row;
        }

        $this->render('admin/scripts/index', [
            'pageTitle' => 'Central de Scripts | Admin Maia',
            'scripts'   => $scripts,
            'keys'      => self::KEYS,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function update(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $updated = 0;

        foreach (self::KEYS as $key => $meta) {
            $value  = $_POST[$key]               ?? '';
            $active = isset($_POST['active_' . $key]) ? 1 : 0;

            // Não ativa se não tiver valor
            if (empty(trim($value))) {
                $active = 0;
            }

            db()->prepare(
                'INSERT INTO scripts_config (`key`, `value`, `active`)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `active` = VALUES(`active`)'
            )->execute([$key, trim($value) ?: null, $active]);

            $updated++;
        }

        $this->flash('success', 'Scripts atualizados com sucesso.');
        $this->redirect('/admin/scripts');
    }
}
