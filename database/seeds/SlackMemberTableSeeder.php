<?php

use App\Models\SlackMember;
use Illuminate\Database\Seeder;

class SlackMemberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $slackMember = new SlackMember();
        $slackMember->user_id = 'admin';
        $slackMember->user_name = 'admin';
        $slackMember->save();
    }
}
