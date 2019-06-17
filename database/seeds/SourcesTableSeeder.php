<?php

use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SourcesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear the table and allow bulk inserts.
        DB::table('sources')->truncate();
        Source::unguard();

        // Create SpaceX.
        Source::create([
            'name'            => 'SpaceX',
            'slug'            => 'space',
            'latest_index'    => 0,
            'check_at'        => Carbon::now(),
            'last_updated_at' => Carbon::now()
        ]);

        // Create XKCD.
        Source::create([
            'name'            => 'XKCD',
            'slug'            => 'comics',
            'latest_index'    => 0,
            'check_at'        => Carbon::now(),
            'last_updated_at' => Carbon::now()
        ]);

    }
}
