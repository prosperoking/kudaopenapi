<?php

namespace Prosperoking\KudaOpenApi\Contracts;

interface IAuthCacheDriver
{
    public function setAuthToken(string $value): bool;
    public function getAuthToken(): ?string;
}