<?php

namespace Belltastic\Util;

abstract class Util
{
    public static $isMbstringAvailable;

    /**
     * @param mixed|string $value a string to UTF8-encode
     *
     * @return mixed|string the UTF8-encoded string, or the object passed in if
     *    it wasn't a string
     */
    public static function utf8($value)
    {
        if (null === self::$isMbstringAvailable) {
            self::$isMbstringAvailable = \function_exists('mb_detect_encoding');

            if (! self::$isMbstringAvailable) {
                \trigger_error('It looks like the "mbstring" PHP extension is not enabled. ' .
                    'UTF-8 strings will not be properly encoded. Ask your system ' .
                    'administrator to enable the "mbstring" extension, or write to ' .
                    'support@belltastic.com if you have any questions.', \E_USER_WARNING);
            }
        }

        if (\is_string($value) && self::$isMbstringAvailable && 'UTF-8' !== \mb_detect_encoding($value, 'UTF-8', true)) {
            return \utf8_encode($value);
        }

        return $value;
    }

    public static function hmac($project_id, $user_id, $secret = null): string
    {
        return \base64_encode(\hash_hmac(
            'sha256',
            $project_id . ':' . $user_id,
            $secret ?? config('belltastic.projects.'.$project_id.'.secret'),
            true
        ));
    }
}
