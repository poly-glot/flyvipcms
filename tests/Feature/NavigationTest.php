<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\DatabaseTestCase;

final class NavigationTest extends DatabaseTestCase
{
    use AuthenticationTesting;
    use FeatureTestTrait;

    public function testOnlyTheCurrentSectionIsMarkedAsCurrent(): void
    {
        $result = $this->actingAs($this->login('admin'))->get('admin/aircrafts');
        $body = (string) $result->getBody();

        $this->assertSame(1, preg_match_all('/<a[^>]*aria-current="page"[^>]*>/', $body, $matches));
        $this->assertStringContainsString('title="Aircraft"', $matches[0][0]);
    }

    public function testDashboardIsCurrentOnlyOnTheDashboard(): void
    {
        $result = $this->actingAs($this->login('admin'))->get('admin');

        $this->assertSame(1, preg_match_all('/<a[^>]*aria-current="page"[^>]*>/', (string) $result->getBody(), $matches));
        $this->assertStringContainsString('title="Dashboard"', $matches[0][0]);
    }
}
