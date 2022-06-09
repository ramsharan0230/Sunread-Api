<?php

namespace Modules\Core\Database\factories;

use Illuminate\Support\Arr;
use Modules\Core\Entities\ActivityLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        $fake_subject = Arr::random(["\Modules\Core\Entities\Channel", "\Modules\Core\Entities\Currency"]);
        $fake_causer = Arr::random(["\Modules\User\Entities\Admin", "\Modules\Customer\Entities\Customer", NULL]);

        return [
            'log_name' => 'default',
            'subject_id' => $fake_subject::inRandomOrder()->first()->id,
            'subject_type' => $fake_subject,
            'causer_id' => $fake_causer ? $fake_causer::inRandomOrder()->first()->id : null,
            'causer_type' => $fake_causer,
            'properties' => [],
            'description' => "created",
            'action' => $this->faker->name(),
            'activity' => $this->faker->name()
        ];
    }
}
