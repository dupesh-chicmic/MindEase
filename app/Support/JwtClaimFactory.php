<?php

namespace App\Support;

use Tymon\JWTAuth\Claims\Factory as BaseClaimFactory;

class JwtClaimFactory extends BaseClaimFactory
{
    protected $ttl = 60;

    public function setTTL($ttl)
    {
        $this->ttl = $ttl === null ? null : (int) $ttl;

        return $this;
    }

    public function getTTL()
    {
        return $this->ttl;
    }
}
