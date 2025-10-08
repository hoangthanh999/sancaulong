<?php
// config/app.php

// Nếu có biến môi trường (Railway), thì dùng biến đó.
// Nếu không có (chạy local Laragon), thì dùng giá trị mặc định.
define('DB_HOST', 'hopper.proxy.rlwy.net');
define('DB_PORT', 33136);
define('DB_NAME', 'railway');
define('DB_USER', 'root');
define('DB_PASS', 'iImNxEKVmFbvBHgSdUUnyXwiTirsDxlX');

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
