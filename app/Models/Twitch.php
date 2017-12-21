<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int channel_id
 * @property string channel_name
 * @property string name
 * @property Carbon created_at
 * @property int created_by
 * @property Carbon updated_at
 * @property int updated_by
 */
class Twitch extends Model
{
    protected $table = 'twitch';
}
