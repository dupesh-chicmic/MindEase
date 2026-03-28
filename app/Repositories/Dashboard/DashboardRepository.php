<?php

namespace App\Repositories\Dashboard;

use App\Models\Quote;

class DashboardRepository
{
    public function randomQuote(): ?Quote
    {
        return Quote::query()->inRandomOrder()->first();
    }
}
