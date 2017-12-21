<?php

use Illuminate\Database\Seeder;
use App\Models\SlackMember;
use App\Models\Comic;

class ComicTableSeeder extends Seeder
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
                'comic_id' => 170,
                'name' => '海賊王',
            ],
            [
                'comic_id' => 25010,
                'name' => '東京飡種',
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
            Comic::create($data);
        }
    }
}
