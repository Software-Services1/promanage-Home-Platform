<?php

namespace Database\Seeders;

use App\Models\TaskType;
use App\Support\WorkTypes;
use Illuminate\Database\Seeder;

class TaskTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (WorkTypes::TASKS as $key => $def) {
            TaskType::updateOrCreate(['key' => $key], [
                'label'                 => $def['label'],
                'points'                => $def['points'],
                'bonus'                 => $def['bonus'] ?? 0,
                'category'              => $def['cat'] ?? 'general',
                'counts_when_published' => $key === 'reels_idea',
                'is_active'             => true,
            ]);
        }
    }
}
