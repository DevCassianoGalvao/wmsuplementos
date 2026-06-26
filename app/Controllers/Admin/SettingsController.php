<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\Cache;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;

class SettingsController extends BaseController
{
    private const SETTING_KEYS = [
        'store_name',
        'store_whatsapp',
        'store_email',
        'store_address',
        'store_color_primary',
        'shipping_flat_rate',
        'free_shipping_above',
        'stock_alert_min',
        'hero_label',
        'hero_title_before',
        'hero_title_emphasis',
        'hero_title_after',
        'hero_subtitle',
        'hero_image',
        'hero_primary_label',
        'hero_primary_url',
        'hero_secondary_label',
        'hero_secondary_url',
        'home_faq_enabled',
    ];

    public function index(array $params = []): void
    {
        Auth::requireAdminRole();

        $this->render('admin/settings/index', [
            'pageTitle' => 'Configuracoes | Admin Maia',
            'settings'  => $this->getSettings(),
            'pages'     => $this->getLegalPages(),
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function update(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        foreach (self::SETTING_KEYS as $key) {
            $value = $_POST[$key] ?? '';
            if ($key === 'store_email') {
                $value = Sanitizer::email($value);
            } elseif ($key === 'free_shipping_above' || $key === 'shipping_flat_rate') {
                $value = number_format((float)str_replace(',', '.', (string)$value), 2, '.', '');
            } elseif ($key === 'stock_alert_min') {
                $value = (string)max(0, (int)$value);
            } elseif ($key === 'home_faq_enabled') {
                $value = isset($_POST[$key]) ? '1' : '0';
            } else {
                $value = Sanitizer::plainText((string)$value);
            }

            db()->prepare(
                'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
            )->execute([$key, $value !== '' ? $value : null]);
        }

        $this->updateLegalPage('politica-de-privacidade', 'Politica de Privacidade', $_POST['privacy_policy'] ?? '');
        $this->updateLegalPage('termos-de-uso', 'Termos de Uso', $_POST['terms_of_use'] ?? '');
        $this->updateLegalPage('faq', 'Perguntas Frequentes', $_POST['faq_content'] ?? '');

        Cache::flush('home_');

        $this->flash('success', 'Configuracoes atualizadas.');
        $this->redirect('/admin/configuracoes');
    }

    private function getSettings(): array
    {
        $rows = db()->query('SELECT `key`, `value` FROM settings')->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }

    private function getLegalPages(): array
    {
        $stmt = db()->prepare(
            "SELECT slug, title, content
               FROM pages
              WHERE slug IN ('politica-de-privacidade', 'termos-de-uso', 'faq')"
        );
        $stmt->execute();

        $pages = [];
        foreach ($stmt->fetchAll() as $page) {
            $pages[$page['slug']] = $page;
        }
        return $pages;
    }

    private function updateLegalPage(string $slug, string $title, string $content): void
    {
        $content = $this->sanitizeLegalHtml($content);

        db()->prepare(
            'INSERT INTO pages (slug, title, content, active)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), active = 1'
        )->execute([$slug, $title, $content !== '' ? $content : null]);

        $settingKey = match ($slug) {
            'politica-de-privacidade' => 'privacy_policy',
            'termos-de-uso' => 'terms_of_use',
            'faq' => 'faq_content',
            default => null,
        };

        if ($settingKey !== null) {
            db()->prepare(
                'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
            )->execute([$settingKey, $content !== '' ? $content : null]);
        }
    }

    private function sanitizeLegalHtml(string $content): string
    {
        $allowedTags = '<p><br><strong><b><em><i><ul><ol><li><h2><h3><h4><a>';
        $content = trim(strip_tags($content, $allowedTags));

        return preg_replace_callback('/<a\s+([^>]+)>/i', static function (array $match): string {
            if (!preg_match('/href\s*=\s*("|\')([^"\']+)\1/i', $match[1], $hrefMatch)) {
                return '<a>';
            }

            $href = $hrefMatch[2];
            if (!preg_match('#^(https?://|mailto:)#i', $href)) {
                return '<a>';
            }

            return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" rel="noopener">';
        }, $content) ?? '';
    }
}
