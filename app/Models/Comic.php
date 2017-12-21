<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int comic_id
 * @property string name
 * @property Carbon created_at
 * @property int created_by
 * @property Carbon updated_at
 * @property int updated_by
 */
class Comic extends Model
{
    protected $table = 'comic';
}
