<?php

namespace Prosperoking\KudaOpenApi;

use Prosperoking\KudaOpenApi\Contracts\IAuthCacheDriver;

class DefaultCacheDriver implements IAuthCacheDriver
{

    public function setAuthToken(string $value): bool
    {
        $tempFile = sys_get_temp_dir().'/kauth';

        return file_put_contents($tempFile,$value);
    }

    public function getAuthToken(): ?string
    {
        $tempFile = sys_get_temp_dir().'/kauth';
        if(!is_file($tempFile)) return null;
        $lmodt = filemtime($tempFile);

        if(!$lmodt || (time() - $lmodt) > 60 * 12) return null;

        return file_get_contents($tempFile);
        // TODO: Implement getAuthToken() method.
    }
}