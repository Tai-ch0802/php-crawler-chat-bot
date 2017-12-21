<?php

use Illuminate\Database\Seeder;
use App\Models\SlackMember;
use App\Models\Twitch;

class TwitchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $list = [
            [
                'channel_name' => 'yuniko0720',
                'name' => '小熊',
            ],
            [
                'channel_name' => 'nightblue3',
                'name' => 'nightblue3',
            ],
            [
                'channel_name' => 'cawai0147',
                'name' => '蛋捲',
            ],
            [
                'channel_name' => 'asiagodtonegg3be0',
                'name' => '統神',
            ],
        ];
        $this->insertData($list);
    }

    private function insertData($list)
    {
        /** @var SlackMember $slackMember */
        $slackMember = SlackMember::find(1);

        foreach ($list as $data) {
            $data['created_by'] = $slackMember->id;
            $data['updated_by'] = $slackMember->id;
            Twitch::create($data);
        }
    }
}
