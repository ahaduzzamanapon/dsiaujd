<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StreamServer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'stream_id' => 'integer',
        'order' => 'integer',
    ];

    public function stream()
    {
        return $this->belongsTo(Stream::class);
    }

    /**
     * Resolve Referer and Origin headers for a given stream URL.
     */
    public static function resolveHeadersForUrl(string $url, ?string $referer = null, ?string $origin = null): array
    {
        $ref = !empty($referer) ? $referer : null;
        $orig = !empty($origin) ? $origin : null;

        if (str_contains($url, '198.195.')) {
            $ref = $ref ?: 'http://198.195.239.50/';
            $orig = $orig ?: 'http://198.195.239.50';
        } elseif (str_contains($url, 'bdixtv24') || str_contains($url, 'bdix')) {
            $ref = $ref ?: 'https://bdixtv24.com/';
            $orig = $orig ?: 'https://bdixtv24.com';
        } elseif (str_contains($url, 'zohanayaan.com') || str_contains($url, 'executeandship.com') || str_contains($url, 'crichd')) {
            $ref = $ref ?: 'https://executeandship.com/';
            $orig = $orig ?: 'https://executeandship.com';
        } elseif (str_contains($url, 'fancode.com')) {
            $ref = $ref ?: 'https://fancode.com/';
            $orig = $orig ?: 'https://fancode.com';
        } elseif (str_contains($url, 'redforce.live')) {
            $ref = $ref ?: 'http://redforce.live/';
            $orig = $orig ?: 'http://redforce.live';
        }

        // Hostname fallback matching from the database
        if (empty($ref) || empty($orig)) {
            $host = parse_url($url, PHP_URL_HOST);
            if ($host) {
                $existing = self::where('url', 'like', "%//{$host}%")
                    ->where(function($query) {
                        $query->whereNotNull('http_referer')
                              ->where('http_referer', '!=', '');
                    })
                    ->first();
                if ($existing) {
                    $ref = $ref ?: $existing->http_referer;
                    $orig = $orig ?: $existing->http_origin;
                }
            }
        }

        return [
            'referer' => $ref,
            'origin' => $orig,
        ];
    }

    /**
     * Bootstrap the model and its event hooks.
     */
    protected static function booted()
    {
        static::saving(function ($server) {
            $resolved = self::resolveHeadersForUrl($server->url, $server->http_referer, $server->http_origin);
            $server->http_referer = $resolved['referer'];
            $server->http_origin = $resolved['origin'];
        });
    }
}
