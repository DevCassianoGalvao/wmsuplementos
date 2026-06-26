-- =============================================================================
-- Maia Suplementos — Dados fictícios para demonstração
-- Importar APÓS schema.sql
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Marcas
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO brands (name, slug, active) VALUES
('Growth Supplements', 'growth-supplements', 1),
('Optimum Nutrition',  'optimum-nutrition',  1),
('Max Titanium',       'max-titanium',       1),
('Integral Medica',    'integral-medica',    1),
('MuscleTech',         'muscletech',         1);

-- -----------------------------------------------------------------------------
-- Categorias
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO categories (name, slug, seo_title, seo_description, active, sort_order) VALUES
('Proteínas',        'proteinas',       'Proteínas | Maia Suplementos',         'Whey Protein, Albumina, Proteína Vegana e muito mais.',    1, 1),
('Creatina',         'creatina',        'Creatina | Maia Suplementos',          'Creatina Monohidratada e fórmulas avançadas.',             1, 2),
('Pré-Treino',       'pre-treino',      'Pré-Treino | Maia Suplementos',        'Maximize seu desempenho com os melhores pré-treinos.',     1, 3),
('Aminoácidos',      'aminoacidos',     'Aminoácidos | Maia Suplementos',       'BCAA, Glutamina, EAA e mais.',                             1, 4),
('Vitaminas',        'vitaminas',       'Vitaminas e Minerais | Maia',          'Multivitamínicos e suplementos para saúde geral.',         1, 5),
('Hipercalóricos',   'hipercaloricos',  'Hipercalóricos | Maia Suplementos',    'Ganho de massa com os melhores hipercalóricos.',           1, 6),
('Termogênicos',     'termogenicos',    'Termogênicos | Maia Suplementos',      'Acelere seu metabolismo e queime gordura.',                1, 7),
('Barras e Snacks',  'barras-snacks',   'Barras e Snacks | Maia Suplementos',   'Lanches saudáveis e nutritivos para qualquer hora.',      1, 8);

-- -----------------------------------------------------------------------------
-- Produtos
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO products
  (name, slug, sku, category_id, brand_id, price, price_sale, stock, stock_alert_threshold, description, active, featured, bestseller)
VALUES
-- Proteínas
('Whey Protein Concentrado 900g Baunilha',   'whey-protein-concentrado-900g-baunilha',  'WPC-900-BAN', 1, 1,  89.90,  69.90, 120, 10, 'Whey Protein Concentrado com 24g de proteína por dose. Ideal para recuperação muscular e ganho de massa. Sabor Baunilha cremoso e delicioso.', 1, 1, 1),
('Whey Protein Isolado 900g Chocolate',      'whey-protein-isolado-900g-chocolate',     'WPI-900-CHO', 1, 2, 149.90, 119.90,  80, 10, 'Whey Protein Isolado com 27g de proteína por dose e mínimo de gordura e lactose. Perfeito para definição muscular.', 1, 1, 1),
('Whey Protein Concentrado 1,8kg Morango',   'whey-protein-concentrado-1800g-morango',  'WPC-1800-MOR', 1, 3, 159.90, 139.90,  60, 8,  'A escolha econômica para quem não abre mão da qualidade. 24g de proteína por dose, sabor Morango.', 1, 0, 1),
('Proteína Vegana Ervilha 500g',             'proteina-vegana-ervilha-500g',            'VEG-500-EAR', 1, 4,  79.90,  null,  40, 5,  'Proteína 100% vegetal derivada da ervilha. Livre de glúten e lactose, ideal para veganos e intolerantes.', 1, 0, 0),
('Albumina 500g Natural',                    'albumina-500g-natural',                   'ALB-500-NAT', 1, 1,  39.90,  null,  90, 10, 'Proteína do ovo desidratada. Alto valor biológico e absorção lenta, perfeita para ser consumida antes de dormir.', 1, 0, 0),

-- Creatina
('Creatina Monohidratada 300g',              'creatina-monohidratada-300g',             'CRE-300',     2, 2,  59.90,  44.90, 150, 15, 'Creatina pura Monohidratada para aumento de força e potência muscular. Sem adição de corantes ou saborizantes.', 1, 1, 1),
('Creatina HCl 120 cápsulas',               'creatina-hcl-120-capsulas',               'CRE-HCL-120', 2, 5,  89.90,  null,  50, 8,  'Cloridrato de Creatina: maior solubilidade e absorção, sem retenção de líquidos. 120 cápsulas.', 1, 0, 0),

-- Pré-Treino
('Pré-Treino Dark Energy 300g',              'pre-treino-dark-energy-300g',             'PRE-DARK-300', 3, 3, 109.90,  89.90,  70, 8,  'Fórmula explosiva com Cafeína, Beta-Alanina, Arginina e L-Citrulina. Energia máxima para seu treino.', 1, 1, 1),
('Pré-Treino Black Skull 200g',             'pre-treino-black-skull-200g',             'PRE-BS-200',  3, 4,  79.90,  null,  45, 5,  'Combinação poderosa de estimulantes e vasodilatadores para treinos intensos.', 1, 0, 0),

-- Aminoácidos
('BCAA 2:1:1 210 cápsulas',                 'bcaa-211-210-capsulas',                   'BCAA-210',    4, 2,  69.90,  54.90, 100, 10, '21 aminoácidos de cadeia ramificada na proporção ideal 2:1:1 (Leucina, Isoleucina e Valina). Anticanabólico eficiente.', 1, 1, 0),
('Glutamina 300g',                           'glutamina-300g',                          'GLU-300',     4, 1,  49.90,  null,  80, 10, 'L-Glutamina pura para recuperação muscular, fortalecimento do sistema imune e saúde intestinal.', 1, 0, 0),
('EAA Essential Amino Acids 300g',           'eaa-essential-amino-acids-300g',          'EAA-300',     4, 5,  99.90,  79.90,  35, 5,  'Todos os aminoácidos essenciais em um único produto. Suporte completo para síntese proteica.', 1, 0, 0),

-- Vitaminas
('Multivitamínico Sport 60 comprimidos',     'multivitaminico-sport-60-comprimidos',    'MULTI-60',    5, 3,  39.90,  null, 200, 20, 'Complexo vitamínico e mineral completo desenvolvido para atletas. 60 comprimidos revestidos.', 1, 0, 1),
('Vitamina C 1000mg 60 cápsulas',           'vitamina-c-1000mg-60-capsulas',           'VTC-1000-60', 5, 4,  29.90,  null, 180, 15, 'Vitamina C 1000mg com liberação prolongada. Antioxidante poderoso e suporte ao sistema imunológico.', 1, 0, 0),
('Ômega 3 1000mg 120 cápsulas',             'omega-3-1000mg-120-capsulas',             'OMG-1000-120',5, 1,  49.90,  39.90, 110, 10, 'Óleo de peixe de alta qualidade com EPA e DHA para saúde cardiovascular e redução de inflamação.', 1, 0, 0),

-- Hipercalóricos
('Hipercalórico 3kg Chocolate',              'hipercalorico-3kg-chocolate',             'HIPO-3KG-CHO',6, 3, 129.90, 99.90,  40, 5,  'Ganhe massa muscular com mais de 1000 kcal por dose. Rico em carboidratos complexos e proteínas. Sabor Chocolate.', 1, 0, 0),
('Mass Gainer 1,5kg Baunilha',              'mass-gainer-1500g-baunilha',              'MASS-1500-BAN',6, 2,  89.90,  null,  55, 8,  'Fórmula balanceada para ganho de peso de qualidade. Carboidratos + Proteínas + Vitaminas.', 1, 0, 0),

-- Termogênicos
('Termogênico Lipo 60 cápsulas',            'termogenico-lipo-60-capsulas',            'TERM-60',     7, 4,  59.90,  49.90, 85,  10, 'Fórmula termogênica com Cafeína, Chá Verde, Gengibre e Pimenta Caiena para acelerar o metabolismo.', 1, 1, 0),
('Fat Burner Extreme 120 cápsulas',         'fat-burner-extreme-120-capsulas',         'FAT-120',     7, 5,  89.90,  null,  30, 5,  'Fórmula avançada para queima de gordura. Sem efeito rebote.', 1, 0, 0),

-- Barras
('Barra de Proteína Chocolate 12 unidades', 'barra-proteina-chocolate-12un',           'BAR-CHO-12',  8, 1,  79.90,  69.90,  60, 8,  'Barras proteicas com 20g de proteína e apenas 5g de açúcar. Caixa com 12 unidades. Sabor Chocolate ao Leite.', 1, 1, 0),
('Barra de Cereal Integral 24 unidades',    'barra-cereal-integral-24un',              'BAR-CER-24',  8, 3,  49.90,  null,   70, 8,  'Barras integrais com aveia e mel. Fonte de carboidratos de qualidade para energia prolongada.', 1, 0, 0);

-- -----------------------------------------------------------------------------
-- Combos
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO combos (name, slug, description, price, active) VALUES
('Kit Definição Total',
 'kit-definicao-total',
 'Tudo que você precisa para definir: Whey Isolado + Creatina + Termogênico. Economize mais de R$ 50.',
 199.90, 1),
('Kit Ganho de Massa',
 'kit-ganho-de-massa',
 'A combinação perfeita para ganhar massa: Hipercalórico + Whey Concentrado + BCAA.',
 239.90, 1),
('Kit Iniciante',
 'kit-iniciante',
 'Comece sua jornada fitness com o essencial: Whey Concentrado + Creatina + Multivitamínico.',
 159.90, 1);

-- Itens dos combos (usando IDs sequenciais — ajustar se necessário)
INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1
FROM combos c, products p
WHERE c.slug = 'kit-definicao-total' AND p.slug = 'whey-protein-isolado-900g-chocolate';

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1
FROM combos c, products p
WHERE c.slug = 'kit-definicao-total' AND p.slug = 'creatina-monohidratada-300g';

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1
FROM combos c, products p
WHERE c.slug = 'kit-definicao-total' AND p.slug = 'termogenico-lipo-60-capsulas';

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1
FROM combos c, products p
WHERE c.slug = 'kit-ganho-de-massa' AND p.slug = 'hipercalorico-3kg-chocolate';

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1
FROM combos c, products p
WHERE c.slug = 'kit-ganho-de-massa' AND p.slug = 'whey-protein-concentrado-900g-baunilha';

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1
FROM combos c, products p
WHERE c.slug = 'kit-ganho-de-massa' AND p.slug = 'bcaa-211-210-capsulas';

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1
FROM combos c, products p
WHERE c.slug = 'kit-iniciante' AND p.slug = 'whey-protein-concentrado-900g-baunilha';

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1
FROM combos c, products p
WHERE c.slug = 'kit-iniciante' AND p.slug = 'creatina-monohidratada-300g';

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1
FROM combos c, products p
WHERE c.slug = 'kit-iniciante' AND p.slug = 'multivitaminico-sport-60-comprimidos';

-- -----------------------------------------------------------------------------
-- Cupons de desconto
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO coupons (code, type, value, min_order, max_uses, active, expires_at) VALUES
('BEMVINDO10',  'percent', 10.00,  0.00, 1000, 1, DATE_ADD(NOW(), INTERVAL 1 YEAR)),
('PROMO20',     'percent', 20.00, 150.00,  200, 1, DATE_ADD(NOW(), INTERVAL 6 MONTH)),
('FRETE30',     'fixed',   30.00,  99.00, null, 1, DATE_ADD(NOW(), INTERVAL 3 MONTH)),
('MAIA50OFF',   'fixed',   50.00, 299.00,   50, 1, DATE_ADD(NOW(), INTERVAL 2 MONTH));

-- -----------------------------------------------------------------------------
-- Usuário cliente de teste
-- Execute database/create_test_users.php no servidor para criar com senha correta.
-- Placeholder com hash inválido — substituído pelo script PHP:
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO users (name, email, password_hash, phone, active) VALUES
('João da Silva',   'joao@teste.com',  'HASH_PENDENTE', '(11) 98765-4321', 1),
('Maria Oliveira',  'maria@teste.com', 'HASH_PENDENTE', '(21) 99876-5432', 1);

-- -----------------------------------------------------------------------------
-- Pedidos fictícios
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO orders
  (user_id, customer_name, customer_email, customer_phone,
   zip_code, address, address_number, city, state,
   subtotal, discount, total, payment_method, payment_id, status)
VALUES
(1, 'João da Silva',  'joao@teste.com',  '(11) 98765-4321',
 '01310-100', 'Avenida Paulista', '1000', 'São Paulo', 'SP',
 139.80, 0.00, 139.80, 'pix',    'PAY-001-DEMO', 'entregue'),
(1, 'João da Silva',  'joao@teste.com',  '(11) 98765-4321',
 '01310-100', 'Avenida Paulista', '1000', 'São Paulo', 'SP',
 179.80, 17.98, 161.82, 'cartao', 'PAY-002-DEMO', 'enviado'),
(2, 'Maria Oliveira', 'maria@teste.com', '(21) 99876-5432',
 '20040-020', 'Rua da Assembleia', '50', 'Rio de Janeiro', 'RJ',
 89.90, 0.00, 89.90, 'pix', 'PAY-003-DEMO', 'pago'),
(null, 'Carlos Teste', 'carlos@teste.com', '(31) 97654-3210',
 '30112-000', 'Rua Espírito Santo', '100', 'Belo Horizonte', 'MG',
 69.90, 0.00, 69.90, 'boleto', null, 'aguardando_pagamento');

-- Itens dos pedidos
INSERT IGNORE INTO order_items (order_id, product_id, product_name, price, quantity, subtotal)
SELECT o.id, p.id, p.name, p.price, 1, p.price
FROM orders o, products p
WHERE o.payment_id = 'PAY-001-DEMO' AND p.slug IN ('whey-protein-concentrado-900g-baunilha', 'creatina-monohidratada-300g');

INSERT IGNORE INTO order_items (order_id, product_id, product_name, price, quantity, subtotal)
SELECT o.id, p.id, p.name, p.price, 1, p.price
FROM orders o, products p
WHERE o.payment_id = 'PAY-002-DEMO' AND p.slug IN ('whey-protein-isolado-900g-chocolate', 'bcaa-211-210-capsulas');

INSERT IGNORE INTO order_items (order_id, product_id, product_name, price, quantity, subtotal)
SELECT o.id, p.id, p.name, p.price, 1, p.price
FROM orders o, products p
WHERE o.payment_id = 'PAY-003-DEMO' AND p.slug = 'termogenico-lipo-60-capsulas';

-- Histórico de status
INSERT IGNORE INTO order_status_history (order_id, status, note)
SELECT id, 'pago',         'Pagamento PIX confirmado.'   FROM orders WHERE payment_id = 'PAY-001-DEMO';
INSERT IGNORE INTO order_status_history (order_id, status, note)
SELECT id, 'em_preparacao','Pedido em separação.'        FROM orders WHERE payment_id = 'PAY-001-DEMO';
INSERT IGNORE INTO order_status_history (order_id, status, note)
SELECT id, 'enviado',      'Enviado via Correios.'       FROM orders WHERE payment_id = 'PAY-001-DEMO';
INSERT IGNORE INTO order_status_history (order_id, status, note)
SELECT id, 'entregue',     'Entregue ao destinatário.'   FROM orders WHERE payment_id = 'PAY-001-DEMO';

-- -----------------------------------------------------------------------------
-- Avaliações aprovadas
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO reviews (product_id, user_id, order_id, rating, comment, status, review_token)
SELECT p.id, 1, o.id, 5,
  'Produto excelente! Dissolve fácil, sabor ótimo e os resultados vieram rápido. Super recomendo!',
  'approved', MD5(CONCAT('rev-', p.slug, '-joao'))
FROM products p
JOIN orders o ON o.payment_id = 'PAY-001-DEMO'
WHERE p.slug = 'whey-protein-concentrado-900g-baunilha';

INSERT IGNORE INTO reviews (product_id, user_id, order_id, rating, comment, status, review_token)
SELECT p.id, 1, o.id, 5,
  'Já uso creatina há anos e essa é uma das melhores que já tomei. Força e recuperação melhoraram muito.',
  'approved', MD5(CONCAT('rev-', p.slug, '-joao'))
FROM products p
JOIN orders o ON o.payment_id = 'PAY-001-DEMO'
WHERE p.slug = 'creatina-monohidratada-300g';

INSERT IGNORE INTO reviews (product_id, user_id, order_id, rating, comment, status, review_token)
SELECT p.id, 2, o.id, 4,
  'Bom termogênico, senti o efeito logo nos primeiros dias. Só não coloquei 5 estrelas porque o preço poderia ser melhor.',
  'approved', MD5(CONCAT('rev-', p.slug, '-maria'))
FROM products p
JOIN orders o ON o.payment_id = 'PAY-003-DEMO'
WHERE p.slug = 'termogenico-lipo-60-capsulas';

-- -----------------------------------------------------------------------------
-- Notificações admin
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO notifications (type, title, message, `read`) VALUES
('order',         'Novo pedido recebido',          'Pedido de Carlos Teste — R$ 69,90 — aguardando pagamento.', 0),
('payment',       'Pagamento aprovado — Pedido #3', 'R$ 89,90 via PIX confirmado.', 0),
('review_requests','Avaliações pendentes',          '1 avaliação aguardando moderação.', 0),
('low_stock',     'Estoque baixo: Fat Burner',      'Fat Burner Extreme com apenas 30 unidades (alerta: 5).', 1);

-- -----------------------------------------------------------------------------
-- Páginas
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO pages (slug, title, content, active) VALUES
('sobre',
 'Sobre a Maia Suplementos',
 '<h2>Nossa História</h2><p>A Maia Suplementos nasceu da paixão pelo esporte e pelo bem-estar. Fundada em 2020, somos uma loja especializada em suplementos alimentares de alta qualidade para atletas e praticantes de atividade física.</p><h2>Nossa Missão</h2><p>Oferecer os melhores produtos com o melhor custo-benefício, acompanhado de um atendimento diferenciado e entrega rápida para todo o Brasil.</p><p>Trabalhamos apenas com marcas reconhecidas e produtos com procedência garantida.</p>',
 1),
('como-comprar',
 'Como Comprar',
 '<h2>É fácil comprar na Maia Suplementos</h2><ol><li>Escolha seus produtos e adicione ao carrinho.</li><li>Clique em "Finalizar Compra".</li><li>Preencha seus dados de entrega.</li><li>Escolha a forma de pagamento (PIX, Cartão ou Boleto).</li><li>Pronto! Você receberá a confirmação por e-mail.</li></ol>',
 1),
('formas-de-pagamento',
 'Formas de Pagamento',
 '<h2>Aceitamos:</h2><ul><li><strong>PIX</strong> — Aprovação imediata, desconto de 5%.</li><li><strong>Cartão de Crédito</strong> — Até 6x sem juros nos cartões Visa, Mastercard, Elo e Amex.</li><li><strong>Boleto Bancário</strong> — Compensação em até 3 dias úteis.</li></ul>',
 1),
('prazo-de-entrega',
 'Prazo de Entrega',
 '<h2>Prazos estimados</h2><ul><li><strong>Capitais:</strong> 2 a 4 dias úteis.</li><li><strong>Interior:</strong> 4 a 8 dias úteis.</li><li><strong>Norte e Nordeste:</strong> 6 a 12 dias úteis.</li></ul><p>Pedidos feitos até as 14h em dias úteis são despachados no mesmo dia.</p>',
 1),
('trocas-e-devolucoes',
 'Trocas e Devoluções',
 '<h2>Política de Troca</h2><p>Você tem até 7 dias após o recebimento para solicitar troca ou devolução conforme o Código de Defesa do Consumidor.</p><p>Entre em contato pelo e-mail <strong>contato@maiasuplementos.com.br</strong> ou WhatsApp informando o número do pedido e o motivo.</p>',
 1),
('faq',
 'Perguntas Frequentes',
 '<h2>Dúvidas frequentes</h2><h3>Os produtos são originais?</h3><p>Sim. Trabalhamos apenas com distribuidores autorizados e todos os produtos possuem nota fiscal.</p><h3>Vocês entregam em todo o Brasil?</h3><p>Sim, entregamos via Correios para todo o território nacional.</p><h3>Posso rastrear meu pedido?</h3><p>Sim. Assim que seu pedido for despachado, você receberá o código de rastreamento por e-mail.</p>',
 1),
('perguntas-frequentes',
 'Perguntas Frequentes',
 '<h2>Perguntas Frequentes</h2><h3>Os produtos são originais e lacrados?</h3><p>Sim. Trabalhamos somente com distribuidores autorizados e todos os produtos são enviados lacrados, com nota fiscal e procedência garantida.</p><h3>Qual o prazo de entrega?</h3><p>Capitais: 2 a 4 dias úteis. Interior: 4 a 8 dias úteis. Norte e Nordeste: 6 a 12 dias úteis. Pedidos aprovados até as 14h em dias úteis são despachados no mesmo dia.</p><h3>Quais as formas de pagamento?</h3><p>Aceitamos PIX (aprovação imediata), cartão de crédito (até 6x sem juros) e boleto bancário.</p><h3>Como funciona a troca ou devolução?</h3><p>Você tem até 7 dias após o recebimento para solicitar troca ou devolução, conforme o Código de Defesa do Consumidor. Consulte a página de <a href="/pagina/trocas-e-devolucoes">Trocas e Devoluções</a>.</p><h3>Posso rastrear meu pedido?</h3><p>Sim. Assim que o pedido for despachado, você recebe o código de rastreamento por e-mail.</p><h3>Como falo com o atendimento?</h3><p>Pelo e-mail contato@maiasuplementos.com.br ou pelo WhatsApp (22) 99869-6430.</p>',
 1);

-- -----------------------------------------------------------------------------
-- Settings
-- -----------------------------------------------------------------------------
INSERT INTO settings (`key`, `value`) VALUES
('store_whatsapp', '5511999999999'),
('store_email',    'contato@maiasuplementos.com.br'),
('store_address',  'São Paulo, SP'),
('free_shipping_above', '199.00'),
('hero_label', 'Performance & Resultados'),
('hero_title_before', 'Suplementos para'),
('hero_title_emphasis', 'performance'),
('hero_title_after', 'real.'),
('hero_subtitle', 'Produtos selecionados para treino, rotina e evolucao.'),
('hero_image', '/assets/img/hero-supplement.webp'),
('hero_primary_label', 'Ver Produtos'),
('hero_primary_url', '/produtos'),
('hero_secondary_label', 'Ver Combos'),
('hero_secondary_url', '/combos'),
('home_faq_enabled', '1')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

SET FOREIGN_KEY_CHECKS = 1;
