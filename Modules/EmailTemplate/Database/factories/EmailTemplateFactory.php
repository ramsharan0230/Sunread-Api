<?php
namespace Modules\EmailTemplate\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmailTemplateFactory extends Factory
{
    protected $model = \Modules\EmailTemplate\Entities\EmailTemplate::class;

    public function definition(): array
    {
        return [
            "name" => $this->faker->name(),
            "subject" => $this->faker->name(),
            "content" => $this->faker->paragraph(),
            "style" => $this->faker->name(),
            "email_template_code" => "welcome_email"
        ];
    }
}

