-- =============================================================================
-- WM Suplementos - Seed visual basico
-- Importar depois de database/schema.sql.
--
-- Objetivo: deixar a loja preenchida visualmente sem criar clientes, pedidos,
-- avaliacoes, notificacoes ou dados operacionais falsos.
-- Pode ser removido pelo cliente depois pelo painel admin.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Marcas
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO brands (name, slug, active) VALUES
('Growth Supplements', 'growth-supplements', 1),
('Optimum Nutrition', 'optimum-nutrition', 1),
('Max Titanium', 'max-titanium', 1),
('Integral Medica', 'integral-medica', 1),
('Dark Lab', 'dark-lab', 1),
('Black Skull', 'black-skull', 1);

-- -----------------------------------------------------------------------------
-- Categorias
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO categories (name, slug, seo_title, seo_description, active, sort_order) VALUES
('Proteinas', 'proteinas', 'Proteinas | WM Suplementos', 'Whey Protein, albumina e proteinas para rotina de treino.', 1, 1),
('Creatina', 'creatina', 'Creatina | WM Suplementos', 'Creatina monohidratada e formulas para forca e desempenho.', 1, 2),
('Pre-Treino', 'pre-treino', 'Pre-Treino | WM Suplementos', 'Energia, foco e intensidade para treinos mais fortes.', 1, 3),
('Aminoacidos', 'aminoacidos', 'Aminoacidos | WM Suplementos', 'BCAA, EAA, glutamina e suporte para recuperacao muscular.', 1, 4),
('Vitaminas', 'vitaminas', 'Vitaminas | WM Suplementos', 'Vitaminas e minerais para saude e performance diaria.', 1, 5),
('Hipercaloricos', 'hipercaloricos', 'Hipercaloricos | WM Suplementos', 'Suplementos para ganho de peso e massa muscular.', 1, 6),
('Termogenicos', 'termogenicos', 'Termogenicos | WM Suplementos', 'Produtos para energia, metabolismo e definicao.', 1, 7),
('Barras e Snacks', 'barras-snacks', 'Barras e Snacks | WM Suplementos', 'Snacks proteicos e opcoes praticas para o dia a dia.', 1, 8);

-- -----------------------------------------------------------------------------
-- Produtos
-- Sem imagens reais: o front usa placeholders por categoria.
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO products
  (name, slug, sku, category_id, brand_id, price, price_sale, stock, stock_alert_threshold,
   description, benefits, seo_title, seo_description, active, featured, bestseller, total_sold)
VALUES
('Whey Protein Concentrado 900g Baunilha', 'whey-protein-concentrado-900g-baunilha', 'WPC-900-BAN',
 (SELECT id FROM categories WHERE slug = 'proteinas' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'growth-supplements' LIMIT 1),
 89.90, 69.90, 120, 10,
 'Whey concentrado com sabor baunilha, ideal para completar a ingestao diaria de proteinas.',
 '24g de proteina por dose; Sabor cremoso; Boa opcao para pos-treino',
 'Whey Protein Concentrado 900g Baunilha | WM Suplementos',
 'Whey concentrado para recuperacao muscular e ganho de massa.',
 1, 1, 1, 86),

('Whey Protein Isolado 900g Chocolate', 'whey-protein-isolado-900g-chocolate', 'WPI-900-CHO',
 (SELECT id FROM categories WHERE slug = 'proteinas' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'optimum-nutrition' LIMIT 1),
 149.90, 119.90, 80, 10,
 'Whey isolado com alto teor proteico e sabor chocolate.',
 'Alta concentracao de proteina; Baixo teor de gordura; Ideal para definicao',
 'Whey Protein Isolado 900g Chocolate | WM Suplementos',
 'Whey isolado para quem busca proteina de alta qualidade.',
 1, 1, 1, 74),

('Whey Protein Concentrado 1,8kg Morango', 'whey-protein-concentrado-1800g-morango', 'WPC-1800-MOR',
 (SELECT id FROM categories WHERE slug = 'proteinas' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'max-titanium' LIMIT 1),
 159.90, 139.90, 60, 8,
 'Embalagem economica de whey concentrado sabor morango.',
 'Melhor custo por dose; Sabor morango; Indicado para ganho de massa',
 'Whey Protein Concentrado 1,8kg Morango | WM Suplementos',
 'Whey concentrado em embalagem economica para rotina de treino.',
 1, 0, 1, 63),

('Creatina Monohidratada 300g', 'creatina-monohidratada-300g', 'CRE-300',
 (SELECT id FROM categories WHERE slug = 'creatina' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'integral-medica' LIMIT 1),
 59.90, 44.90, 150, 15,
 'Creatina monohidratada pura para forca, potencia e desempenho.',
 'Aumenta desempenho em treinos intensos; Sem sabor; Uso diario',
 'Creatina Monohidratada 300g | WM Suplementos',
 'Creatina monohidratada para forca e performance.',
 1, 1, 1, 112),

('Creatina Creapure 250g', 'creatina-creapure-250g', 'CRE-CREAPURE-250',
 (SELECT id FROM categories WHERE slug = 'creatina' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'growth-supplements' LIMIT 1),
 129.90, NULL, 55, 8,
 'Creatina de alta pureza para uso diario.',
 'Alta pureza; Sem adicao de acucar; Facil de misturar',
 'Creatina Creapure 250g | WM Suplementos',
 'Creatina premium para desempenho e recuperacao.',
 1, 0, 0, 41),

('Pre-Treino Dark Energy 300g', 'pre-treino-dark-energy-300g', 'PRE-DARK-300',
 (SELECT id FROM categories WHERE slug = 'pre-treino' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'dark-lab' LIMIT 1),
 109.90, 89.90, 70, 8,
 'Formula para energia, foco e intensidade durante o treino.',
 'Energia para treinos pesados; Foco; Pump',
 'Pre-Treino Dark Energy 300g | WM Suplementos',
 'Pre-treino para energia e intensidade.',
 1, 1, 1, 58),

('Pre-Treino Black Skull 200g', 'pre-treino-black-skull-200g', 'PRE-BS-200',
 (SELECT id FROM categories WHERE slug = 'pre-treino' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'black-skull' LIMIT 1),
 79.90, NULL, 45, 5,
 'Pre-treino para quem busca mais disposicao e intensidade.',
 'Cafeina; Energia; Ideal para rotina de treino',
 'Pre-Treino Black Skull 200g | WM Suplementos',
 'Pre-treino para foco e energia.',
 1, 0, 0, 32),

('BCAA 2:1:1 210 capsulas', 'bcaa-211-210-capsulas', 'BCAA-210',
 (SELECT id FROM categories WHERE slug = 'aminoacidos' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'max-titanium' LIMIT 1),
 69.90, 54.90, 100, 10,
 'Aminoacidos de cadeia ramificada em capsulas.',
 'Suporte para recuperacao; Pratico em capsulas; Proporcao 2:1:1',
 'BCAA 2:1:1 210 capsulas | WM Suplementos',
 'BCAA em capsulas para suporte muscular.',
 1, 1, 0, 47),

('Glutamina 300g', 'glutamina-300g', 'GLU-300',
 (SELECT id FROM categories WHERE slug = 'aminoacidos' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'growth-supplements' LIMIT 1),
 49.90, NULL, 80, 10,
 'L-glutamina para rotina de recuperacao muscular.',
 'Recuperacao; Sem sabor; Facil de misturar',
 'Glutamina 300g | WM Suplementos',
 'Glutamina para recuperacao e rotina de treino.',
 1, 0, 0, 29),

('Multivitaminico Sport 60 comprimidos', 'multivitaminico-sport-60-comprimidos', 'MULTI-60',
 (SELECT id FROM categories WHERE slug = 'vitaminas' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'max-titanium' LIMIT 1),
 39.90, NULL, 200, 20,
 'Complexo de vitaminas e minerais para rotina ativa.',
 'Vitaminas essenciais; Minerais; Uso diario',
 'Multivitaminico Sport 60 comprimidos | WM Suplementos',
 'Multivitaminico para saude e performance diaria.',
 1, 0, 1, 67),

('Omega 3 1000mg 120 capsulas', 'omega-3-1000mg-120-capsulas', 'OMG-1000-120',
 (SELECT id FROM categories WHERE slug = 'vitaminas' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'integral-medica' LIMIT 1),
 49.90, 39.90, 110, 10,
 'Omega 3 em capsulas para suporte a rotina de saude.',
 'EPA e DHA; Uso diario; Capsulas praticas',
 'Omega 3 1000mg 120 capsulas | WM Suplementos',
 'Omega 3 para saude e bem-estar.',
 1, 0, 0, 38),

('Hipercalorico 3kg Chocolate', 'hipercalorico-3kg-chocolate', 'HIPO-3KG-CHO',
 (SELECT id FROM categories WHERE slug = 'hipercaloricos' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'max-titanium' LIMIT 1),
 129.90, 99.90, 40, 5,
 'Hipercalorico sabor chocolate para ganho de peso e massa.',
 'Alto valor calorico; Carboidratos e proteinas; Sabor chocolate',
 'Hipercalorico 3kg Chocolate | WM Suplementos',
 'Hipercalorico para ganho de massa.',
 1, 0, 0, 23),

('Mass Gainer 1,5kg Baunilha', 'mass-gainer-1500g-baunilha', 'MASS-1500-BAN',
 (SELECT id FROM categories WHERE slug = 'hipercaloricos' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'growth-supplements' LIMIT 1),
 89.90, NULL, 55, 8,
 'Mass gainer para complementar calorias e proteinas.',
 'Ganho de peso; Sabor baunilha; Pratico no dia a dia',
 'Mass Gainer 1,5kg Baunilha | WM Suplementos',
 'Mass gainer para ganho de massa.',
 1, 0, 0, 21),

('Termogenico Lipo 60 capsulas', 'termogenico-lipo-60-capsulas', 'TERM-60',
 (SELECT id FROM categories WHERE slug = 'termogenicos' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'black-skull' LIMIT 1),
 59.90, 49.90, 85, 10,
 'Termogenico em capsulas para energia e rotina de definicao.',
 'Cafeina; Energia; Auxilia na rotina de definicao',
 'Termogenico Lipo 60 capsulas | WM Suplementos',
 'Termogenico para energia e metabolismo.',
 1, 1, 0, 52),

('Fat Burner Extreme 120 capsulas', 'fat-burner-extreme-120-capsulas', 'FAT-120',
 (SELECT id FROM categories WHERE slug = 'termogenicos' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'dark-lab' LIMIT 1),
 89.90, NULL, 30, 5,
 'Formula em capsulas para energia e foco na fase de definicao.',
 'Uso pratico; Energia; Rotina de definicao',
 'Fat Burner Extreme 120 capsulas | WM Suplementos',
 'Fat burner para rotina de definicao.',
 1, 0, 0, 19),

('Barra de Proteina Chocolate 12 unidades', 'barra-proteina-chocolate-12un', 'BAR-CHO-12',
 (SELECT id FROM categories WHERE slug = 'barras-snacks' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'growth-supplements' LIMIT 1),
 79.90, 69.90, 60, 8,
 'Caixa com 12 barras proteicas sabor chocolate.',
 'Pratico; 12 unidades; Snack proteico',
 'Barra de Proteina Chocolate 12 unidades | WM Suplementos',
 'Barras proteicas para lanches praticos.',
 1, 1, 0, 44),

('Pasta de Amendoim Integral 1kg', 'pasta-de-amendoim-integral-1kg', 'PASTA-1KG',
 (SELECT id FROM categories WHERE slug = 'barras-snacks' LIMIT 1),
 (SELECT id FROM brands WHERE slug = 'growth-supplements' LIMIT 1),
 39.90, NULL, 75, 8,
 'Pasta de amendoim integral para lanches e receitas.',
 'Fonte de energia; Sem adicao de acucar; Versatil',
 'Pasta de Amendoim Integral 1kg | WM Suplementos',
 'Pasta de amendoim para rotina fitness.',
 1, 0, 0, 34);

-- -----------------------------------------------------------------------------
-- Combos
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO combos (name, slug, description, price, active) VALUES
('Kit Iniciante', 'kit-iniciante', 'Comece com o essencial: whey concentrado, creatina e multivitaminico.', 159.90, 1),
('Kit Ganho de Massa', 'kit-ganho-de-massa', 'Combinacao para fase de ganho: hipercalorico, whey e BCAA.', 239.90, 1),
('Kit Definicao Total', 'kit-definicao-total', 'Whey isolado, creatina e termogenico para rotina de definicao.', 199.90, 1),
('Kit Energia e Foco', 'kit-energia-e-foco', 'Pre-treino, creatina e snack proteico para treinos intensos.', 179.90, 1);

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1 FROM combos c JOIN products p
WHERE c.slug = 'kit-iniciante' AND p.slug IN (
  'whey-protein-concentrado-900g-baunilha',
  'creatina-monohidratada-300g',
  'multivitaminico-sport-60-comprimidos'
);

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1 FROM combos c JOIN products p
WHERE c.slug = 'kit-ganho-de-massa' AND p.slug IN (
  'hipercalorico-3kg-chocolate',
  'whey-protein-concentrado-1800g-morango',
  'bcaa-211-210-capsulas'
);

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1 FROM combos c JOIN products p
WHERE c.slug = 'kit-definicao-total' AND p.slug IN (
  'whey-protein-isolado-900g-chocolate',
  'creatina-monohidratada-300g',
  'termogenico-lipo-60-capsulas'
);

INSERT IGNORE INTO combo_items (combo_id, product_id, quantity)
SELECT c.id, p.id, 1 FROM combos c JOIN products p
WHERE c.slug = 'kit-energia-e-foco' AND p.slug IN (
  'pre-treino-dark-energy-300g',
  'creatina-creapure-250g',
  'barra-proteina-chocolate-12un'
);

-- -----------------------------------------------------------------------------
-- Cupons visuais/teste
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO coupons (code, type, value, min_order, max_uses, active, expires_at) VALUES
('BEMVINDO10', 'percent', 10.00, 0.00, 1000, 1, DATE_ADD(NOW(), INTERVAL 1 YEAR)),
('WM20', 'percent', 20.00, 150.00, 200, 1, DATE_ADD(NOW(), INTERVAL 6 MONTH));

-- -----------------------------------------------------------------------------
-- Paginas institucionais
-- -----------------------------------------------------------------------------
INSERT INTO pages (slug, title, content, active) VALUES
('sobre',
 'Sobre a WM Suplementos',
 '<h2>Sobre a WM Suplementos</h2><p>A WM Suplementos e uma loja especializada em suplementos, vitaminas e produtos para rotina esportiva.</p><p>Trabalhamos com marcas reconhecidas e atendimento direto pelo WhatsApp.</p>',
 1),
('como-comprar',
 'Como Comprar',
 '<h2>Como comprar</h2><ol><li>Escolha seus produtos.</li><li>Adicione ao carrinho.</li><li>Finalize o pedido com PIX ou cartao.</li><li>Envie o comprovante ou combine o pagamento pelo WhatsApp.</li></ol>',
 1),
('formas-de-pagamento',
 'Formas de Pagamento',
 '<h2>Formas de pagamento</h2><p>Aceitamos PIX e cartao de credito. O atendimento final acontece pelo WhatsApp.</p>',
 1),
('trocas-e-devolucoes',
 'Trocas e Devolucoes',
 '<h2>Trocas e devolucoes</h2><p>Solicitacoes devem ser feitas pelo WhatsApp dentro do prazo legal, com numero do pedido e motivo da troca.</p>',
 1),
('perguntas-frequentes',
 'Perguntas Frequentes',
 '<h2>Perguntas frequentes</h2><h3>Os produtos sao originais?</h3><p>Sim. Trabalhamos com produtos lacrados e marcas reconhecidas.</p><h3>Como finalizo o pagamento?</h3><p>No PIX, copie a chave e envie o comprovante pelo WhatsApp. No cartao, nossa equipe entra em contato.</p>',
 1)
ON DUPLICATE KEY UPDATE
  title = VALUES(title),
  content = VALUES(content),
  active = VALUES(active);

-- -----------------------------------------------------------------------------
-- Configuracoes visuais
-- hero_image vazio = visual antigo sem foto de fundo.
-- -----------------------------------------------------------------------------
INSERT INTO settings (`key`, `value`) VALUES
('store_name', 'WM Suplementos'),
('store_whatsapp', '5599999999999'),
('store_email', 'contato@wmsuplementos.com.br'),
('store_pix_key', NULL),
('store_address', 'Brasil'),
('shipping_flat_rate', '0.00'),
('free_shipping_above', '0.00'),
('card_interest_monthly', '3.00'),
('header_nav_links', 'Produtos|/produtos\nCombos|/combos'),
('footer_nav_links', 'Todos os Produtos|/produtos\nCombos|/combos\nProteinas|/categoria/proteinas\nCreatina|/categoria/creatina'),
('footer_info_links', 'Sobre Nos|/pagina/sobre\nComo Comprar|/pagina/como-comprar\nTrocas e Devolucoes|/pagina/trocas-e-devolucoes\nPerguntas Frequentes|/pagina/perguntas-frequentes'),
('stock_alert_min', '5'),
('hero_label', 'Performance & Resultados'),
('hero_title_before', 'Suplementos para'),
('hero_title_emphasis', 'performance'),
('hero_title_after', 'real.'),
('hero_subtitle', 'Produtos selecionados para treino, rotina e evolucao.'),
('hero_image', ''),
('hero_primary_label', 'Ver Produtos'),
('hero_primary_url', '/produtos'),
('hero_secondary_label', 'Ver Combos'),
('hero_secondary_url', '/combos'),
('home_faq_enabled', '1')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

SET FOREIGN_KEY_CHECKS = 1;
