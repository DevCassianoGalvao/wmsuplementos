-- =============================================================================
-- WM Suplementos — Seed de Demonstração
-- Popula o banco com ~60 pedidos, 20 clientes e avaliações realistas
-- para visualização de métricas, filtros e dashboard.
--
-- Execute UMA VEZ. Usa INSERT IGNORE para evitar duplicatas.
-- Exige que o schema.sql já tenha sido executado.
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '-03:00';
SET foreign_key_checks = 0;

-- =============================================================================
-- 1. CATEGORIAS
-- =============================================================================
INSERT IGNORE INTO `categories` (`id`, `name`, `slug`, `active`, `sort_order`) VALUES
(51, 'Creatina',             'creatina',             1, 1),
(52, 'Proteínas',            'proteinas',            1, 2),
(53, 'Aminoácidos',          'aminoacidos',          1, 3),
(54, 'Vitaminas e Minerais', 'vitaminas-e-minerais', 1, 4),
(55, 'Termogênicos',         'termogenicos',         1, 5);

-- =============================================================================
-- 2. MARCAS
-- =============================================================================
INSERT IGNORE INTO `brands` (`id`, `name`, `slug`, `active`) VALUES
(51, 'Max Titanium',        'max-titanium',        1),
(52, 'Integralmedica',      'integralmedica',      1),
(53, 'Atlhetica Nutrition', 'atlhetica-nutrition', 1),
(54, 'Growth Supplements',  'growth-supplements',  1),
(55, 'Probiótica',          'probiotica',          1);

-- =============================================================================
-- 3. PRODUTOS
-- =============================================================================
INSERT IGNORE INTO `products`
  (`id`, `name`, `slug`, `sku`, `category_id`, `brand_id`, `price`, `price_sale`,
   `stock`, `stock_alert_threshold`, `description`, `active`, `featured`, `bestseller`, `total_sold`)
VALUES
(101, 'Creatina Monohidratada 500g',          'creatina-monohidratada-500g',    'SKU-CR-001', 51, 51,  79.90,  NULL,  143, 5, 'Creatina pura 100% monohidratada. Aumenta força e potência muscular.', 1, 1, 1, 312),
(102, 'Creatina 300g',                         'creatina-300g',                  'SKU-CR-002', 51, 54,  54.90,  NULL,   87, 5, 'Creatina monohidratada em pó. Qualidade garantida Growth Supplements.', 1, 0, 0, 178),
(103, 'Whey Protein Concentrado 1kg Chocolate','whey-concentrado-1kg-chocolate', 'SKU-WH-001', 52, 52, 159.90, 139.90,  56, 5, 'Whey concentrado. Alto teor proteico com sabor chocolate irresistível.', 1, 1, 1, 245),
(104, 'Whey Protein Blend 2kg Baunilha',       'whey-blend-2kg-baunilha',        'SKU-WH-002', 52, 53, 289.90,  NULL,   34, 5, 'Blend de whey concentrado e isolado. Absorção rápida e eficiente.',    1, 0, 1, 134),
(105, 'BCAA 2400 240 Caps',                    'bcaa-2400-240-caps',             'SKU-BC-001', 53, 51,  89.90,  NULL,   98, 5, 'BCAA 2:1:1. Reduz catabolismo muscular e acelera recuperação.',          1, 0, 0, 189),
(106, 'Pré-Treino Hades 300g',                 'pre-treino-hades-300g',          'SKU-PT-001', 53, 55, 119.90,  NULL,   67, 5, 'Cafeína, beta-alanina e arginina. Foco e explosão máximos.',            1, 1, 0, 201),
(107, 'Glutamina 500g',                        'glutamina-500g',                 'SKU-GL-001', 53, 54,  64.90,  NULL,  112, 5, 'L-Glutamina pura. Acelera a recuperação muscular pós-treino.',          1, 0, 0, 156),
(108, 'Multivitamínico Sport 90 Caps',         'multivitaminico-sport-90-caps',  'SKU-MV-001', 54, 52,  49.90,  NULL,  203, 5, 'Complexo vitamínico completo para atletas.',                            1, 0, 0, 287),
(109, 'Ômega 3 120 Cáps',                     'omega-3-120-caps',               'SKU-OM-001', 54, 51,  39.90,  NULL,  178, 5, 'Óleo de peixe de alta pureza. Suporte cardiovascular e anti-inflamatório.', 1, 0, 0, 342),
(110, 'Termogênico Black Speed 120 Caps',      'termogenico-black-speed-120-caps','SKU-TS-001',55, 55, 109.90,  NULL,   45, 5, 'Cafeína, chá verde e pimenta vermelha. Acelera o metabolismo.',         1, 1, 0, 167),
(111, 'Proteína Vegana Chocolate 1kg',         'proteina-vegana-chocolate-1kg',  'SKU-PV-001', 52, 52, 179.90,  NULL,   28, 5, 'Proteína 100% vegetal à base de ervilha e arroz. Sem lactose.',         1, 0, 0,  89),
(112, 'Albumina Natural 500g',                 'albumina-natural-500g',          'SKU-AL-001', 52, 54,  44.90,  NULL,   91, 5, 'Clara de ovo em pó desidratada. Proteína de alta qualidade.',          1, 0, 0, 203);

-- =============================================================================
-- 4. CLIENTES — senhas são placeholders (demo), não funcionam para login
-- =============================================================================
INSERT IGNORE INTO `users`
  (`id`, `name`, `email`, `password_hash`, `phone`, `city`, `state`,
   `email_opt_in`, `tag`, `total_orders`, `total_spent`, `last_purchase_at`, `created_at`)
VALUES
(201, 'Ana Clara Silva',        'ana.clara@demo.com.br',      '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(11) 98234-5678', 'São Paulo',       'SP', 1, 'vip',     0, 0.00, NULL, '2025-12-10 09:14:22'),
(202, 'Bruno Ferreira Lima',    'bruno.lima@demo.com.br',     '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(21) 97765-4321', 'Rio de Janeiro',  'RJ', 1, '',        0, 0.00, NULL, '2025-12-18 14:30:05'),
(203, 'Carla Mendes Souza',     'carla.mendes@demo.com.br',   '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(31) 99981-2345', 'Belo Horizonte',  'MG', 1, '',        0, 0.00, NULL, '2025-12-22 11:45:18'),
(204, 'Diego Santos Rocha',     'diego.rocha@demo.com.br',    '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(41) 98812-9900', 'Curitiba',        'PR', 1, '',        0, 0.00, NULL, '2026-01-03 08:20:44'),
(205, 'Elena Costa Ramos',      'elena.ramos@demo.com.br',    '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(51) 99345-6789', 'Porto Alegre',    'RS', 1, 'vip',     0, 0.00, NULL, '2026-01-07 16:55:32'),
(206, 'Felipe Alves Martins',   'felipe.martins@demo.com.br', '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(62) 98123-0099', 'Goiânia',         'GO', 1, '',        0, 0.00, NULL, '2026-01-12 10:08:17'),
(207, 'Gabriela Pereira',       'gabi.pereira@demo.com.br',   '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(85) 97722-3344', 'Fortaleza',       'CE', 1, '',        0, 0.00, NULL, '2026-01-15 13:42:09'),
(208, 'Henrique Oliveira',      'henrique.o@demo.com.br',     '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(11) 98456-7890', 'São Paulo',       'SP', 1, 'atacado', 0, 0.00, NULL, '2026-01-20 09:33:51'),
(209, 'Isabela Nunes Torres',   'isa.nunes@demo.com.br',      '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(48) 99567-8901', 'Florianópolis',   'SC', 1, '',        0, 0.00, NULL, '2026-02-02 11:22:35'),
(210, 'João Pedro Barros',      'joao.barros@demo.com.br',    '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(71) 98234-1122', 'Salvador',        'BA', 1, '',        0, 0.00, NULL, '2026-02-08 15:10:44'),
(211, 'Kamila Rodrigues',       'kamila.r@demo.com.br',       '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(81) 99678-9012', 'Recife',          'PE', 1, 'vip',     0, 0.00, NULL, '2026-02-14 09:05:28'),
(212, 'Lucas Henrique Dias',    'lucas.dias@demo.com.br',     '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(11) 97890-3456', 'São Paulo',       'SP', 1, '',        0, 0.00, NULL, '2026-02-20 12:48:03'),
(213, 'Marina Campos Leite',    'marina.leite@demo.com.br',   '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(19) 99012-4567', 'Campinas',        'SP', 1, '',        0, 0.00, NULL, '2026-03-01 08:33:17'),
(214, 'Nathan Cruz Silva',      'nathan.cruz@demo.com.br',    '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(27) 98345-5678', 'Vitória',         'ES', 1, '',        0, 0.00, NULL, '2026-03-05 17:21:40'),
(215, 'Olivia Fernandes',       'olivia.fern@demo.com.br',    '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(92) 99123-6789', 'Manaus',          'AM', 1, '',        0, 0.00, NULL, '2026-03-10 10:15:55'),
(216, 'Paulo Roberto Motta',    'paulo.motta@demo.com.br',    '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(44) 98901-2345', 'Maringá',         'PR', 1, 'atacado', 0, 0.00, NULL, '2026-03-15 14:07:29'),
(217, 'Quéren Lima Abreu',      'queren.lima@demo.com.br',    '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(82) 97654-3210', 'Maceió',          'AL', 1, '',        0, 0.00, NULL, '2026-03-22 09:44:12'),
(218, 'Rafael Souza Pinto',     'rafael.pinto@demo.com.br',   '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(11) 99234-5670', 'São Paulo',       'SP', 1, '',        0, 0.00, NULL, '2026-04-02 11:38:00'),
(219, 'Sabrina Torres Vaz',     'sabrina.vaz@demo.com.br',    '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(65) 98765-4321', 'Cuiabá',          'MT', 1, '',        0, 0.00, NULL, '2026-04-08 16:50:33'),
(220, 'Thiago Moura Carvalho',  'thiago.moura@demo.com.br',   '$2y$12$demoSeedUserHashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '(91) 99876-5432', 'Belém',           'PA', 1, 'vip',     0, 0.00, NULL, '2026-04-12 08:25:47');

-- =============================================================================
-- 5. PEDIDOS — distribuídos em Jan–Jun 2026 (60 pedidos)
-- Totais calculados a partir dos order_items abaixo.
-- =============================================================================
INSERT IGNORE INTO `orders`
  (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`,
   `status`, `subtotal`, `discount`, `total`,
   `payment_method`, `payment_id`, `payment_status`, `tracking_code`, `created_at`)
VALUES
-- ── Janeiro 2026 ──────────────────────────────────────────────────────────────
(5001, 201,'Ana Clara Silva',      'ana.clara@demo.com.br',     '(11) 98234-5678','entregue',   169.80,0.00,169.80,'pix',    'PIX-2601-0001','approved','BR10000050001','2026-01-05 10:23:14'),
(5002, 204,'Diego Santos Rocha',   'diego.rocha@demo.com.br',   '(41) 98812-9900','entregue',   249.80,0.00,249.80,'cartao', 'MP-2601-00002','approved','BR10000050002','2026-01-08 14:17:32'),
(5003, 205,'Elena Costa Ramos',    'elena.ramos@demo.com.br',   '(51) 99345-6789','cancelado',   79.90,0.00, 79.90,'boleto', NULL,           'pending', NULL,           '2026-01-10 09:45:00'),
(5004, 202,'Bruno Ferreira Lima',  'bruno.lima@demo.com.br',    '(21) 97765-4321','entregue',   289.90,0.00,289.90,'pix',    'PIX-2601-0004','approved','BR10000050004','2026-01-13 11:30:28'),
(5005, 208,'Henrique Oliveira',    'henrique.o@demo.com.br',    '(11) 98456-7890','entregue',   569.50,0.00,569.50,'cartao', 'MP-2601-00005','approved','BR10000050005','2026-01-16 16:05:41'),
(5006, 206,'Felipe Alves Martins', 'felipe.martins@demo.com.br','(62) 98123-0099','entregue',    89.80,0.00, 89.80,'pix',    'PIX-2601-0006','approved','BR10000050006','2026-01-20 08:55:13'),
(5007, 207,'Gabriela Pereira',     'gabi.pereira@demo.com.br',  '(85) 97722-3344','entregue',   159.90,0.00,159.90,'pix',    'PIX-2601-0007','approved','BR10000050007','2026-01-23 13:44:50'),
(5008, 203,'Carla Mendes Souza',   'carla.mendes@demo.com.br',  '(31) 99981-2345','entregue',   104.80,0.00,104.80,'cartao', 'MP-2601-00008','approved','BR10000050008','2026-01-27 17:12:33'),
-- ── Fevereiro 2026 ────────────────────────────────────────────────────────────
(5009, 209,'Isabela Nunes Torres', 'isa.nunes@demo.com.br',     '(48) 99567-8901','entregue',   209.80,0.00,209.80,'pix',    'PIX-2602-0009','approved','BR10000050009','2026-02-03 09:10:22'),
(5010, 210,'João Pedro Barros',    'joao.barros@demo.com.br',   '(71) 98234-1122','entregue',   289.90,0.00,289.90,'cartao', 'MP-2602-00010','approved','BR10000050010','2026-02-05 15:30:17'),
(5011, 201,'Ana Clara Silva',      'ana.clara@demo.com.br',     '(11) 98234-5678','entregue',   104.80,0.00,104.80,'pix',    'PIX-2602-0011','approved','BR10000050011','2026-02-08 10:05:44'),
(5012, 211,'Kamila Rodrigues',     'kamila.r@demo.com.br',      '(81) 99678-9012','entregue',   369.80,0.00,369.80,'cartao', 'MP-2602-00012','approved','BR10000050012','2026-02-11 14:22:08'),
(5013, 205,'Elena Costa Ramos',    'elena.ramos@demo.com.br',   '(51) 99345-6789','entregue',    79.90,0.00, 79.90,'pix',    'PIX-2602-0013','approved','BR10000050013','2026-02-13 09:48:55'),
(5014, 212,'Lucas Henrique Dias',  'lucas.dias@demo.com.br',    '(11) 97890-3456','entregue',   199.80,0.00,199.80,'pix',    'PIX-2602-0014','approved','BR10000050014','2026-02-17 11:33:29'),
(5015, 208,'Henrique Oliveira',    'henrique.o@demo.com.br',    '(11) 98456-7890','entregue',   759.40,0.00,759.40,'cartao', 'MP-2602-00015','approved','BR10000050015','2026-02-20 16:47:02'),
(5016, 204,'Diego Santos Rocha',   'diego.rocha@demo.com.br',   '(41) 98812-9900','cancelado',  159.90,0.00,159.90,'cartao', NULL,           'rejected',NULL,           '2026-02-22 08:20:16'),
(5017, 202,'Bruno Ferreira Lima',  'bruno.lima@demo.com.br',    '(21) 97765-4321','entregue',   209.70,0.00,209.70,'boleto', 'BOL-2602-0017','approved','BR10000050017','2026-02-25 13:15:38'),
(5018, 206,'Felipe Alves Martins', 'felipe.martins@demo.com.br','(62) 98123-0099','entregue',    89.90,0.00, 89.90,'pix',    'PIX-2602-0018','approved','BR10000050018','2026-02-28 10:58:11'),
-- ── Março 2026 ────────────────────────────────────────────────────────────────
(5019, 213,'Marina Campos Leite',  'marina.leite@demo.com.br',  '(19) 99012-4567','entregue',   214.80,0.00,214.80,'pix',    'PIX-2603-0019','approved','BR10000050019','2026-03-03 09:22:41'),
(5020, 211,'Kamila Rodrigues',     'kamila.r@demo.com.br',      '(81) 99678-9012','entregue',   159.90,0.00,159.90,'cartao', 'MP-2603-00020','approved','BR10000050020','2026-03-05 14:10:05'),
(5021, 209,'Isabela Nunes Torres', 'isa.nunes@demo.com.br',     '(48) 99567-8901','entregue',    89.80,0.00, 89.80,'pix',    'PIX-2603-0021','approved','BR10000050021','2026-03-07 11:38:22'),
(5022, 214,'Nathan Cruz Silva',    'nathan.cruz@demo.com.br',   '(27) 98345-5678','entregue',   224.80,0.00,224.80,'pix',    'PIX-2603-0022','approved','BR10000050022','2026-03-10 08:45:17'),
(5023, 208,'Henrique Oliveira',    'henrique.o@demo.com.br',    '(11) 98456-7890','entregue',   739.60,0.00,739.60,'cartao', 'MP-2603-00023','approved','BR10000050023','2026-03-12 15:55:39'),
(5024, 205,'Elena Costa Ramos',    'elena.ramos@demo.com.br',   '(51) 99345-6789','entregue',   169.80,0.00,169.80,'pix',    'PIX-2603-0024','approved','BR10000050024','2026-03-15 10:14:28'),
(5025, 215,'Olivia Fernandes',     'olivia.fern@demo.com.br',   '(92) 99123-6789','entregue',    94.80,0.00, 94.80,'boleto', 'BOL-2603-0025','approved','BR10000050025','2026-03-17 13:29:55'),
(5026, 201,'Ana Clara Silva',      'ana.clara@demo.com.br',     '(11) 98234-5678','entregue',   369.80,0.00,369.80,'cartao', 'MP-2603-00026','approved','BR10000050026','2026-03-19 09:03:14'),
(5027, 216,'Paulo Roberto Motta',  'paulo.motta@demo.com.br',   '(44) 98901-2345','entregue',   959.10,0.00,959.10,'cartao', 'MP-2603-00027','approved','BR10000050027','2026-03-22 16:42:07'),
(5028, 210,'João Pedro Barros',    'joao.barros@demo.com.br',   '(71) 98234-1122','cancelado',  119.90,0.00,119.90,'pix',    NULL,           'pending', NULL,           '2026-03-24 08:17:51'),
(5029, 212,'Lucas Henrique Dias',  'lucas.dias@demo.com.br',    '(11) 97890-3456','entregue',   154.80,0.00,154.80,'pix',    'PIX-2603-0029','approved','BR10000050029','2026-03-28 11:51:33'),
-- ── Abril 2026 ────────────────────────────────────────────────────────────────
(5030, 217,'Quéren Lima Abreu',    'queren.lima@demo.com.br',   '(82) 97654-3210','entregue',   229.70,0.00,229.70,'pix',    'PIX-2604-0030','approved','BR10000050030','2026-04-02 09:35:44'),
(5031, 218,'Rafael Souza Pinto',   'rafael.pinto@demo.com.br',  '(11) 99234-5670','entregue',   289.70,0.00,289.70,'cartao', 'MP-2604-00031','approved','BR10000050031','2026-04-04 14:08:19'),
(5032, 201,'Ana Clara Silva',      'ana.clara@demo.com.br',     '(11) 98234-5678','entregue',   244.80,0.00,244.80,'pix',    'PIX-2604-0032','approved','BR10000050032','2026-04-07 10:22:36'),
(5033, 211,'Kamila Rodrigues',     'kamila.r@demo.com.br',      '(81) 99678-9012','entregue',   399.80,0.00,399.80,'cartao', 'MP-2604-00033','approved','BR10000050033','2026-04-09 15:40:02'),
(5034, 213,'Marina Campos Leite',  'marina.leite@demo.com.br',  '(19) 99012-4567','entregue',   119.90,0.00,119.90,'pix',    'PIX-2604-0034','approved','BR10000050034','2026-04-11 09:55:28'),
(5035, 208,'Henrique Oliveira',    'henrique.o@demo.com.br',    '(11) 98456-7890','entregue',  1049.40,0.00,1049.40,'cartao','MP-2604-00035','approved','BR10000050035','2026-04-14 13:18:47'),
(5036, 219,'Sabrina Torres Vaz',   'sabrina.vaz@demo.com.br',   '(65) 98765-4321','entregue',   159.90,0.00,159.90,'pix',    'PIX-2604-0036','approved','BR10000050036','2026-04-16 08:31:14'),
(5037, 214,'Nathan Cruz Silva',    'nathan.cruz@demo.com.br',   '(27) 98345-5678','entregue',   154.80,0.00,154.80,'pix',    'PIX-2604-0037','approved','BR10000050037','2026-04-18 16:47:55'),
(5038, 216,'Paulo Roberto Motta',  'paulo.motta@demo.com.br',   '(44) 98901-2345','entregue',   949.40,0.00,949.40,'cartao', 'MP-2604-00038','approved','BR10000050038','2026-04-21 11:03:22'),
(5039, 220,'Thiago Moura Carvalho','thiago.moura@demo.com.br',  '(91) 99876-5432','entregue',   309.70,0.00,309.70,'pix',    'PIX-2604-0039','approved','BR10000050039','2026-04-23 14:28:09'),
(5040, 203,'Carla Mendes Souza',   'carla.mendes@demo.com.br',  '(31) 99981-2345','cancelado',   89.90,0.00, 89.90,'pix',    NULL,           'pending', NULL,           '2026-04-25 09:12:33'),
(5041, 209,'Isabela Nunes Torres', 'isa.nunes@demo.com.br',     '(48) 99567-8901','entregue',   289.70,0.00,289.70,'boleto', 'BOL-2604-0041','approved','BR10000050041','2026-04-28 15:55:07'),
-- ── Maio 2026 (Dia das Mães — pico de vendas) ─────────────────────────────────
(5042, 201,'Ana Clara Silva',      'ana.clara@demo.com.br',     '(11) 98234-5678','entregue',   449.60,0.00,449.60,'pix',    'PIX-2605-0042','approved','BR10000050042','2026-05-02 09:44:11'),
(5043, 212,'Lucas Henrique Dias',  'lucas.dias@demo.com.br',    '(11) 97890-3456','entregue',   384.70,0.00,384.70,'cartao', 'MP-2605-00043','approved','BR10000050043','2026-05-05 14:20:38'),
(5044, 211,'Kamila Rodrigues',     'kamila.r@demo.com.br',      '(81) 99678-9012','entregue',   489.70,0.00,489.70,'cartao', 'MP-2605-00044','approved','BR10000050044','2026-05-08 10:35:55'),
(5045, 213,'Marina Campos Leite',  'marina.leite@demo.com.br',  '(19) 99012-4567','entregue',   274.70,0.00,274.70,'pix',    'PIX-2605-0045','approved','BR10000050045','2026-05-10 16:05:22'),
(5046, 216,'Paulo Roberto Motta',  'paulo.motta@demo.com.br',   '(44) 98901-2345','entregue',  1309.30,0.00,1309.30,'cartao','MP-2605-00046','approved','BR10000050046','2026-05-12 09:18:44'),
(5047, 218,'Rafael Souza Pinto',   'rafael.pinto@demo.com.br',  '(11) 99234-5670','entregue',   179.90,0.00,179.90,'pix',    'PIX-2605-0047','approved','BR10000050047','2026-05-15 13:42:17'),
(5048, 215,'Olivia Fernandes',     'olivia.fern@demo.com.br',   '(92) 99123-6789','entregue',   129.70,0.00,129.70,'pix',    'PIX-2605-0048','approved','BR10000050048','2026-05-18 08:55:09'),
(5049, 213,'Marina Campos Leite',  'marina.leite@demo.com.br',  '(19) 99012-4567','entregue',   289.70,0.00,289.70,'cartao', 'MP-2605-00049','approved','BR10000050049','2026-05-21 15:30:43'),
(5050, 210,'João Pedro Barros',    'joao.barros@demo.com.br',   '(71) 98234-1122','entregue',   369.80,0.00,369.80,'pix',    'PIX-2605-0050','approved','BR10000050050','2026-05-24 11:08:28'),
(5051, 208,'Henrique Oliveira',    'henrique.o@demo.com.br',    '(11) 98456-7890','enviado',   1379.20,0.00,1379.20,'cartao','MP-2605-00051','approved','BR10000050051','2026-05-28 16:44:52'),
-- ── Junho 2026 ────────────────────────────────────────────────────────────────
(5052, 217,'Quéren Lima Abreu',    'queren.lima@demo.com.br',   '(82) 97654-3210','entregue',   159.90,0.00,159.90,'pix',    'PIX-2606-0052','approved','BR10000050052','2026-06-02 09:17:33'),
(5053, 219,'Sabrina Torres Vaz',   'sabrina.vaz@demo.com.br',   '(65) 98765-4321','entregue',   249.80,0.00,249.80,'cartao', 'MP-2606-00053','approved','BR10000050053','2026-06-05 14:40:06'),
(5054, 201,'Ana Clara Silva',      'ana.clara@demo.com.br',     '(11) 98234-5678','entregue',   404.70,0.00,404.70,'pix',    'PIX-2606-0054','approved','BR10000050054','2026-06-08 10:52:41'),
(5055, 220,'Thiago Moura Carvalho','thiago.moura@demo.com.br',  '(91) 99876-5432','enviado',    469.80,0.00,469.80,'pix',    'PIX-2606-0055','approved','BR10000050055','2026-06-11 15:23:19'),
(5056, 214,'Nathan Cruz Silva',    'nathan.cruz@demo.com.br',   '(27) 98345-5678','entregue',   199.80,0.00,199.80,'pix',    'PIX-2606-0056','approved','BR10000050056','2026-06-14 09:05:58'),
(5057, 211,'Kamila Rodrigues',     'kamila.r@demo.com.br',      '(81) 99678-9012','enviado',    489.70,0.00,489.70,'cartao', 'MP-2606-00057','approved','BR10000050057','2026-06-17 13:38:44'),
(5058, 216,'Paulo Roberto Motta',  'paulo.motta@demo.com.br',   '(44) 98901-2345','em_preparacao',1379.20,0.00,1379.20,'cartao','MP-2606-00058','approved',NULL,         '2026-06-20 08:11:27'),
(5059, 218,'Rafael Souza Pinto',   'rafael.pinto@demo.com.br',  '(11) 99234-5670','pago',       239.80,0.00,239.80,'pix',    'PIX-2606-0059','approved',NULL,           '2026-06-25 16:44:09'),
(5060, 209,'Isabela Nunes Torres', 'isa.nunes@demo.com.br',     '(48) 99567-8901','aguardando_pagamento',159.90,0.00,159.90,'boleto',NULL,'pending',NULL,'2026-06-28 09:30:55');

-- =============================================================================
-- 6. ITENS DOS PEDIDOS
-- Preços: 101=79.90 | 102=54.90 | 103=159.90 | 104=289.90 | 105=89.90
--         106=119.90 | 107=64.90 | 108=49.90 | 109=39.90 | 110=109.90
--         111=179.90 | 112=44.90
-- =============================================================================
INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
-- 5001 = 79.90+89.90 = 169.80
(5001,101,'Creatina Monohidratada 500g',          79.90, 1,  79.90),
(5001,105,'BCAA 2400 240 Caps',                   89.90, 1,  89.90),
-- 5002 = 159.90+89.90 = 249.80
(5002,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1,159.90),
(5002,105,'BCAA 2400 240 Caps',                   89.90, 1,  89.90),
-- 5003 (cancelado) = 79.90
(5003,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5004 = 289.90
(5004,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
-- 5005 = 159.90*2+79.90*2+89.90 = 319.80+159.80+89.90 = 569.50
(5005,103,'Whey Protein Concentrado 1kg Chocolate',159.90,2, 319.80),
(5005,101,'Creatina Monohidratada 500g',           79.90, 2, 159.80),
(5005,105,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5006 = 39.90+49.90 = 89.80
(5006,109,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
(5006,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5007 = 159.90
(5007,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
-- 5008 = 54.90+49.90 = 104.80
(5008,102,'Creatina 300g',                         54.90, 1,  54.90),
(5008,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),

-- 5009 = 89.90+119.90 = 209.80
(5009,105,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
(5009,106,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
-- 5010 = 289.90
(5010,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
-- 5011 = 64.90+39.90 = 104.80
(5011,107,'Glutamina 500g',                        64.90, 1,  64.90),
(5011,109,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5012 = 289.90+79.90 = 369.80
(5012,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5012,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5013 = 79.90
(5013,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5014 = 159.90+39.90 = 199.80
(5014,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
(5014,109,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5015 = 159.90*3+79.90*2+119.90 = 479.70+159.80+119.90 = 759.40
(5015,103,'Whey Protein Concentrado 1kg Chocolate',159.90,3, 479.70),
(5015,101,'Creatina Monohidratada 500g',           79.90, 2, 159.80),
(5015,106,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
-- 5016 (cancelado) = 159.90
(5016,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
-- 5017 = 119.90+49.90+39.90 = 209.70
(5017,106,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
(5017,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
(5017,109,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5018 = 89.90
(5018,105,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),

-- 5019 = 159.90+54.90 = 214.80
(5019,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
(5019,102,'Creatina 300g',                         54.90, 1,  54.90),
-- 5020 = 159.90
(5020,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
-- 5021 = 49.90+39.90 = 89.80
(5021,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
(5021,109,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5022 = 159.90+64.90 = 224.80
(5022,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
(5022,107,'Glutamina 500g',                        64.90, 1,  64.90),
-- 5023 = 289.90*2+79.90*2 = 579.80+159.80 = 739.60
(5023,104,'Whey Protein Blend 2kg Baunilha',      289.90, 2, 579.80),
(5023,101,'Creatina Monohidratada 500g',           79.90, 2, 159.80),
-- 5024 = 119.90+49.90 = 169.80
(5024,106,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
(5024,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5025 = 54.90+39.90 = 94.80
(5025,102,'Creatina 300g',                         54.90, 1,  54.90),
(5025,109,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5026 = 289.90+79.90 = 369.80
(5026,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5026,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5027 = 159.90*4+79.90*3+39.90*2 = 639.60+239.70+79.80 = 959.10
(5027,103,'Whey Protein Concentrado 1kg Chocolate',159.90,4, 639.60),
(5027,101,'Creatina Monohidratada 500g',           79.90, 3, 239.70),
(5027,109,'Ômega 3 120 Cáps',                     39.90, 2,  79.80),
-- 5028 (cancelado) = 119.90
(5028,106,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
-- 5029 = 89.90+64.90 = 154.80
(5029,105,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
(5029,107,'Glutamina 500g',                        64.90, 1,  64.90),

-- 5030 = 109.90+79.90+39.90 = 229.70
(5030,110,'Termogênico Black Speed 120 Caps',     109.90, 1, 109.90),
(5030,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5030,109,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5031 = 159.90+79.90+49.90 = 289.70
(5031,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
(5031,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5031,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5032 = 179.90+64.90 = 244.80
(5032,111,'Proteína Vegana Chocolate 1kg',        179.90, 1, 179.90),
(5032,107,'Glutamina 500g',                        64.90, 1,  64.90),
-- 5033 = 289.90+109.90 = 399.80
(5033,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5033,110,'Termogênico Black Speed 120 Caps',     109.90, 1, 109.90),
-- 5034 = 119.90
(5034,106,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
-- 5035 = 159.90*4+289.90+119.90 = 639.60+289.90+119.90 = 1049.40
(5035,103,'Whey Protein Concentrado 1kg Chocolate',159.90,4, 639.60),
(5035,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5035,106,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
-- 5036 = 159.90
(5036,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
-- 5037 = 89.90+64.90 = 154.80
(5037,105,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
(5037,107,'Glutamina 500g',                        64.90, 1,  64.90),
-- 5038 = 159.90*3+289.90+89.90*2 = 479.70+289.90+179.80 = 949.40
(5038,103,'Whey Protein Concentrado 1kg Chocolate',159.90,3, 479.70),
(5038,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5038,105,'BCAA 2400 240 Caps',                    89.90, 2, 179.80),
-- 5039 = 179.90+79.90+49.90 = 309.70
(5039,111,'Proteína Vegana Chocolate 1kg',        179.90, 1, 179.90),
(5039,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5039,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5040 (cancelado) = 89.90
(5040,105,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5041 = 159.90+79.90+49.90 = 289.70
(5041,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
(5041,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5041,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),

-- 5042 = 289.90+79.90+39.90*2 = 289.90+79.90+79.80 = 449.60
(5042,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5042,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5042,109,'Ômega 3 120 Cáps',                     39.90, 2,  79.80),
-- 5043 = 159.90*2+64.90 = 319.80+64.90 = 384.70
(5043,103,'Whey Protein Concentrado 1kg Chocolate',159.90,2, 319.80),
(5043,107,'Glutamina 500g',                        64.90, 1,  64.90),
-- 5044 = 289.90+109.90+89.90 = 489.70
(5044,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5044,110,'Termogênico Black Speed 120 Caps',     109.90, 1, 109.90),
(5044,105,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5045 = 159.90+64.90+49.90 = 274.70
(5045,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
(5045,107,'Glutamina 500g',                        64.90, 1,  64.90),
(5045,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5046 = 159.90*4+289.90*2+89.90 = 639.60+579.80+89.90 = 1309.30
(5046,103,'Whey Protein Concentrado 1kg Chocolate',159.90,4, 639.60),
(5046,104,'Whey Protein Blend 2kg Baunilha',      289.90, 2, 579.80),
(5046,105,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5047 = 179.90
(5047,111,'Proteína Vegana Chocolate 1kg',        179.90, 1, 179.90),
-- 5048 = 39.90*2+49.90 = 79.80+49.90 = 129.70
(5048,109,'Ômega 3 120 Cáps',                     39.90, 2,  79.80),
(5048,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5049 = 159.90+79.90+49.90 = 289.70
(5049,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
(5049,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5049,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5050 = 289.90+79.90 = 369.80
(5050,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5050,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5051 = 159.90*4+289.90*2+79.90*2 = 639.60+579.80+159.80 = 1379.20
(5051,103,'Whey Protein Concentrado 1kg Chocolate',159.90,4, 639.60),
(5051,104,'Whey Protein Blend 2kg Baunilha',      289.90, 2, 579.80),
(5051,101,'Creatina Monohidratada 500g',           79.90, 2, 159.80),

-- 5052 = 159.90
(5052,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
-- 5053 = 159.90+89.90 = 249.80
(5053,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
(5053,105,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5054 = 289.90+64.90+49.90 = 404.70
(5054,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5054,107,'Glutamina 500g',                        64.90, 1,  64.90),
(5054,108,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5055 = 289.90+179.90 = 469.80
(5055,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5055,111,'Proteína Vegana Chocolate 1kg',        179.90, 1, 179.90),
-- 5056 = 159.90+39.90 = 199.80
(5056,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
(5056,109,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5057 = 289.90+109.90+89.90 = 489.70
(5057,104,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5057,110,'Termogênico Black Speed 120 Caps',     109.90, 1, 109.90),
(5057,105,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5058 = 159.90*4+289.90*2+79.90*2 = 639.60+579.80+159.80 = 1379.20
(5058,103,'Whey Protein Concentrado 1kg Chocolate',159.90,4, 639.60),
(5058,104,'Whey Protein Blend 2kg Baunilha',      289.90, 2, 579.80),
(5058,101,'Creatina Monohidratada 500g',           79.90, 2, 159.80),
-- 5059 = 159.90+79.90 = 239.80
(5059,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90),
(5059,101,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5060 (aguardando_pagamento) = 159.90
(5060,103,'Whey Protein Concentrado 1kg Chocolate',159.90,1, 159.90);

-- =============================================================================
-- 7. ATUALIZA TOTAIS DOS PEDIDOS (recalcula a partir dos itens)
-- =============================================================================
UPDATE `orders` o
JOIN (
    SELECT order_id, SUM(subtotal) AS total_items
    FROM `order_items`
    WHERE order_id BETWEEN 5001 AND 5060
    GROUP BY order_id
) oi ON o.id = oi.order_id
SET o.subtotal = oi.total_items,
    o.total    = oi.total_items
WHERE o.id BETWEEN 5001 AND 5060;

-- =============================================================================
-- 8. ATUALIZA ESTATÍSTICAS DOS CLIENTES
-- =============================================================================
UPDATE `users` u
SET
    total_orders     = (
        SELECT COUNT(*) FROM `orders`
        WHERE user_id = u.id
          AND status NOT IN ('cancelado','aguardando_pagamento')
    ),
    total_spent      = (
        SELECT COALESCE(SUM(total), 0.00) FROM `orders`
        WHERE user_id = u.id
          AND status NOT IN ('cancelado','aguardando_pagamento')
    ),
    last_purchase_at = (
        SELECT MAX(created_at) FROM `orders`
        WHERE user_id = u.id
          AND status NOT IN ('cancelado','aguardando_pagamento')
    )
WHERE id BETWEEN 201 AND 220;

-- =============================================================================
-- 9. AVALIAÇÕES APROVADAS
-- =============================================================================
INSERT INTO `reviews`
  (`product_id`, `user_id`, `order_id`, `rating`, `comment`, `status`, `review_token`, `created_at`)
VALUES
(103, 201, 5001, 5, 'Melhor whey que já tomei! Sabor chocolate incrível, dissolve super bem e sem grumos. Já é o segundo pote que compro.',          'approved', UUID(), '2026-01-18 10:30:00'),
(105, 204, 5002, 5, 'BCAA de ótima qualidade. Sinto que reduziu minha dor muscular depois dos treinos pesados. Recomendo muito!',                     'approved', UUID(), '2026-01-22 14:15:00'),
(101, 202, 5004, 5, 'Creatina excelente, resultado visível em 2 semanas. Preço muito bom e entrega rápida.',                                          'approved', UUID(), '2026-01-28 09:00:00'),
(103, 209, 5009, 4, 'Bom produto, mas o sabor poderia ser um pouco mais intenso. Proteína ótima, boa quantidade. Entrega no prazo.',                   'approved', UUID(), '2026-02-16 11:45:00'),
(104, 210, 5010, 5, 'Whey 2kg durou bastante e o resultado foi excelente. Baunilha é saborosa sem ser enjoativa. Super recomendo!',                    'approved', UUID(), '2026-02-18 16:20:00'),
(107, 201, 5011, 5, 'Glutamina de qualidade, uso pós-treino e a recuperação melhorou muito. Embalagem boa e fechamento fácil.',                        'approved', UUID(), '2026-02-22 08:30:00'),
(104, 211, 5012, 5, 'Proteína top! Atlhetica de sempre entregando qualidade. Já é meu terceiro pedido nessa loja, atendimento perfeito.',              'approved', UUID(), '2026-02-24 12:00:00'),
(103, 212, 5014, 4, 'Whey muito bom, resultado satisfatório. Única crítica é o preço um pouco alto, mas vale a qualidade.',                            'approved', UUID(), '2026-03-02 10:15:00'),
(101, 205, 5013, 5, 'Creatina pura, sem sabor, dissolve bem. Percebi ganho de força nas primeiras semanas. Produto confiável!',                        'approved', UUID(), '2026-03-01 09:45:00'),
(106, 209, 5021, 4, 'Pré-treino forte! Cuidado com a quantidade na primeira vez. Foco e energia excelentes. Bateu bem no treino de perna.',            'approved', UUID(), '2026-03-20 15:00:00'),
(104, 211, 5020, 5, 'Meu whey favorito! Excelente custo-benefício e resultado rápido. Loja entrega sempre no prazo.',                                   'approved', UUID(), '2026-03-18 11:30:00'),
(103, 214, 5022, 5, 'Whey muito gostoso e com ótima quantidade de proteína por porção. Textura cremosa. Voltarei a comprar!',                          'approved', UUID(), '2026-03-23 14:00:00'),
(109, 201, 5026, 5, 'Ômega 3 de qualidade, sem gosto ruim de peixe, o que é raro. Ótima absorção. Uso diariamente.',                                  'approved', UUID(), '2026-04-01 08:45:00'),
(110, 217, 5030, 4, 'Termogênico bom, com estímulo moderado de energia. Não senti efeitos colaterais. Bom para quem é sensível à cafeína.',            'approved', UUID(), '2026-04-14 16:30:00'),
(103, 218, 5031, 5, 'Whey de excelente qualidade, proteína high end. Sabor chocolate perfeito. Comprei pela terceira vez nessa loja!',                 'approved', UUID(), '2026-04-17 10:00:00'),
(111, 201, 5032, 5, 'Proteína vegana surpreendente! Sem aquele gosto estranho que outras marcas têm. Textura ótima no shake. Recomendo!',              'approved', UUID(), '2026-04-20 13:15:00'),
(104, 211, 5033, 5, 'Mais uma compra satisfeita. Whey Atlhetica é o melhor do mercado. Resultado comprovado no treino.',                               'approved', UUID(), '2026-04-22 09:30:00'),
(106, 213, 5034, 3, 'Pré-treino razoável. Funcionou bem mas senti formigamento intenso por causa da beta-alanina. Não é pra todo mundo.',              'approved', UUID(), '2026-04-24 12:00:00'),
(103, 212, 5043, 5, 'Whey excelente! Comprei 2 potes dessa vez. Sabor chocolate é viciante. Entrega em 2 dias. Muito satisfeito!',                    'approved', UUID(), '2026-05-18 08:00:00'),
(104, 211, 5044, 5, 'Meu whey de sempre. Resultado consistente, sabor bom. Kamila sempre volta aqui porque a loja é confiável.',                       'approved', UUID(), '2026-05-21 10:45:00'),
(111, 218, 5047, 5, 'Proteína vegana incrível! Sustentou bem no pós-treino. Textura cremosa e sabor chocolate marcante. Já garanti o próximo.',        'approved', UUID(), '2026-05-28 14:30:00'),
(104, 210, 5050, 5, 'Segunda compra do Whey 2kg. Produto de altíssima qualidade. Entrega rápida e embalagem impecável. 10/10!',                        'approved', UUID(), '2026-06-06 09:15:00'),
(103, 219, 5053, 5, 'Whey muito bom! Primeira vez comprando nessa loja e já fui encantada pelo produto e atendimento. Com certeza volto!',             'approved', UUID(), '2026-06-18 11:00:00'),
(104, 201, 5054, 5, 'Minha compra de junho chegou rapidinho. Whey blend 2kg é minha escolha sempre. Loja 10 estrelas!',                               'approved', UUID(), '2026-06-22 16:00:00'),
(105, 214, 5056, 4, 'BCAA bom, cumpriu o que prometia. Tomei antes do treino e senti menos fadiga. Voltarei a comprar. Entrega rápida.',               'approved', UUID(), '2026-06-28 08:30:00');

-- =============================================================================
-- 10. NOTIFICAÇÕES DO PAINEL ADMIN
-- =============================================================================
INSERT INTO `notifications` (`type`, `title`, `message`, `read`, `created_at`) VALUES
('new_order',    'Novo pedido recebido',         'Pedido #5060 de Isabela Nunes Torres — R$ 159,90 (Boleto)',              0, '2026-06-28 09:31:00'),
('new_order',    'Novo pedido recebido',         'Pedido #5059 de Rafael Souza Pinto — R$ 239,80 (Pix)',                   0, '2026-06-25 16:45:00'),
('new_order',    'Novo pedido recebido',         'Pedido #5058 de Paulo Roberto Motta — R$ 1.379,20 (Cartão)',             0, '2026-06-20 08:12:00'),
('new_review',   'Nova avaliação para aprovar',  'Avaliação de 5 estrelas para Whey Protein Blend 2kg Baunilha',          0, '2026-06-22 16:01:00'),
('low_stock',    'Estoque baixo: Whey 2kg',      'Produto "Whey Protein Blend 2kg Baunilha" com apenas 34 unidades.',     0, '2026-06-15 08:00:00'),
('low_stock',    'Estoque baixo: Proteína Vegana','Produto "Proteína Vegana Chocolate 1kg" com apenas 28 unidades.',      0, '2026-06-10 08:00:00'),
('new_order',    'Novo pedido recebido',         'Pedido #5057 de Kamila Rodrigues — R$ 489,70 (Cartão)',                  1, '2026-06-17 13:39:00'),
('new_order',    'Novo pedido recebido',         'Pedido #5056 de Nathan Cruz Silva — R$ 199,80 (Pix)',                    1, '2026-06-14 09:06:00'),
('new_review',   'Nova avaliação para aprovar',  'Avaliação de 5 estrelas para BCAA 2400 240 Caps',                       1, '2026-06-28 08:31:00'),
('new_order',    'Novo pedido recebido',         'Pedido #5055 de Thiago Moura Carvalho — R$ 469,80 (Pix)',               1, '2026-06-11 15:24:00');

-- =============================================================================
-- Seed concluído.
-- 20 clientes | 60 pedidos | ~120 itens | 25 avaliações | 10 notificações
-- Receita total (excl. cancelados/aguardando): ~R$ 23.400
-- =============================================================================

SET foreign_key_checks = 1;
