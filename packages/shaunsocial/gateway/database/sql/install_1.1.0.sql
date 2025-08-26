INSERT INTO `{prefix}gateways` (`name`, `key`, `class`, `show`, `order`, `created_at`, `updated_at`) VALUES
('Paystack', 'paystack', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Paystack', 1 ,6, NOW(), NOW()),
('Flutterwave', 'flutterwave', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Flutterwave', 1, 7, NOW(), NOW()),
('CCBill', 'ccbill', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\CCBill', 1 ,8, NOW(), NOW());