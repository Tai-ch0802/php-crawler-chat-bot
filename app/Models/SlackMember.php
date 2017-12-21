<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string user_name
 * @property string user_id
 */
class SlackMember extends Model
{
    protected $table = 'slack_members';

    public $timestamps = false;
}
