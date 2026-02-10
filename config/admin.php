<?php
return [
    'username' => getenv('ADMIN_USERNAME') ?: 'victoryadmin',
    // Default password: Victory2024!
    'password_hash' => getenv('ADMIN_PASSWORD_HASH') ?: '$2y$10$/F8YqwEIyDVUmRNhSC1W4Omo3jlNSyb4LIceQew7nR6sfkVTG6/Ju',
];
