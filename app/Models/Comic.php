<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int comic_id
 * @property string name
 * @property Carbon created_at
 * @property int created_by
 * @property Carbon updated_at
 * @property int updated_by
 * @property SlackMember creator
 * @property SlackMember updater
 */
class Comic extends Model
{
    protected $table = 'comic';

    public function creator(): BelongsTo
    {
        return $this->belongsTo(SlackMember::class, 'created_by', 'id');
    }
    public function updater(): BelongsTo
    {
        return $this->belongsTo(SlackMember::class, 'updated_by', 'id');
    }
}
