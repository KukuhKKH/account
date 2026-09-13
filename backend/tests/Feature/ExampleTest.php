<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends TestCase
{
    public function testTheApplicationRedirectsToFrontend(): void
    {
        $this->get('/')
            ->assertStatus(302);
    }
}
