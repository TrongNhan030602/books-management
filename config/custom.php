<?php

return [
    'dateFormat' => 'Y-m-d',
    'dateTimeFormat' => 'Y-m-d H:i:s',
    'phoneFormat' => '/^0[0-9]{9}$/',
    'passwordFormat' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]{6,15}$/',
    // Mức phạt
    'late_fee' => env(key: 'LATE_FEE'),
    // Số ngày tối đa được gia hạn
    'max_extension_days' => env(key: 'MAX_EXT_DAYS'),
    'max_extensions' => env(key: 'MAX_EXT'),
];