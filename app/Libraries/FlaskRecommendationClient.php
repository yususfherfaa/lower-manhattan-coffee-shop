<?php

namespace App\Libraries;

final class FlaskRecommendationClient
{
    /** @var callable|null */
    private $transport;

    public function __construct(?callable $transport = null)
    {
        $this->transport = $transport;
    }

    /** Return a valid Flask response, or null when Flask is unavailable/bad. */
    public function fetch(?int $hour = null, ?int $minute = null): ?array
    {
        $url = 'http://127.0.0.1:5001/api/rekomendasi';
        $query = [];
        if ($hour !== null) {
            $query['jam'] = $hour;
        }
        if ($minute !== null) {
            $query['menit'] = $minute;
        }
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        if ($this->transport !== null) {
            $response = ($this->transport)($url);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $body = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $response = ['status' => $status, 'body' => $body];
        }

        if (!is_array($response) || ($response['status'] ?? 0) !== 200 || !is_string($response['body'] ?? null)) {
            return null;
        }
        $decoded = json_decode($response['body'], true);
        return is_array($decoded) && isset($decoded['data']) ? $decoded : null;
    }
}
