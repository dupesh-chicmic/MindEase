<?php

namespace Database\Seeders;

use App\Models\Mood;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DashboardSampleSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::now(config('app.timezone'))->startOfDay();

        $samples = [
            0 => ['label' => 'good', 'score' => 4, 'emoji' => '🙂', 'sleep' => 4, 'stress' => 2, 'productivity' => 4, 'ate' => true],
            1 => ['label' => 'excellent', 'score' => 5, 'emoji' => '😄', 'sleep' => 5, 'stress' => 1, 'productivity' => 5, 'ate' => true],
            2 => ['label' => 'okay', 'score' => 3, 'emoji' => '😐', 'sleep' => 3, 'stress' => 3, 'productivity' => 3, 'ate' => false],
            3 => ['label' => 'bad', 'score' => 2, 'emoji' => '😔', 'sleep' => 2, 'stress' => 4, 'productivity' => 2, 'ate' => true],
            4 => ['label' => 'good', 'score' => 4, 'emoji' => '😊', 'sleep' => 4, 'stress' => 2, 'productivity' => 4, 'ate' => true],
            5 => ['label' => 'terrible', 'score' => 1, 'emoji' => '😞', 'sleep' => 1, 'stress' => 5, 'productivity' => 1, 'ate' => false],
            6 => ['label' => 'good', 'score' => 4, 'emoji' => '🙂', 'sleep' => 3, 'stress' => 2, 'productivity' => 4, 'ate' => true],
        ];

        $users = User::query()->get();
        if ($users->isEmpty()) {
            $users = collect([
                User::query()->create([
                    'name' => 'Nishant Singh',
                    'email' => 'nishant@example.com',
                    'password' => Hash::make('password'),
                    'gender' => 'male',
                    'age' => 24,
                ]),
            ]);
        }

        foreach ($users as $user) {
            foreach ($samples as $offset => $data) {
                $date = $today->copy()->subDays($offset)->toDateString();

                Mood::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $date,
                    ],
                    [
                        'mood_score' => $data['score'],
                        'mood_label' => $data['label'],
                        'emoji' => $data['emoji'],
                        'sleep_score' => $data['sleep'],
                        'stress_score' => $data['stress'],
                        'productivity_score' => $data['productivity'],
                        'ate_well' => $data['ate'],
                    ]
                );
            }
        }
    }
}
