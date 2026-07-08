<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Models\UserModel;
use Maia\Models\OrderModel;
use Maia\Services\BrevoService;

class CustomerController extends BaseController
{
    public function index(array $params = []): void
    {
        Auth::requireAdminRole();

        $page    = max(1, (int)($_GET['pagina'] ?? 1));
        $filters = [
            'search'  => $_GET['busca']     ?? '',
            'segment' => $_GET['segmento']  ?? '',
            'opt_in'  => $_GET['opt_in']    ?? '',
            'tag'     => $_GET['tag']       ?? '',
            'spent_above_value' => $_GET['gastou_acima'] ?? '',
        ];
        if ($filters['spent_above_value'] !== '') {
            $filters['segment'] = 'spent_above';
        }

        $model     = new UserModel();
        $customerList = $model->getList($filters, $page, 30);
        $customers    = $customerList['items'] ?? [];
        $total        = (int)($customerList['total'] ?? $model->countList($filters));

        $this->render('admin/customers/index', [
            'pageTitle'  => 'Clientes | Admin WM',
            'customers'  => $customers,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => (int)ceil($total / 30),
            'filters'    => $filters,
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function show(array $params): void
    {
        Auth::requireAdminRole();

        $id   = (int)($params['id'] ?? 0);
        $user = (new UserModel())->findById($id);

        if (!$user) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $orders = (new OrderModel())->getByUser($id, 1, 20);

        $this->render('admin/customers/show', [
            'pageTitle' => $user['name'] . ' | Clientes | Admin WM',
            'customer'  => $user,
            'orders'    => $orders,
        ], 'admin');
    }

    public function export(array $params = []): void
    {
        Auth::requireAdminRole();

        $filters = [
            'search'  => $_GET['busca']     ?? '',
            'segment' => $_GET['segmento']  ?? '',
            'opt_in'  => $_GET['opt_in']    ?? '',
            'tag'     => $_GET['tag']       ?? '',
            'spent_above_value' => $_GET['gastou_acima'] ?? '',
        ];
        if ($filters['spent_above_value'] !== '') {
            $filters['segment'] = 'spent_above';
        }

        $result = (new UserModel())->getList($filters, 1, 100000);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="clientes-' . date('Ymd') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Nome', 'Email', 'Telefone', 'Cidade', 'Estado', 'Tag', 'Pedidos', 'Total gasto', 'Ultima compra', 'Opt-in', 'Cadastro']);

        foreach ($result['items'] as $row) {
            fputcsv($out, [
                $row['id'],
                $row['name'],
                $row['email'],
                $row['phone'],
                $row['city'],
                $row['state'],
                $row['tag'],
                $row['total_orders'],
                number_format((float)$row['total_spent'], 2, ',', '.'),
                $row['last_purchase_at'],
                !empty($row['email_opt_in']) ? 'sim' : 'nao',
                $row['created_at'],
            ]);
        }

        fclose($out);
        exit;
    }

    public function exportXlsx(array $params = []): void
    {
        Auth::requireAdminRole();

        $filters = [
            'search'  => $_GET['busca']     ?? '',
            'segment' => $_GET['segmento']  ?? '',
            'opt_in'  => $_GET['opt_in']    ?? '',
            'tag'     => $_GET['tag']       ?? '',
            'spent_above_value' => $_GET['gastou_acima'] ?? '',
        ];
        if ($filters['spent_above_value'] !== '') {
            $filters['segment'] = 'spent_above';
        }

        $result = (new UserModel())->getList($filters, 1, 100000);
        $rows = [[
            'ID',
            'Nome',
            'Email',
            'Telefone',
            'Cidade',
            'Estado',
            'Tag',
            'Pedidos',
            'Total gasto',
            'Ultima compra',
            'Opt-in',
            'Cadastro',
        ]];

        foreach ($result['items'] as $row) {
            $rows[] = [
                (string)($row['id'] ?? ''),
                (string)($row['name'] ?? ''),
                (string)($row['email'] ?? ''),
                (string)($row['phone'] ?? ''),
                (string)($row['city'] ?? ''),
                (string)($row['state'] ?? ''),
                (string)($row['tag'] ?? ''),
                (string)($row['total_orders'] ?? '0'),
                number_format((float)($row['total_spent'] ?? 0), 2, ',', '.'),
                (string)($row['last_purchase_at'] ?? ''),
                !empty($row['email_opt_in']) ? 'sim' : 'nao',
                (string)($row['created_at'] ?? ''),
            ];
        }

        $path = $this->buildXlsx($rows);
        $filename = 'clientes-' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        @unlink($path);
        exit;
    }

    public function syncBrevo(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $filters = [
            'search' => $_GET['busca'] ?? '',
            'segment' => $_GET['segmento'] ?? '',
            'opt_in' => '1',
            'tag' => $_GET['tag'] ?? '',
            'spent_above_value' => $_GET['gastou_acima'] ?? '',
        ];
        if ($filters['spent_above_value'] !== '') {
            $filters['segment'] = 'spent_above';
        }

        $result = (new UserModel())->getList($filters, 1, 500);
        $sync = (new BrevoService())->syncContacts($result['items'] ?? []);

        $this->flash(
            $sync['failed'] > 0 ? 'error' : 'success',
            'Brevo: ' . (int)$sync['synced'] . ' contato(s) sincronizado(s), '
            . (int)$sync['failed'] . ' falha(s).'
        );
        $this->redirect('/admin/clientes?' . http_build_query($_GET));
    }

    public function create(array $params = []): void
    {
        Auth::requireAdminRole();
        $this->render('admin/customers/form', [
            'pageTitle' => 'Novo Cliente | Admin WM',
            'customer'  => null,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function store(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $name  = trim((string)($_POST['name']  ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            $this->flash('error', 'Nome, e-mail e senha são obrigatórios.');
            $this->redirect('/admin/clientes/novo');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'E-mail inválido.');
            $this->redirect('/admin/clientes/novo');
        }

        if (strlen($password) < 8) {
            $this->flash('error', 'Senha deve ter ao menos 8 caracteres.');
            $this->redirect('/admin/clientes/novo');
        }

        $model = new UserModel();
        if ($model->findByEmail($email)) {
            $this->flash('error', 'E-mail já cadastrado.');
            $this->redirect('/admin/clientes/novo');
        }

        $id = $model->create([
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone,
            'password' => $password,
        ]);

        $this->flash('success', 'Cliente criado com sucesso.');
        $this->redirect('/admin/clientes/' . $id);
    }

    public function edit(array $params): void
    {
        Auth::requireAdminRole();

        $id       = (int)($params['id'] ?? 0);
        $customer = (new UserModel())->findById($id);

        if (!$customer) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('admin/customers/form', [
            'pageTitle' => 'Editar Cliente | Admin WM',
            'customer'  => $customer,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id       = (int)($params['id'] ?? 0);
        $customer = (new UserModel())->findById($id);

        if (!$customer) {
            $this->flash('error', 'Cliente não encontrado.');
            $this->redirect('/admin/clientes');
        }

        $name  = trim((string)($_POST['name']  ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($name === '' || $email === '') {
            $this->flash('error', 'Nome e e-mail são obrigatórios.');
            $this->redirect('/admin/clientes/' . $id . '/editar');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'E-mail inválido.');
            $this->redirect('/admin/clientes/' . $id . '/editar');
        }

        $model = new UserModel();
        $data = ['name' => $name, 'email' => $email, 'phone' => $phone];

        if ($password !== '') {
            if (strlen($password) < 8) {
                $this->flash('error', 'Nova senha deve ter ao menos 8 caracteres.');
                $this->redirect('/admin/clientes/' . $id . '/editar');
            }
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $model->update($id, $data);

        $this->flash('success', 'Cliente atualizado.');
        $this->redirect('/admin/clientes/' . $id);
    }

    public function anonymize(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('error', 'Cliente invalido.');
            $this->redirect('/admin/clientes');
        }

        (new UserModel())->anonymize($id);

        $this->flash('success', 'Cliente anonimizado conforme LGPD.');
        $this->redirect('/admin/clientes');
    }

    public function updateTag(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        $tag = (string)($_POST['tag'] ?? '');
        $allowed = ['', 'vip', 'atacado', 'bloqueado'];

        if ($id <= 0 || !in_array($tag, $allowed, true)) {
            $this->flash('error', 'Tag de cliente invalida.');
            $this->redirect('/admin/clientes');
        }

        (new UserModel())->updateTag($id, $tag);

        $this->flash('success', 'Tag do cliente atualizada.');
        $this->redirect('/admin/clientes/' . $id);
    }

    private function buildXlsx(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'maia-clientes-');
        if ($path === false) {
            throw new \RuntimeException('Nao foi possivel criar arquivo temporario.');
        }

        $this->writeZip($path, [
            '[Content_Types].xml' => $this->xlsxContentTypes(),
            '_rels/.rels' => $this->xlsxRootRels(),
            'xl/workbook.xml' => $this->xlsxWorkbook(),
            'xl/_rels/workbook.xml.rels' => $this->xlsxWorkbookRels(),
            'xl/worksheets/sheet1.xml' => $this->xlsxWorksheet($rows),
        ]);

        return $path;
    }

    private function writeZip(string $path, array $entries): void
    {
        $file = fopen($path, 'wb');
        if ($file === false) {
            throw new \RuntimeException('Nao foi possivel escrever XLSX temporario.');
        }

        $centralDirectory = '';
        $offset = 0;

        foreach ($entries as $name => $contents) {
            $contents = (string)$contents;
            $name = str_replace('\\', '/', (string)$name);
            $crc = crc32($contents);
            if ($crc < 0) {
                $crc += 4294967296;
            }

            $size = strlen($contents);
            $nameLength = strlen($name);

            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0,
                0,
                0,
                0,
                $crc,
                $size,
                $size,
                $nameLength,
                0
            );

            fwrite($file, $localHeader . $name . $contents);

            $centralDirectory .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                0,
                0,
                0,
                $crc,
                $size,
                $size,
                $nameLength,
                0,
                0,
                0,
                0,
                0,
                $offset
            ) . $name;

            $offset += strlen($localHeader) + $nameLength + $size;
        }

        $centralOffset = $offset;
        fwrite($file, $centralDirectory);
        $centralSize = strlen($centralDirectory);
        $entryCount = count($entries);

        fwrite($file, pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $entryCount,
            $entryCount,
            $centralSize,
            $centralOffset,
            0
        ));

        fclose($file);
    }

    private function xlsxWorksheet(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $xml .= '<row r="' . $excelRow . '">';
            foreach ($row as $columnIndex => $value) {
                $cell = $this->xlsxColumnName($columnIndex + 1) . $excelRow;
                $xml .= '<c r="' . $cell . '" t="inlineStr"><is><t>'
                    . $this->xmlEscape((string)$value)
                    . '</t></is></c>';
            }
            $xml .= '</row>';
        }

        return $xml . '</sheetData></worksheet>';
    }

    private function xlsxColumnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)) . $name;
            $number = intdiv($number, 26);
        }
        return $name;
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function xlsxContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';
    }

    private function xlsxRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function xlsxWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Clientes" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function xlsxWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';
    }
}
