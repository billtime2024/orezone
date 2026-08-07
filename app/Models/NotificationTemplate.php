<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'channel',
        'title_template',
        'body_template',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Render the template with given data placeholders.
     */
    public function render(array $data = []): array
    {
        $title = $this->title_template;
        $body = $this->body_template;

        foreach ($data as $key => $value) {
            $title = str_replace("{{$key}}", $value, $title);
            $body = str_replace("{{$key}}", $value, $body);
        }

        return [
            'title' => $title,
            'body' => $body,
        ];
    }
}
