<div class="page-header">
    <div>
        <h1>Usuários Admin</h1>
        <p class="page-subtitle">Controle quem pode acessar o painel administrativo.</p>
    </div>
    <a href="/admin/usuarios/novo" class="btn btn-primary">Novo Usuário</a>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<table class="admin-table">
    <thead>
        <tr>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Perfil</th>
            <th>Status</th>
            <th>Ultimo login</th>
            <th>Acoes</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($user['role'] === 'admin' ? 'Admin' : 'Operador', ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <span class="<?= !empty($user['active']) ? 'status-pago' : 'status-cancelado' ?>">
                    <?= !empty($user['active']) ? 'Ativo' : 'Inativo' ?>
                </span>
            </td>
            <td><?= htmlspecialchars($user['last_login'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><a href="/admin/usuarios/<?= (int)$user['id'] ?>" class="btn btn-sm btn-outline">Editar</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
        <tr><td colspan="6" class="empty-state">Nenhum usuario encontrado.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
