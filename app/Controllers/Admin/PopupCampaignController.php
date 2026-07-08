<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;
use Maia\Models\PopupCampaignModel;
use Maia\Services\ImageService;
use PDOException;

class PopupCampaignController extends BaseController
{
    private PopupCampaignModel $model;
    private ?string $uploadError = null;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PopupCampaignModel();
    }

    public function index(array $params = []): void
    {
        Auth::requireAdminRole();

        $tableMissing = !$this->model->tableExists();
        $campaigns = [];
        if (!$tableMissing) {
            $campaigns = $this->model->getAll();
        }

        $this->render('admin/popups/index', [
            'pageTitle'    => 'Popups | Admin WM',
            'campaigns'    => $campaigns,
            'tableMissing' => $tableMissing,
            'flash'        => $this->getFlash(),
        ], 'admin');
    }

    public function create(array $params = []): void
    {
        Auth::requireAdminRole();

        $this->render('admin/popups/form', [
            'pageTitle' => 'Novo Popup | Admin WM',
            'campaign'  => null,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function store(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $data = $this->extractData();
        if ($data['title'] === '') {
            $this->flash('error', 'Informe o titulo da campanha.');
            $this->redirect('/admin/configuracoes/popups/novo');
        }

        if ($data['slug'] === '') {
            $data['slug'] = Sanitizer::slug($data['title']);
        }

        $data['image'] = $this->handleImageUpload();
        if ($this->uploadError !== null) {
            $this->flash('error', $this->uploadError);
            $this->redirect('/admin/configuracoes/popups/novo');
        }

        try {
            $this->model->create($data);
            $this->flash('success', 'Popup criado.');
            $this->redirect('/admin/configuracoes/popups');
        } catch (PDOException $e) {
            error_log('[PopupCampaignController] create falhou: ' . $e->getMessage());
            $this->flash('error', 'Banco ainda sem tabela de popups. Rode database/popup_campaigns.sql.');
            $this->redirect('/admin/configuracoes/popups');
        }
    }

    public function edit(array $params): void
    {
        Auth::requireAdminRole();

        $campaign = $this->model->findById((int)($params['id'] ?? 0));
        if (!$campaign) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('admin/popups/form', [
            'pageTitle' => 'Editar Popup | Admin WM',
            'campaign'  => $campaign,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        $campaign = $this->model->findById($id);
        if (!$campaign) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $data = $this->extractData();
        if ($data['title'] === '') {
            $this->flash('error', 'Informe o titulo da campanha.');
            $this->redirect('/admin/configuracoes/popups/' . $id);
        }

        if ($data['slug'] === '') {
            $data['slug'] = Sanitizer::slug($data['title']);
        }

        $newImage = $this->handleImageUpload();
        if ($this->uploadError !== null) {
            $this->flash('error', $this->uploadError);
            $this->redirect('/admin/configuracoes/popups/' . $id);
        }
        $data['image'] = $newImage ?? ($campaign['image'] ?? null);

        if (!empty($_POST['remove_image'])) {
            ImageService::deleteAll((string)($campaign['image'] ?? ''));
            $data['image'] = null;
        } elseif ($newImage && !empty($campaign['image'])) {
            ImageService::deleteAll((string)$campaign['image']);
        }

        $this->model->update($id, $data);
        $this->flash('success', 'Popup atualizado.');
        $this->redirect('/admin/configuracoes/popups');
    }

    public function toggle(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $this->model->toggleActive((int)($params['id'] ?? 0));
        $this->flash('success', 'Status do popup alterado.');
        $this->redirect('/admin/configuracoes/popups');
    }

    public function delete(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $campaign = $this->model->delete((int)($params['id'] ?? 0));
        if (!empty($campaign['image'])) {
            ImageService::deleteAll((string)$campaign['image']);
        }

        $this->flash('success', 'Popup removido.');
        $this->redirect('/admin/configuracoes/popups');
    }

    private function extractData(): array
    {
        $mode = (string)($_POST['mode'] ?? 'message');
        if (!in_array($mode, ['image', 'message'], true)) {
            $mode = 'message';
        }

        return [
            'title'       => Sanitizer::plainText($_POST['title'] ?? ''),
            'slug'        => Sanitizer::slug($_POST['slug'] ?? ''),
            'mode'        => $mode,
            'image'       => null,
            'target_url'  => $this->cleanUrl($_POST['target_url'] ?? ''),
            'message'     => Sanitizer::plainText($_POST['message'] ?? ''),
            'coupon_code' => strtoupper(Sanitizer::plainText($_POST['coupon_code'] ?? '')),
            'cta_label'   => Sanitizer::plainText($_POST['cta_label'] ?? ''),
            'active'      => isset($_POST['active']) ? 1 : 0,
            'start_at'    => $this->cleanDateTime($_POST['start_at'] ?? ''),
            'end_at'      => $this->cleanDateTime($_POST['end_at'] ?? ''),
            'show_once'   => isset($_POST['show_once']) ? 1 : 0,
        ];
    }

    private function handleImageUpload(): ?string
    {
        if (empty($_FILES['image']['tmp_name']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            return null;
        }

        try {
            $paths = ImageService::processUpload($_FILES['image'], 'popups');
            return $paths['medium'] ?? null;
        } catch (\RuntimeException $e) {
            error_log('[PopupCampaignController] imagem invalida: ' . $e->getMessage());
            $this->uploadError = 'Imagem invalida. Envie JPG, PNG, GIF ou WebP.';
            return null;
        }
    }

    private function cleanUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return Sanitizer::plainText($url);
        }

        if (preg_match('#^https?://#i', $url)) {
            return filter_var($url, FILTER_SANITIZE_URL) ?: '';
        }

        return '';
    }

    private function cleanDateTime(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $dt = \DateTime::createFromFormat('Y-m-d\TH:i', $value);
        return $dt ? $dt->format('Y-m-d H:i:s') : '';
    }
}
