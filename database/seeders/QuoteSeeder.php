<?php

namespace Database\Seeders;

use App\Models\Quote;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        if (Quote::query()->exists()) {
            return;
        }

        $rows = [
            ['text' => 'Every day is a fresh start. Take a deep breath and begin again.', 'author' => 'Unknown'],
            ['text' => 'You are stronger than you know, braver than you believe, and more loved than you imagine.', 'author' => 'A.A. Milne'],
            ['text' => 'Progress, not perfection, is what we should be asking of ourselves.', 'author' => 'Julia Cameron'],
            ['text' => 'Healing takes time, and asking for help is a courageous step.', 'author' => 'Unknown'],
            ['text' => 'Your feelings are valid. You have the right to feel whatever you feel.', 'author' => 'Unknown'],
            ['text' => 'Small steps every day lead to big changes over time.', 'author' => 'Unknown'],
            ['text' => 'Be gentle with yourself. You are doing the best you can.', 'author' => 'Unknown'],
            ['text' => 'Mental health is not a destination, but a process. It is about how you drive, not where you are going.', 'author' => 'Noam Shpancer'],
            ['text' => 'What lies behind us and what lies before us are tiny matters compared to what lies within us.', 'author' => 'Ralph Waldo Emerson'],
            ['text' => 'You do not have to control your thoughts. You just have to stop letting them control you.', 'author' => 'Dan Millman'],
            ['text' => 'Self-care is how you take your power back.', 'author' => 'Lalah Delia'],
            ['text' => 'There is hope, even when your brain tells you there is not.', 'author' => 'John Green'],
            ['text' => 'Rest is not idleness. It is a vital part of growth.', 'author' => 'Unknown'],
            ['text' => 'You are allowed to be both a masterpiece and a work in progress simultaneously.', 'author' => 'Sophia Bush'],
            ['text' => 'The sun is a daily reminder that we too can rise again from the darkness.', 'author' => 'Unknown'],
            ['text' => 'Talk to yourself like someone you love.', 'author' => 'Brené Brown'],
            ['text' => 'Courage does not always roar. Sometimes courage is the quiet voice at the end of the day saying, I will try again tomorrow.', 'author' => 'Mary Anne Radmacher'],
            ['text' => 'Your present circumstances do not determine where you can go; they merely determine where you start.', 'author' => 'Nido Qubein'],
            ['text' => 'Almost everything will work again if you unplug it for a few minutes, including you.', 'author' => 'Anne Lamott'],
            ['text' => 'You matter. Your story matters. Your healing matters.', 'author' => 'Unknown'],
        ];

        foreach ($rows as $row) {
            Quote::query()->create($row);
        }
    }
}
