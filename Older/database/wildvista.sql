CREATE DATABASE IF NOT EXISTS wildvista_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wildvista_db;


SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS audit_logs, notifications, payments, booking_items, bookings, activities, transports, accommodations, tours, destinations, settings, users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 phone VARCHAR(40) NULL,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('admin','operator','customer') NOT NULL DEFAULT 'customer',
 status ENUM('active','pending','suspended') NOT NULL DEFAULT 'active',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE destinations (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(150) NOT NULL,
 slug VARCHAR(180) NOT NULL UNIQUE,
 description TEXT NOT NULL,
 area VARCHAR(120) NULL,
 image VARCHAR(255) NULL,
 status ENUM('active','pending','draft') NOT NULL DEFAULT 'pending',
 created_by INT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tours (
 id INT AUTO_INCREMENT PRIMARY KEY,
 operator_id INT NULL,
 destination_id INT NULL,
 title VARCHAR(180) NOT NULL,
 slug VARCHAR(200) NOT NULL UNIQUE,
 short_description VARCHAR(255) NOT NULL,
 description TEXT NOT NULL,
 duration_days INT NOT NULL DEFAULT 1,
 base_price DECIMAL(12,2) NOT NULL DEFAULT 0,
 price_type ENUM('per_person','per_group') NOT NULL DEFAULT 'per_person',
 max_guests INT NOT NULL DEFAULT 6,
 image VARCHAR(255) NULL,
 status ENUM('active','pending','draft','rejected') NOT NULL DEFAULT 'pending',
 featured TINYINT(1) NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(operator_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(destination_id) REFERENCES destinations(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE accommodations (
 id INT AUTO_INCREMENT PRIMARY KEY,
 operator_id INT NULL,
 name VARCHAR(180) NOT NULL,
 type VARCHAR(80) NOT NULL,
 location VARCHAR(180) NOT NULL,
 description TEXT NOT NULL,
 amenities TEXT NULL,
 price_per_night DECIMAL(12,2) NOT NULL DEFAULT 0,
 max_guests INT NOT NULL DEFAULT 2,
 image VARCHAR(255) NULL,
 status ENUM('active','pending','draft','rejected') NOT NULL DEFAULT 'pending',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(operator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE transports (
 id INT AUTO_INCREMENT PRIMARY KEY,
 operator_id INT NULL,
 name VARCHAR(180) NOT NULL,
 type VARCHAR(80) NOT NULL,
 description TEXT NOT NULL,
 capacity INT NOT NULL DEFAULT 4,
 pickup_area VARCHAR(180) NULL,
 price DECIMAL(12,2) NOT NULL DEFAULT 0,
 image VARCHAR(255) NULL,
 status ENUM('active','pending','draft','rejected') NOT NULL DEFAULT 'pending',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(operator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE activities (
 id INT AUTO_INCREMENT PRIMARY KEY,
 operator_id INT NULL,
 name VARCHAR(180) NOT NULL,
 description TEXT NOT NULL,
 duration_hours DECIMAL(5,1) NOT NULL DEFAULT 2,
 price DECIMAL(12,2) NOT NULL DEFAULT 0,
 image VARCHAR(255) NULL,
 status ENUM('active','pending','draft','rejected') NOT NULL DEFAULT 'pending',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(operator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE bookings (
 id INT AUTO_INCREMENT PRIMARY KEY,
 reference VARCHAR(30) NOT NULL UNIQUE,
 customer_id INT NULL,
 tour_id INT NOT NULL,
 booking_date DATE NOT NULL,
 guests INT NOT NULL DEFAULT 1,
 accommodation_id INT NULL,
 accommodation_nights INT NOT NULL DEFAULT 0,
 transport_id INT NULL,
 total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
 status ENUM('pending_payment','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending_payment',
 payment_status ENUM('unpaid','pending','paid','failed','refunded') NOT NULL DEFAULT 'unpaid',
 customer_name VARCHAR(120) NOT NULL,
 customer_email VARCHAR(190) NOT NULL,
 customer_phone VARCHAR(40) NULL,
 notes TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(customer_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(tour_id) REFERENCES tours(id) ON DELETE RESTRICT,
 FOREIGN KEY(accommodation_id) REFERENCES accommodations(id) ON DELETE SET NULL,
 FOREIGN KEY(transport_id) REFERENCES transports(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE booking_items (
 id INT AUTO_INCREMENT PRIMARY KEY,
 booking_id INT NOT NULL,
 item_type ENUM('tour','accommodation','transport','activity','extra') NOT NULL,
 item_id INT NULL,
 item_name VARCHAR(180) NOT NULL,
 qty DECIMAL(10,2) NOT NULL DEFAULT 1,
 unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
 total_price DECIMAL(12,2) NOT NULL DEFAULT 0,
 FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
 id INT AUTO_INCREMENT PRIMARY KEY,
 booking_id INT NOT NULL,
 provider VARCHAR(120) NOT NULL DEFAULT 'manual',
 method VARCHAR(80) NOT NULL DEFAULT 'manual',
 transaction_ref VARCHAR(160) NULL,
 amount DECIMAL(12,2) NOT NULL DEFAULT 0,
 status ENUM('initiated','pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
 proof_path VARCHAR(255) NULL,
 customer_note TEXT NULL,
 submitted_at DATETIME NULL,
 verified_by INT NULL,
 verified_at DATETIME NULL,
 paid_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
 FOREIGN KEY(verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
 id INT AUTO_INCREMENT PRIMARY KEY,
 booking_id INT NULL,
 recipient_email VARCHAR(190) NULL,
 recipient_phone VARCHAR(40) NULL,
 channel ENUM('email','sms') NOT NULL,
 subject VARCHAR(180) NULL,
 message TEXT NOT NULL,
 status ENUM('queued','sent','failed') NOT NULL DEFAULT 'queued',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 action VARCHAR(160) NOT NULL,
 entity VARCHAR(80) NOT NULL,
 entity_id INT NULL,
 meta TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE settings (
 id INT AUTO_INCREMENT PRIMARY KEY,
 key_name VARCHAR(80) NOT NULL UNIQUE,
 value TEXT NULL
) ENGINE=InnoDB;

INSERT INTO users(name,email,phone,password_hash,role,status) VALUES
('Akily','akilykaaya@gmail.com','+255 794 872 433','$2y$12$7thaqAfR6BkgXaVYDC/WFeiQSpANOXoGbhqE.15ofmxPj.adiAuY2', 'admin','active'),
('Patrick','patrick@wildvista.test','+255 760 000 001','$2y$12$7thaqAfR6BkgXaVYDC/WFeiQSpANOXoGbhqE.15ofmxPj.adiAuY2', 'operator','active'),
('Aqil','aqil@wildvista.test','+255 760 000 002','$2y$12$7thaqAfR6BkgXaVYDC/WFeiQSpANOXoGbhqE.15ofmxPj.adiAuY2', 'customer','active');

INSERT INTO settings(key_name,value) VALUES
('site_name','Wildvista'),('site_tagline','Explore Arusha Your Way'),('support_email','akilykaaya@gmail.com'),('support_phone','+255 794 872 433'),('currency','USD'),('email_provider','SMTP not configured'),('sms_provider','SMS API not configured'),('payment_provider','Manual verification enabled'),('payment_mobile_money','Pay using the Wildvista business number shown here. Add your real Tigo Pesa/Airtel Money/M-Pesa number from Admin → Settings.'),('payment_bank','Bank transfer details will appear here after the Wildvista owner configures the bank account.'),('payment_control_number','Use the payment/control number issued by Wildvista. The customer must submit the transaction/reference after payment.'),('payment_paypal','PayPal manual verification is available: send payment to the configured PayPal account and submit the PayPal transaction ID.'),('paypal_client_id',''),('paypal_secret',''));

INSERT INTO destinations(name,slug,description,area,image,status,created_by) VALUES
('Arusha City','arusha-city','A lively gateway to northern safari country, full of food, culture, craft markets and easy access to outdoor experiences.','Arusha','hero-clean2.jpg','active',1),
('Mount Meru','mount-meru','A dramatic mountain adventure overlooking Arusha, with forest trails, viewpoints and guided climbs.','Arusha','mountain-card.jpg','active',1),
('Arusha National Park','arusha-national-park','A compact and scenic park known for forest, lakes, giraffes and Mount Meru landscapes.','Near Arusha','hero-clean2.jpg','active',1),
('Cultural Arusha','cultural-arusha','Immersive local experiences, village visits, food, craft and storytelling.','Arusha','activity-card.jpg','active',1),
('Arusha Escapes','arusha-escapes','Relaxed outdoor experiences around Arusha, built for flexible day trips and weekend escapes.','Arusha','activity-card.jpg','pending',1),
('Partner Listings — Coming Soon','partner-listings-coming-soon','More independently operated stays and services are being prepared for Wildvista verification.','Arusha','stay-card.jpg','pending',1);

INSERT INTO tours(operator_id,destination_id,title,slug,short_description,description,duration_days,base_price,price_type,max_guests,image,status,featured) VALUES
(2,2,'Mount Meru Wild Escape','mount-meru-wild-escape','A scenic mountain experience with guided trails and big views.','A flexible guided Mount Meru experience designed for travellers who want mountain time, nature and memorable viewpoints without a complicated booking process.',1,640,'per_person',6,'mountain-card.jpg','active',1),
(2,3,'5 Days Northern Circuit Safari','5-days-northern-circuit-safari','Wildlife, landscapes and unforgettable Arusha departure-to-return safari days.','A five-day Arusha-based safari journey built around wildlife, scenic drives, professional guiding and flexible add-ons for accommodation and transport.',5,1250,'per_person',6,'safari-card.jpg','active',1),
(2,4,'Arusha Culture & Makers Day','arusha-culture-makers-day','Meet makers, taste local food and experience Arusha beyond the safari.','A full-day cultural route connecting local food, craft and storytelling in and around Arusha.',1,180,'per_person',8,'activity-card.jpg','active',1),
(NULL,1,'Lodge-to-Park Day Safari','lodge-to-park-day-safari','A pending sample package awaiting an active operator.','Demo listing prepared for the Wildvista catalogue.','1',260,'per_person',6,'hero-clean2.jpg','pending',0);

INSERT INTO accommodations(operator_id,name,type,location,description,amenities,price_per_night,max_guests,image,status) VALUES
(2,'Arusha Coffee Lodge','Lodge','Arusha','A calm lodge-style stay with gardens, spacious rooms and easy access to Arusha-based departures.','Breakfast, Wi-Fi, Restaurant, Pool, Parking',250,2,'stay-card.jpg','active'),
(2,'The Baobab Hotel','Hotel','Arusha','A comfortable city stay suited to travellers who want a central base before or after a trip.','Wi-Fi, Restaurant, Parking, Airport transfer on request',160,2,'stay-card.jpg','active'),
(NULL,'Meru Horizon Camp','Camp','Near Arusha','Sample accommodation listing prepared for future verification and activation.','Tents, Dining area, Nature views',220,3,'activity-card.jpg','pending'),
(NULL,'Private Arusha Villa','Private accommodation','Arusha','A demo private-stay listing. The owner can later claim and manage this listing through the operator workflow.','Kitchen, Wi-Fi, Parking, Private rooms',180,4,'stay-card.jpg','pending');

INSERT INTO transports(operator_id,name,type,description,capacity,pickup_area,price,image,status) VALUES
(2,'4×4 Safari Vehicle','Safari 4×4','Comfortable safari vehicle option for Arusha departures, sightseeing and park trips.','6','Arusha / Airport',180,'safari-card.jpg','active'),
(2,'Private City SUV','Private SUV','Private airport transfer and city mobility option for small groups.','4','Arusha / Kilimanjaro Airport',75,'stay-card.jpg','active'),
(NULL,'Shared Shuttle','Shared transfer','Pending demo transport listing that may be activated by an approved provider.','8','Airport / Arusha',35,'hero-clean2.jpg','pending');

INSERT INTO activities(operator_id,name,description,duration_hours,price,image,status) VALUES
(2,'Arusha Food & Makers Walk','Taste local flavours, see makers at work and explore Arusha with a local guide.',4,70,'activity-card.jpg','active'),
(2,'Sunset Mount Meru View','A relaxed scenic experience designed around viewpoints and golden-hour photography.',3,55,'mountain-card.jpg','active'),
(NULL,'Private Coffee Experience','Pending sample activity waiting for an operator to activate it.',2,45,'activity-card.jpg','pending');

INSERT INTO bookings(reference,customer_id,tour_id,booking_date,guests,accommodation_id,accommodation_nights,transport_id,total_amount,status,payment_status,customer_name,customer_email,customer_phone,notes) VALUES
('WV-AQIL01',3,2,'2026-10-15',2,1,4,1,3680,'pending_payment','unpaid','Aqil','aqil@wildvista.test','+255 760 000 002','Sample booking for demo dashboard.');
INSERT INTO booking_items(booking_id,item_type,item_id,item_name,qty,unit_price,total_price) VALUES
(1,'tour',2,'5 Days Northern Circuit Safari',2,1250,2500),
(1,'accommodation',1,'Arusha Coffee Lodge',4,250,1000),
(1,'transport',1,'4×4 Safari Vehicle',1,180,180);
