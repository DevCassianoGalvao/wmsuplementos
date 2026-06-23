# AGENTS.md — Maia Suplementos

Este arquivo instrui o agente de IA (Claude Code / Codex) sobre como trabalhar neste projeto.
Leia este arquivo inteiro antes de escrever qualquer código.

---

## Visão geral do projeto

Plataforma de vendas própria para marca de suplementos alimentares.
Stack: PHP 8.2+ · MySQL 8+ · HTML5 · CSS3 · JavaScript Vanilla · cPanel/Apache.
Documentação completa: `docs/PRD.md` (ou `docs/PRD.docx`).

---

## Regras absolutas (nunca viole)

### PHP
- SEMPRE usar PDO com prepared statements. NUNCA concatenar variáveis em SQL.
- NUNCA usar `mysql_*` ou `mysqli_*` diretamente.
- NUNCA usar frameworks (sem Laravel, sem Slim, sem Symfony).
- NUNCA usar Composer exceto para autoload PSR-4.
- SEMPRE fazer hash de senhas com `password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12])`.
- NUNCA armazenar CPF, dados de cartão ou senha em texto puro.
- SEMPRE escapar output com `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`. NUNCA dar echo direto de input do usuário.
- SEMPRE validar MIME type real de uploads com `finfo_file()`. NUNCA confiar na extensão do arquivo.
- SEMPRE usar `session_regenerate_id(true)` após login.

### JavaScript
- JavaScript Vanilla apenas. NUNCA usar jQuery, React, Vue ou qualquer framework.
- Um arquivo JS por funcionalidade (ex: `cart.js`, `checkout.js`, `admin-products.js`).

### Banco de dados
- NUNCA rodar migrations destrutivas (DROP TABLE, TRUNCATE) sem aviso explícito no comentário do código.
- SEMPRE usar InnoDB e charset utf8mb4.
- Índices obrigatórios: definidos no schema SQL em `database/schema.sql`.

### Credenciais e segurança
- NUNCA hardcodar credenciais. SEMPRE ler de variáveis de ambiente via `$_ENV` ou `getenv()`.
- O arquivo `.env` NUNCA deve ser commitado. Está no `.gitignore`.
- Configurações ficam em `config/` lendo do `.env`.

---

## Estrutura de diretórios (respeite exatamente)

```
/
├── public/                  → document root do cPanel
│   ├── index.php
│   ├── .htaccess
│   └── assets/
│       ├── css/
│       ├── js/
│       └── uploads/
│           └── images/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   ├── Helpers/             → Auth.php, CSRF.php, Sanitizer.php, Validator.php
│   └── Services/            → MercadoPagoService.php, BrevoService.php, ImageService.php
├── admin/
│   ├── index.php
│   └── assets/
├── config/
│   ├── database.php         → lê DB_* do .env
│   ├── app.php              → lê APP_* do .env
│   └── mail.php             → lê BREVO_* do .env
├── cron/                    → scripts de cron job (não acessíveis publicamente)
├── database/
│   └── schema.sql           → schema completo do banco
├── docs/                    → PRD e documentação
├── logs/                    → fora do document root
├── .env.example             → template sem valores reais
├── .env                     → NUNCA commitar (está no .gitignore)
├── .gitignore
└── AGENTS.md
```

---

## Convenções de nomenclatura

| Elemento | Convenção | Exemplo |
|---|---|---|
| Tabelas SQL | snake_case plural | `order_items`, `product_images` |
| Classes PHP | PascalCase | `ProductController`, `OrderModel` |
| Métodos PHP | camelCase | `getBySlug()`, `createOrder()` |
| Variáveis PHP | camelCase | `$cartItems`, `$totalAmount` |
| Arquivos PHP | PascalCase | `ProductController.php` |
| Funções JS | camelCase | `addToCart()`, `validateCoupon()` |
| Classes CSS | kebab-case | `.product-card`, `.btn-primary` |
| URLs | kebab-case | `/meu-carrinho`, `/finalizar-compra` |
| Constantes PHP | UPPER_SNAKE | `MAX_UPLOAD_SIZE`, `DB_HOST` |

---

## Ordem de implementação

Siga esta ordem. Não pule etapas.

1. **Schema SQL** — criar `database/schema.sql` completo conforme PRD (22 tabelas)
2. **Config e bootstrap** — `config/database.php`, `config/app.php`, autoloader PSR-4, roteador simples
3. **Helpers core** — `Auth.php`, `CSRF.php`, `Sanitizer.php`, `Validator.php`
4. **Models** — `ProductModel`, `UserModel`, `OrderModel`, `CategoryModel`
5. **Área pública** — Home → Categoria → Produto → Carrinho → Checkout
6. **Webhook Mercado Pago** — implementar e testar com sandbox ANTES de continuar
7. **Painel admin** — Dashboard → Produtos → Estoque → Pedidos → Clientes
8. **Comunicação** — integração Brevo + `email_queue` + cron jobs
9. **Marketing** — Central de Scripts + UTM Builder
10. **Avaliações + Notificações**
11. **Segurança** — headers HTTP, rate limiting, audit log
12. **Performance** — WebP automático, cache, minificação

---

## O que fazer quando tiver dúvida

- Consultar `docs/PRD.md` para requisitos de negócio.
- Consultar este arquivo para decisões técnicas.
- Se algo não estiver coberto nos dois documentos, escolher a opção mais simples e segura e documentar o motivo em comentário no código.
- NUNCA inventar integrações ou dependências não listadas no PRD.
