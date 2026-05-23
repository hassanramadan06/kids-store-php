-- =============================================================
--  Luluat Almisbah — Sample / dummy data
--  Default admin: username = admin   password = Admin@12345
--  Default user : email    = user@example.com  password = User@12345
-- =============================================================
USE `kids_store`;

-- ---------- admins -------------------------------------------
INSERT INTO `admins` (`username`, `email`, `password_hash`, `full_name`, `is_active`) VALUES
-- bcrypt hash of "Admin@12345"
('admin', 'admin@kidsstore.local',
 '$2y$10$CiUdqJpHiZAXuru/m3TCoOUSt7SZdESX6cu9eGwYsOS3HCZJPUqWq',
 'Store Administrator', 1);

-- ---------- users --------------------------------------------
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `address`, `city`) VALUES
-- bcrypt hash of "User@12345"
('Demo Customer', 'user@example.com', '+201000000000',
 '$2y$10$jp/yqSblI2kord.r1iZKROb5Pz62fYyoNvM5LA1yzTcjQtbuCoc22',
 '12 Tahrir St.', 'Cairo');

-- ---------- sections -----------------------------------------
INSERT INTO `sections` (`id`, `name_ar`, `name_en`, `slug`, `icon`, `sort_order`) VALUES
(1,'ملابس الأطفال (أولاد وبنات)','Baby Clothing (Boys & Girls)','baby-clothing','baby',1),
(2,'مستلزمات الأطفال','Baby Essentials','baby-essentials','bottle',2),
(3,'ملابس الأولاد','Boys Clothing','boys-clothing','boy',3),
(4,'ملابس البنات','Girls Clothing','girls-clothing','girl',4),
(5,'هدايا المواليد','Newborn Gifts','newborn-gifts','gift',5),
(6,'غرفة نوم الطفل','Baby Bedroom','baby-bedroom','bed',6),
(7,'عروض حصرية','Exclusive Offers','exclusive-offers','tag',7);

-- ---------- categories ---------------------------------------
INSERT INTO `categories` (`id`,`section_id`,`name_ar`,`name_en`,`slug`,`sort_order`) VALUES
-- Baby Clothing (Boys & Girls)
(1, 1,'ملابس داخلية','Underwear','baby-underwear',1),
(2, 1,'بيجامات','Pajamas','baby-pajamas',2),
(3, 1,'ملابس','Clothes','baby-clothes',3),
-- Baby Essentials
(4, 2,'زجاجات الرضاعة','Feeding Bottles','feeding-bottles',1),
(5, 2,'حقائب','Bags','baby-bags',2),
(6, 2,'حمالات الأطفال','Baby Carriers','baby-carriers',3),
(7, 2,'فوط','Towels','baby-towels',4),
(8, 2,'إكسسوارات','Accessories','baby-accessories',5),
-- Boys Clothing
(9, 3,'ملابس داخلية','Underwear','boys-underwear',1),
(10,3,'بيجامات','Pajamas','boys-pajamas',2),
(11,3,'ملابس','Clothes','boys-clothes',3),
-- Girls Clothing
(12,4,'ملابس داخلية','Underwear','girls-underwear',1),
(13,4,'بيجامات','Pajamas','girls-pajamas',2),
(14,4,'ملابس','Clothes','girls-clothes',3),
-- Newborn Gifts
(15,5,'مجموعات الهدايا','Gift Sets','gift-sets',1),
-- Baby Bedroom
(16,6,'أسرة الأطفال','Baby Beds','baby-beds',1),
(17,6,'أسرة يدوية','Manual Beds','manual-beds',2),
(18,6,'مراتب','Mattresses','mattresses',3),
(19,6,'ناموسيات','Mosquito Nets','mosquito-nets',4),
-- Exclusive Offers
(20,7,'عروض خاصة','Special Deals','special-deals',1);

-- ---------- offers (homepage banners) ------------------------
INSERT INTO `offers` (`title_ar`,`title_en`,`description_ar`,`description_en`,`discount_percent`,`is_active`) VALUES
('خصم 30% على ملابس الأطفال','30% OFF Baby Clothing','تشكيلة ربيع 2025 بأسعار لا تقاوم','Spring 2025 collection at irresistible prices',30,1),
('شحن مجاني للطلبات فوق 1000ج','Free Shipping over 1000 EGP','استمتعي بالشحن المجاني لكل طلباتك','Enjoy free shipping on all your orders',0,1),
('عروض المواليد الجدد','Newborn Bundles','مجموعات هدايا متكاملة بخصم 25%','Complete gift bundles with 25% off',25,1);

-- ---------- products (24 sample products, 2/section avg) -----
INSERT INTO `products` (`id`,`section_id`,`category_id`,`name_ar`,`name_en`,`slug`,`sku`,`description_ar`,`description_en`,`price`,`discount_price`,`stock`,`sizes`,`colors`,`is_featured`) VALUES
(1,1,2,'بيجاما قطن ناعمة للأطفال','Soft Cotton Baby Pajamas','soft-cotton-baby-pajamas','PJ-001',
 'بيجاما من القطن العضوي 100% بنعومة فائقة وألوان زاهية تناسب بشرة الطفل الحساسة، مريحة في النوم والاستخدام اليومي.',
 '100% organic cotton pajamas with extra-soft feel and lively colors, gentle on babies sensitive skin, comfortable for sleep and daily wear.',
 250.00, 199.00, 50,'NB,3M,6M,9M,12M','Pink,Blue,Yellow',1),

(2,1,3,'سيت ملابس للأطفال 3 قطع','3-Piece Baby Outfit Set','3-piece-baby-outfit-set','SET-002',
 'مجموعة ملابس مكونة من 3 قطع بألوان متناسقة، مناسبة للمناسبات والاستخدام اليومي.',
 '3-piece coordinated outfit set perfect for special occasions and daily wear.',
 480.00, 380.00, 35,'3M,6M,9M,12M,18M','White,Beige',1),

(3,1,1,'ملابس داخلية قطنية أطفال','Baby Cotton Underwear Pack','baby-cotton-underwear','UW-003',
 'عبوة 5 قطع من الملابس الداخلية القطنية للأطفال بألوان متنوعة.',
 'Pack of 5 cotton underwear for babies in assorted colors.',
 180.00, NULL, 80,'3M,6M,12M,18M,24M','Multi',0),

(4,2,4,'زجاجة رضاعة مضادة للمغص','Anti-Colic Feeding Bottle','anti-colic-feeding-bottle','FB-004',
 'زجاجة رضاعة 240مل مضادة للمغص بنظام تهوية متطور وحلمة سيليكون آمنة.',
 '240ml anti-colic feeding bottle with advanced venting system and safe silicone nipple.',
 120.00, 99.00, 100,'240ml','Pink,Blue,Green',1),

(5,2,5,'حقيبة أمومة فاخرة متعددة الجيوب','Premium Multi-Pocket Diaper Bag','premium-diaper-bag','BAG-005',
 'حقيبة أمومة عملية مع 12 جيب، مقاومة للماء وسهلة التنظيف.',
 'Practical diaper bag with 12 pockets, water-resistant and easy to clean.',
 850.00, 699.00, 25,'One Size','Black,Gray,Pink',1),

(6,2,6,'حمالة أطفال إرغونومية','Ergonomic Baby Carrier','ergonomic-baby-carrier','BC-006',
 'حمالة أطفال إرغونومية لدعم الظهر بأربع أوضاع حمل مختلفة.',
 'Ergonomic baby carrier with back support and 4 different carrying positions.',
 1200.00, 999.00, 18,'One Size','Gray,Navy',1),

(7,2,7,'فوط استحمام قطنية ناعمة','Soft Cotton Bath Towels','soft-bath-towels','TW-007',
 'مجموعة 3 فوط استحمام للأطفال 100% قطن مع كاب لطيف.',
 'Set of 3 baby bath towels, 100% cotton with cute hooded design.',
 220.00, 175.00, 60,'70x70','Pink,Blue,Yellow',0),

(8,2,8,'مجموعة إكسسوارات حديثي الولادة','Newborn Accessories Set','newborn-accessories-set','ACC-008',
 'مجموعة إكسسوارات شاملة للمواليد الجدد: قبعة، قفازات، جوارب.',
 'Complete newborn accessories: hat, mittens, and socks.',
 150.00, NULL, 120,'NB,0-3M','White,Pink,Blue',0),

(9,3,11,'تيشيرت أولاد قطن','Boys Cotton T-Shirt','boys-cotton-tshirt','BT-009',
 'تيشيرت أولاد من القطن المريح بطباعة عصرية.',
 'Comfortable cotton t-shirt for boys with trendy print.',
 180.00, 149.00, 70,'2Y,3Y,4Y,5Y,6Y','White,Black,Navy',1),

(10,3,10,'بيجاما أولاد سفنجية شتوية','Boys Winter Fleece Pajama','boys-winter-pajama','BPJ-010',
 'بيجاما شتوية دافئة من قماش سفنجي ناعم.',
 'Warm winter pajama in soft fleece fabric.',
 320.00, 269.00, 40,'3Y,4Y,5Y,6Y,7Y','Blue,Gray',0),

(11,3,9,'سراويل داخلية أولاد - 5 قطع','Boys Underwear 5-Pack','boys-underwear-5pack','BUW-011',
 'عبوة 5 سراويل داخلية للأولاد من القطن النقي.',
 'Pack of 5 boys cotton briefs.',
 165.00, NULL, 90,'3Y,4Y,5Y,6Y,7Y','Multi',0),

(12,3,11,'بنطلون جينز أولاد','Boys Denim Jeans','boys-denim-jeans','BJ-012',
 'جينز أولاد بقصة عصرية ونعومة عالية.',
 'Modern fit boys denim jeans with extra softness.',
 280.00, 229.00, 55,'3Y,4Y,5Y,6Y,7Y,8Y','Blue,Black',1),

(13,4,14,'فستان بنات أنيق','Elegant Girls Dress','elegant-girls-dress','GD-013',
 'فستان بناتي راقي مزين بالدانتيل، مناسب للحفلات والمناسبات.',
 'Elegant girls dress with lace details, perfect for parties and occasions.',
 420.00, 349.00, 30,'2Y,3Y,4Y,5Y,6Y','Pink,White,Lavender',1),

(14,4,13,'بيجاما بنات يونيكورن','Girls Unicorn Pajamas','girls-unicorn-pajamas','GPJ-014',
 'بيجاما بنات بطباعة يونيكورن جميلة من القطن الناعم.',
 'Girls pajamas with cute unicorn prints in soft cotton.',
 270.00, 215.00, 45,'3Y,4Y,5Y,6Y','Pink,Purple',1),

(15,4,12,'ملابس داخلية بناتي 5 قطع','Girls Underwear 5-Pack','girls-underwear-5pack','GUW-015',
 'عبوة 5 ملابس داخلية بناتي بطبعات لطيفة.',
 'Pack of 5 girls cotton underwear with cute prints.',
 175.00, NULL, 85,'3Y,4Y,5Y,6Y,7Y','Multi',0),

(16,4,14,'بلوزة بنات صيفية','Girls Summer Blouse','girls-summer-blouse','GB-016',
 'بلوزة بناتي صيفية بألوان زاهية وقماش مريح.',
 'Summer girls blouse in bright colors and comfy fabric.',
 195.00, 159.00, 60,'3Y,4Y,5Y,6Y','Yellow,Mint,Coral',0),

(17,5,15,'مجموعة هدية المولود الجديد - فاخرة','Premium Newborn Gift Set','premium-newborn-gift','NG-017',
 'مجموعة هدية فاخرة للمواليد الجدد: 5 قطع ملابس + بطانية + إكسسوارات.',
 'Premium newborn gift set: 5 clothing pieces + blanket + accessories.',
 990.00, 799.00, 20,'NB,0-3M','Pink,Blue,Neutral',1),

(18,5,15,'سلة هدايا حديثي الولادة الأساسية','Essential Newborn Gift Basket','essential-newborn-gift','NG-018',
 'سلة هدايا أساسية مع كل ما يحتاجه المولود الجديد.',
 'Essential gift basket with everything a newborn needs.',
 650.00, 549.00, 25,'NB','Neutral',1),

(19,6,16,'سرير أطفال خشبي محول','Convertible Wooden Baby Crib','convertible-baby-crib','CR-019',
 'سرير أطفال خشبي يتحول إلى سرير أطفال عند الكبر، آمن ومتين.',
 'Wooden crib that converts to a toddler bed, safe and durable.',
 3500.00, 2999.00, 12,'120x60','Natural,White',1),

(20,6,17,'سرير يدوي محمول','Portable Manual Baby Bed','portable-manual-bed','MB-020',
 'سرير يدوي خفيف وسهل النقل للسفر والاستخدام في أي مكان.',
 'Lightweight portable bed easy to carry for travel and any space.',
 1800.00, 1499.00, 15,'90x50','Beige,Gray',0),

(21,6,18,'مرتبة أطفال طبية','Orthopedic Baby Mattress','orthopedic-baby-mattress','MT-021',
 'مرتبة طبية متعددة الطبقات لدعم نمو الطفل بشكل صحي.',
 'Multi-layer orthopedic mattress to support healthy baby growth.',
 1200.00, 999.00, 22,'120x60,90x50','White',0),

(22,6,19,'ناموسية سرير أطفال','Baby Bed Mosquito Net','baby-mosquito-net','MN-022',
 'ناموسية شفافة بتركيب سهل لحماية الطفل أثناء النوم.',
 'Transparent easy-install mosquito net to protect baby during sleep.',
 250.00, 199.00, 50,'Universal','White,Pink,Blue',0),

(23,7,20,'عرض الجمعة البيضاء - 50% خصم','Black Friday Mega Bundle','black-friday-bundle','OFR-023',
 'مجموعة شاملة بخصم 50% لفترة محدودة جدًا.',
 'Mega bundle at 50% off for a very limited time.',
 1500.00, 749.00, 10,'Mixed','Multi',1),

(24,7,20,'باقة الصيف العائلية','Family Summer Pack','family-summer-pack','OFR-024',
 'باقة صيفية عائلية تشمل ملابس وإكسسوارات بسعر مخفض.',
 'Family summer pack with clothing and accessories at a discounted price.',
 1100.00, 849.00, 18,'Mixed','Multi',1);

-- ---------- product_images (4 per product, using placeholder images) ----------
-- Local placeholder paths are filled by the seeder setup.  At install time the
-- README explains how to drop your own images into /uploads/products/ . For now
-- we point at a single shared placeholder served from /assets/images/.
INSERT INTO `product_images` (`product_id`,`image_path`,`is_primary`,`sort_order`)
SELECT p.id, CONCAT('assets/images/placeholder-', ((p.id - 1) % 6) + 1, '.svg'), 1, 1 FROM products p;
INSERT INTO `product_images` (`product_id`,`image_path`,`is_primary`,`sort_order`)
SELECT p.id, CONCAT('assets/images/placeholder-', (p.id % 6) + 1, '.svg'), 0, 2 FROM products p;
INSERT INTO `product_images` (`product_id`,`image_path`,`is_primary`,`sort_order`)
SELECT p.id, CONCAT('assets/images/placeholder-', ((p.id + 1) % 6) + 1, '.svg'), 0, 3 FROM products p;
INSERT INTO `product_images` (`product_id`,`image_path`,`is_primary`,`sort_order`)
SELECT p.id, CONCAT('assets/images/placeholder-', ((p.id + 2) % 6) + 1, '.svg'), 0, 4 FROM products p;

-- ---------- settings -----------------------------------------
INSERT INTO `settings` (`key_name`,`value`) VALUES
 ('site_name_ar','لؤلؤة المصباح'),
 ('site_name_en','Luluat Almisbah'),
 ('site_tagline_ar','كل ما يحتاجه طفلك في مكان واحد'),
 ('site_tagline_en','Everything your child needs in one place'),
 ('contact_email','contact@luluat-almisbah.local'),
 ('contact_phone','+20 100 000 0000'),
 ('whatsapp','+20 100 000 0000'),
 ('address_ar','شارع التحرير، القاهرة، مصر'),
 ('address_en','Tahrir St., Cairo, Egypt'),
 ('shipping_flat','40'),
 ('free_shipping_threshold','1000'),
 ('currency_ar','ج.م'),
 ('currency_en','EGP'),
 ('facebook','https://facebook.com/'),
 ('instagram','https://instagram.com/'),
 ('twitter','https://twitter.com/'),
 ('tiktok','https://tiktok.com/');
