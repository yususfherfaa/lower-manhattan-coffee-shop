<?php

use App\Libraries\FlaskRecommendationClient;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class FlaskRecommendationClientTest extends CIUnitTestCase
{
    public function testReturnsRecommendationsWhenFlaskResponds(): void
    {
        $client = new FlaskRecommendationClient(static function (string $url): array {
            return ['status' => 200, 'body' => '{"status":"success","data":{"rekomendasi":[]}}'];
        });

        $this->assertSame([], $client->fetch(12)['data']['rekomendasi']);
    }

    public function testReturnsFallbackSignalWhenFlaskIsUnavailable(): void
    {
        $client = new FlaskRecommendationClient(static function (string $url): array {
            return ['status' => 0, 'body' => false];
        });

        $this->assertNull($client->fetch(2));
    }
}
