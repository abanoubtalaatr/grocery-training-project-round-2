<?php

namespace App\Support;

class PhoneNormalizer
{
    public static function normalize(array $data)
    {
        $phone = trim($data['phone'] ?? '');
        $code = trim($data['country_code'] ?? '');

        if ($code !== '' && str_starts_with($phone, $code)) {
            $data['phone'] = substr($phone, strlen($code));
        }

        return $data;
    }
}
