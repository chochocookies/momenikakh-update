<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set the domain for the request.
     */
    protected function withDomain(string $domain): self
    {
        return $this->withHeader('Host', $domain);

    }

}
