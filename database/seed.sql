USE retail_pos;
INSERT INTO categories(name) VALUES ('Drinks'),('Bakery'),('Dairy'),('Pantry');
INSERT INTO products(barcode,name,category_id,selling_price,reorder_level,stock_qty) VALUES
('100001','Coca-Cola 500ml',1,80,10,50),('100002','Bread 400g',2,65,10,30),('100003','Fresh Milk 500ml',3,60,10,8),
('100004','Sugar 1kg',4,170,8,25),('100005','Cooking Oil 1L',4,320,6,4);
INSERT INTO settings VALUES ('shop_name','Retail POS'),('kra_pin','P000000000X');
-- Users are created by:  php database/seed_users.php   (hashes passwords properly)
