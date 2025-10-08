<?php
// config/app.php

// Nếu có biến môi trường (Railway), thì dùng biến đó.
// Nếu không có (chạy local Laragon), thì dùng giá trị mặc định.
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'badminton_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

// URL gốc — sửa khi deploy (Railway sẽ có domain thật)
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/badminton-booking/public');

return [
  // Cấu hình khác
  'bank' => [
    'bank_code'   => 'MB BANK',
    'account_no'  => '0962750432',
    'account_name' => 'HO VAN HUNG',
    'qr_template' => 'compact',
  ],

  'deposit_rate' => 0.15,
];
