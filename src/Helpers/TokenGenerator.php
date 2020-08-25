<?php

namespace Damian972\AuthBundle\Helpers;

class TokenGenerator
{
    const REGISTER_TYPE = 'reg';
    const RESET_PASSWORD_TYPE = 'res';

    public static function generate(string $type, int $length = 25): string
    {
        $randomHash = substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length);

        return $type.'.'.$randomHash;
    }

    public static function validate(string $token, string $type, int $tokenSize = 25): bool
    {
        $pattern = '/^('.self::REGISTER_TYPE.'|'.self::RESET_PASSWORD_TYPE.").([0-9a-fA-F]{{$tokenSize}})$/";

        return preg_match($pattern, $token);
    }
}
