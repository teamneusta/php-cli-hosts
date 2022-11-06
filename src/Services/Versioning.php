<?php

namespace TeamNeusta\Hosts\Services;

class Versioning
{
    public static function updateVersion(string $version): string
    {
        for ($new_version = explode(".", $version), $i = count($new_version) - 1; $i > -1; --$i) {
            if (++$new_version[$i] < 10 || !$i) {
                break;
            }
            $new_version[$i] = 0;
        }

        return implode(".", $new_version);
    }
}