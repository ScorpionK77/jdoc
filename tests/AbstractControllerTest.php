<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Helmich\JsonAssert\JsonAssertions;

abstract class AbstractControllerTest extends WebTestCase
{
    use JsonAssertions;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

    }
}