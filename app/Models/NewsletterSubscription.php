<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'is_active',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSubscribed($query)
    {
        return $query->where('is_active', true)
            ->whereNull('unsubscribed_at');
    }

    // Business Methods
    public function subscribe(): void
    {
        $this->is_active = true;
        $this->subscribed_at = now();
        $this->unsubscribed_at = null;
        $this->save();
    }

    public function unsubscribe(): void
    {
        $this->is_active = false;
        $this->unsubscribed_at = now();
        $this->save();
    }

    public function isSubscribed(): bool
    {
        return $this->is_active && $this->unsubscribed_at === null;
    }

    public function resubscribe(): void
    {
        $this->subscribe();
    }
}
