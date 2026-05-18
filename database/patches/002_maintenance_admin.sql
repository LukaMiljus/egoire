-- Admin za maintenance login i admin panel
-- Korisnik: admin  |  Lozinka: TMEgoire2026!

INSERT INTO `admin_users` (`username`, `password_hash`, `role`, `is_active`)
VALUES (
    'admin',
    '$2y$12$ENPLhLGRq.omJJopLfde2.iGmWwKW/pPDZv4H9Lvtdrjnj.0gqM/2',
    'superadmin',
    1
)
ON DUPLICATE KEY UPDATE
    `password_hash` = VALUES(`password_hash`),
    `is_active` = 1;
