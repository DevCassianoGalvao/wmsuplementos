-- =============================================================================
-- Seed Demo — PATCH (execute se o seed_demo.sql parou no erro #1452)
-- Contém apenas as seções 6 a 10 com os erros corrigidos:
-- • order_items: product_id = NULL (campo nullable — evita FK constraint)
-- • reviews: subquery por slug para encontrar o product_id real no banco
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '-03:00';
SET foreign_key_checks = 0;

-- =============================================================================
-- 6. ITENS DOS PEDIDOS (product_id=NULL — sem dependência de FK)
-- =============================================================================
INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
-- 5001
(5001, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5001, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5002
(5002, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
(5002, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5003 (cancelado)
(5003, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5004
(5004, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
-- 5005
(5005, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 2, 319.80),
(5005, NULL,'Creatina Monohidratada 500g',           79.90, 2, 159.80),
(5005, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5006
(5006, NULL,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
(5006, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5007
(5007, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
-- 5008
(5008, NULL,'Creatina 300g',                         54.90, 1,  54.90),
(5008, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5009
(5009, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
(5009, NULL,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
-- 5010
(5010, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
-- 5011
(5011, NULL,'Glutamina 500g',                        64.90, 1,  64.90),
(5011, NULL,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5012
(5012, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5012, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5013
(5013, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5014
(5014, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
(5014, NULL,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5015
(5015, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 3, 479.70),
(5015, NULL,'Creatina Monohidratada 500g',           79.90, 2, 159.80),
(5015, NULL,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
-- 5016 (cancelado)
(5016, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
-- 5017
(5017, NULL,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
(5017, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
(5017, NULL,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5018
(5018, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5019
(5019, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
(5019, NULL,'Creatina 300g',                         54.90, 1,  54.90),
-- 5020
(5020, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
-- 5021
(5021, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
(5021, NULL,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5022
(5022, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
(5022, NULL,'Glutamina 500g',                        64.90, 1,  64.90),
-- 5023
(5023, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 2, 579.80),
(5023, NULL,'Creatina Monohidratada 500g',           79.90, 2, 159.80),
-- 5024
(5024, NULL,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
(5024, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5025
(5025, NULL,'Creatina 300g',                         54.90, 1,  54.90),
(5025, NULL,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5026
(5026, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5026, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5027
(5027, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 4, 639.60),
(5027, NULL,'Creatina Monohidratada 500g',           79.90, 3, 239.70),
(5027, NULL,'Ômega 3 120 Cáps',                     39.90, 2,  79.80),
-- 5028 (cancelado)
(5028, NULL,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
-- 5029
(5029, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
(5029, NULL,'Glutamina 500g',                        64.90, 1,  64.90),
-- 5030
(5030, NULL,'Termogênico Black Speed 120 Caps',     109.90, 1, 109.90),
(5030, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5030, NULL,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5031
(5031, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
(5031, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5031, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5032
(5032, NULL,'Proteína Vegana Chocolate 1kg',        179.90, 1, 179.90),
(5032, NULL,'Glutamina 500g',                        64.90, 1,  64.90),
-- 5033
(5033, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5033, NULL,'Termogênico Black Speed 120 Caps',     109.90, 1, 109.90),
-- 5034
(5034, NULL,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
-- 5035
(5035, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 4, 639.60),
(5035, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5035, NULL,'Pré-Treino Hades 300g',                119.90, 1, 119.90),
-- 5036
(5036, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
-- 5037
(5037, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
(5037, NULL,'Glutamina 500g',                        64.90, 1,  64.90),
-- 5038
(5038, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 3, 479.70),
(5038, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5038, NULL,'BCAA 2400 240 Caps',                    89.90, 2, 179.80),
-- 5039
(5039, NULL,'Proteína Vegana Chocolate 1kg',        179.90, 1, 179.90),
(5039, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5039, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5040 (cancelado)
(5040, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5041
(5041, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
(5041, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5041, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5042
(5042, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5042, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5042, NULL,'Ômega 3 120 Cáps',                     39.90, 2,  79.80),
-- 5043
(5043, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 2, 319.80),
(5043, NULL,'Glutamina 500g',                        64.90, 1,  64.90),
-- 5044
(5044, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5044, NULL,'Termogênico Black Speed 120 Caps',     109.90, 1, 109.90),
(5044, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5045
(5045, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
(5045, NULL,'Glutamina 500g',                        64.90, 1,  64.90),
(5045, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5046
(5046, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 4, 639.60),
(5046, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 2, 579.80),
(5046, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5047
(5047, NULL,'Proteína Vegana Chocolate 1kg',        179.90, 1, 179.90),
-- 5048
(5048, NULL,'Ômega 3 120 Cáps',                     39.90, 2,  79.80),
(5048, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5049
(5049, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
(5049, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
(5049, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5050
(5050, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5050, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5051
(5051, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 4, 639.60),
(5051, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 2, 579.80),
(5051, NULL,'Creatina Monohidratada 500g',           79.90, 2, 159.80),
-- 5052
(5052, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
-- 5053
(5053, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
(5053, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5054
(5054, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5054, NULL,'Glutamina 500g',                        64.90, 1,  64.90),
(5054, NULL,'Multivitamínico Sport 90 Caps',         49.90, 1,  49.90),
-- 5055
(5055, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5055, NULL,'Proteína Vegana Chocolate 1kg',        179.90, 1, 179.90),
-- 5056
(5056, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
(5056, NULL,'Ômega 3 120 Cáps',                     39.90, 1,  39.90),
-- 5057
(5057, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 1, 289.90),
(5057, NULL,'Termogênico Black Speed 120 Caps',     109.90, 1, 109.90),
(5057, NULL,'BCAA 2400 240 Caps',                    89.90, 1,  89.90),
-- 5058
(5058, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 4, 639.60),
(5058, NULL,'Whey Protein Blend 2kg Baunilha',      289.90, 2, 579.80),
(5058, NULL,'Creatina Monohidratada 500g',           79.90, 2, 159.80),
-- 5059
(5059, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90),
(5059, NULL,'Creatina Monohidratada 500g',           79.90, 1,  79.90),
-- 5060
(5060, NULL,'Whey Protein Concentrado 1kg Chocolate',159.90, 1, 159.90);

-- =============================================================================
-- 7. ATUALIZA TOTAIS DOS PEDIDOS
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
-- 9. AVALIAÇÕES (usa subquery por slug — funciona com qualquer product_id real)
-- INSERT IGNORE ignora silenciosamente se o produto não for encontrado
-- =============================================================================
INSERT IGNORE INTO `reviews`
  (`product_id`, `user_id`, `order_id`, `rating`, `comment`, `status`, `review_token`, `created_at`)
VALUES
((SELECT id FROM products WHERE slug='whey-concentrado-1kg-chocolate' LIMIT 1),       201,5001,5,'Melhor whey que já tomei! Sabor chocolate incrível, dissolve super bem e sem grumos. Já é o segundo pote que compro.','approved',UUID(),'2026-01-18 10:30:00'),
((SELECT id FROM products WHERE slug='bcaa-2400-240-caps' LIMIT 1),                   204,5002,5,'BCAA de ótima qualidade. Sinto que reduziu minha dor muscular depois dos treinos pesados. Recomendo muito!','approved',UUID(),'2026-01-22 14:15:00'),
((SELECT id FROM products WHERE slug='creatina-monohidratada-500g' LIMIT 1),          202,5004,5,'Creatina excelente, resultado visível em 2 semanas. Preço muito bom e entrega rápida.','approved',UUID(),'2026-01-28 09:00:00'),
((SELECT id FROM products WHERE slug='whey-concentrado-1kg-chocolate' LIMIT 1),       209,5009,4,'Bom produto, mas o sabor poderia ser um pouco mais intenso. Proteína ótima, boa quantidade. Entrega no prazo.','approved',UUID(),'2026-02-16 11:45:00'),
((SELECT id FROM products WHERE slug='whey-blend-2kg-baunilha' LIMIT 1),              210,5010,5,'Whey 2kg durou bastante e o resultado foi excelente. Baunilha é saborosa sem ser enjoativa. Super recomendo!','approved',UUID(),'2026-02-18 16:20:00'),
((SELECT id FROM products WHERE slug='glutamina-500g' LIMIT 1),                       201,5011,5,'Glutamina de qualidade, uso pós-treino e a recuperação melhorou muito. Embalagem boa e fechamento fácil.','approved',UUID(),'2026-02-22 08:30:00'),
((SELECT id FROM products WHERE slug='whey-blend-2kg-baunilha' LIMIT 1),              211,5012,5,'Proteína top! Atlhetica de sempre entregando qualidade. Já é meu terceiro pedido nessa loja, atendimento perfeito.','approved',UUID(),'2026-02-24 12:00:00'),
((SELECT id FROM products WHERE slug='whey-concentrado-1kg-chocolate' LIMIT 1),       212,5014,4,'Whey muito bom, resultado satisfatório. Única crítica é o preço um pouco alto, mas vale a qualidade.','approved',UUID(),'2026-03-02 10:15:00'),
((SELECT id FROM products WHERE slug='creatina-monohidratada-500g' LIMIT 1),          205,5013,5,'Creatina pura, sem sabor, dissolve bem. Percebi ganho de força nas primeiras semanas. Produto confiável!','approved',UUID(),'2026-03-01 09:45:00'),
((SELECT id FROM products WHERE slug='pre-treino-hades-300g' LIMIT 1),                209,5021,4,'Pré-treino forte! Cuidado com a quantidade na primeira vez. Foco e energia excelentes. Bateu bem no treino de perna.','approved',UUID(),'2026-03-20 15:00:00'),
((SELECT id FROM products WHERE slug='whey-concentrado-1kg-chocolate' LIMIT 1),       211,5020,5,'Meu whey favorito! Excelente custo-benefício e resultado rápido. Loja entrega sempre no prazo.','approved',UUID(),'2026-03-18 11:30:00'),
((SELECT id FROM products WHERE slug='whey-concentrado-1kg-chocolate' LIMIT 1),       214,5022,5,'Whey muito gostoso e com ótima quantidade de proteína por porção. Textura cremosa. Voltarei a comprar!','approved',UUID(),'2026-03-23 14:00:00'),
((SELECT id FROM products WHERE slug='omega-3-120-caps' LIMIT 1),                     201,5026,5,'Ômega 3 de qualidade, sem gosto ruim de peixe, o que é raro. Ótima absorção. Uso diariamente.','approved',UUID(),'2026-04-01 08:45:00'),
((SELECT id FROM products WHERE slug='termogenico-black-speed-120-caps' LIMIT 1),     217,5030,4,'Termogênico bom, com estímulo moderado de energia. Não senti efeitos colaterais. Bom para quem é sensível à cafeína.','approved',UUID(),'2026-04-14 16:30:00'),
((SELECT id FROM products WHERE slug='whey-concentrado-1kg-chocolate' LIMIT 1),       218,5031,5,'Whey de excelente qualidade, proteína high end. Sabor chocolate perfeito. Comprei pela terceira vez nessa loja!','approved',UUID(),'2026-04-17 10:00:00'),
((SELECT id FROM products WHERE slug='proteina-vegana-chocolate-1kg' LIMIT 1),        201,5032,5,'Proteína vegana surpreendente! Sem aquele gosto estranho que outras marcas têm. Textura ótima no shake. Recomendo!','approved',UUID(),'2026-04-20 13:15:00'),
((SELECT id FROM products WHERE slug='whey-blend-2kg-baunilha' LIMIT 1),              211,5033,5,'Mais uma compra satisfeita. Whey Atlhetica é o melhor do mercado. Resultado comprovado no treino.','approved',UUID(),'2026-04-22 09:30:00'),
((SELECT id FROM products WHERE slug='pre-treino-hades-300g' LIMIT 1),                213,5034,3,'Pré-treino razoável. Funcionou bem mas senti formigamento intenso por causa da beta-alanina. Não é pra todo mundo.','approved',UUID(),'2026-04-24 12:00:00'),
((SELECT id FROM products WHERE slug='whey-concentrado-1kg-chocolate' LIMIT 1),       212,5043,5,'Whey excelente! Comprei 2 potes dessa vez. Sabor chocolate é viciante. Entrega em 2 dias. Muito satisfeito!','approved',UUID(),'2026-05-18 08:00:00'),
((SELECT id FROM products WHERE slug='whey-blend-2kg-baunilha' LIMIT 1),              211,5044,5,'Meu whey de sempre. Resultado consistente, sabor bom. Kamila sempre volta aqui porque a loja é confiável.','approved',UUID(),'2026-05-21 10:45:00'),
((SELECT id FROM products WHERE slug='proteina-vegana-chocolate-1kg' LIMIT 1),        218,5047,5,'Proteína vegana incrível! Sustentou bem no pós-treino. Textura cremosa e sabor chocolate marcante.','approved',UUID(),'2026-05-28 14:30:00'),
((SELECT id FROM products WHERE slug='whey-blend-2kg-baunilha' LIMIT 1),              210,5050,5,'Segunda compra do Whey 2kg. Produto de altíssima qualidade. Entrega rápida e embalagem impecável. 10/10!','approved',UUID(),'2026-06-06 09:15:00'),
((SELECT id FROM products WHERE slug='whey-concentrado-1kg-chocolate' LIMIT 1),       219,5053,5,'Whey muito bom! Primeira vez comprando nessa loja e já fui encantada pelo produto e atendimento. Com certeza volto!','approved',UUID(),'2026-06-18 11:00:00'),
((SELECT id FROM products WHERE slug='whey-blend-2kg-baunilha' LIMIT 1),              201,5054,5,'Minha compra de junho chegou rapidinho. Whey blend 2kg é minha escolha sempre. Loja 10 estrelas!','approved',UUID(),'2026-06-22 16:00:00'),
((SELECT id FROM products WHERE slug='bcaa-2400-240-caps' LIMIT 1),                   214,5056,4,'BCAA bom, cumpriu o que prometia. Tomei antes do treino e senti menos fadiga. Voltarei a comprar.','approved',UUID(),'2026-06-28 08:30:00');

-- =============================================================================
-- 10. NOTIFICAÇÕES
-- =============================================================================
INSERT INTO `notifications` (`type`, `title`, `message`, `read`, `created_at`) VALUES
('new_order',  'Novo pedido recebido',          'Pedido #5060 de Isabela Nunes Torres — R$ 159,90 (Boleto)',          0,'2026-06-28 09:31:00'),
('new_order',  'Novo pedido recebido',          'Pedido #5059 de Rafael Souza Pinto — R$ 239,80 (Pix)',               0,'2026-06-25 16:45:00'),
('new_order',  'Novo pedido recebido',          'Pedido #5058 de Paulo Roberto Motta — R$ 1.379,20 (Cartão)',         0,'2026-06-20 08:12:00'),
('new_review', 'Nova avaliação para aprovar',   'Avaliação de 5 estrelas para Whey Protein Blend 2kg Baunilha',      0,'2026-06-22 16:01:00'),
('low_stock',  'Estoque baixo: Whey 2kg',       'Produto "Whey Protein Blend 2kg Baunilha" com apenas 34 unidades.', 0,'2026-06-15 08:00:00'),
('low_stock',  'Estoque baixo: Proteína Vegana','Produto "Proteína Vegana Chocolate 1kg" com apenas 28 unidades.',   0,'2026-06-10 08:00:00'),
('new_order',  'Novo pedido recebido',          'Pedido #5057 de Kamila Rodrigues — R$ 489,70 (Cartão)',              1,'2026-06-17 13:39:00'),
('new_order',  'Novo pedido recebido',          'Pedido #5056 de Nathan Cruz Silva — R$ 199,80 (Pix)',                1,'2026-06-14 09:06:00'),
('new_review', 'Nova avaliação para aprovar',   'Avaliação de 5 estrelas para BCAA 2400 240 Caps',                   1,'2026-06-28 08:31:00'),
('new_order',  'Novo pedido recebido',          'Pedido #5055 de Thiago Moura Carvalho — R$ 469,80 (Pix)',            1,'2026-06-11 15:24:00');

SET foreign_key_checks = 1;

-- Patch concluído. Dashboard e métricas devem estar populados.
