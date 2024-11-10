<?php

namespace Database\Seeders;

use App\AramiscContentType;
use Illuminate\Database\Seeder;

class aramisc_content_typesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        AramiscContentType::query()->truncate();
        $s = new AramiscContentType();
        $s->type_name = 'Home';
        $s->save();
        $s = new AramiscContentType();
        $s->type_name = 'About';
        $s->save();
        $s = new AramiscContentType();
        $s->type_name = 'Contact';
        $s->save();
        $s = new AramiscContentType();
        $s->type_name = 'Services';
        $s->save();
    }
}
