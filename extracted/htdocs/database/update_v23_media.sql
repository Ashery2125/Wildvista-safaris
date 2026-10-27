-- Wildvista V2.3 media/content update
-- Run this in phpMyAdmin against the EXISTING wildvista_db.
-- No tables are dropped or recreated.

-- Make the main northern safari destinations use real photographic cover URLs.
INSERT INTO destinations (name, slug, description, area, image, status, created_by) VALUES
('Arusha National Park','arusha-national-park','Forest, lakes, giraffes and Mount Meru scenery close to Arusha.','Arusha','https://images.unsplash.com/photo-1785687047548-3123fd6ee83b?auto=format&fit=crop&w=1800&q=85','active',1),
('Tarangire National Park','tarangire-national-park','Baobabs, elephants and open savannah landscapes on the northern safari circuit.','Manyara Region','https://images.unsplash.com/photo-1776772922039-825878fe2324?auto=format&fit=crop&w=1800&q=85','active',1),
('Lake Manyara National Park','lake-manyara-national-park','A scenic Rift Valley park known for wildlife, woodland and the lake ecosystem.','Manyara Region','https://images.unsplash.com/photo-1693247670444-d6eff1d03136?auto=format&fit=crop&w=1800&q=85','active',1),
('Ngorongoro Crater','ngorongoro-crater','A dramatic crater landscape with dense wildlife and exceptional viewpoints.','Arusha Region','https://images.unsplash.com/photo-1634662058604-b76ce3b90e6e?auto=format&fit=crop&w=1800&q=85','active',1),
('Serengeti National Park','serengeti-national-park','Vast plains and iconic wildlife experiences in northern Tanzania.','Mara / Simiyu / Arusha safari route','https://images.unsplash.com/photo-1689479665460-dac7ee513eeb?auto=format&fit=crop&w=1800&q=85','active',1),
('Kilimanjaro','kilimanjaro','Tanzania’s iconic mountain and a major northern adventure destination.','Kilimanjaro Region','https://images.unsplash.com/photo-1709371126801-abe434d13cac?auto=format&fit=crop&w=1800&q=85','active',1),
('Lake Natron','lake-natron','A remote northern landscape known for dramatic scenery, wildlife and flamingo habitats.','Arusha Region','hero-clean2.jpg','active',1),
('Mikumi National Park','mikumi-national-park','Open plains and classic Tanzanian wildlife experiences in a park south of the northern circuit.','Morogoro Region','https://images.unsplash.com/photo-1783531800856-8d3e3f819fac?auto=format&fit=crop&w=1800&q=85','pending',1),
('Zanzibar','zanzibar','Island beaches, ocean experiences and Stone Town culture. Kept pending while Wildvista remains Arusha-first.','Zanzibar, Tanzania','https://images.unsplash.com/photo-1637681126455-25b23bf7fc13?auto=format&fit=crop&w=1800&q=85','pending',1)
ON DUPLICATE KEY UPDATE
 name=VALUES(name), description=VALUES(description), area=VALUES(area), image=VALUES(image);

-- Replace the old placeholder image for the existing Arusha entries where appropriate.
UPDATE destinations SET image='https://images.unsplash.com/photo-1785687047548-3123fd6ee83b?auto=format&fit=crop&w=1800&q=85' WHERE slug='arusha-national-park';
UPDATE destinations SET image='https://images.unsplash.com/photo-1709371126801-abe434d13cac?auto=format&fit=crop&w=1800&q=85' WHERE slug='mount-meru';

-- New support number requested by the owner.
INSERT INTO settings(key_name,value) VALUES
('support_phone','+255 794 872 433 / +255 621 385 106')
ON DUPLICATE KEY UPDATE value=VALUES(value);
