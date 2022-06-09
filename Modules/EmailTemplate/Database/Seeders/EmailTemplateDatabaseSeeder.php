<?php

namespace Modules\EmailTemplate\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplateDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $this->call(EmailTemplateSeeder::class);
    }
}
