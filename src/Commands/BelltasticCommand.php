<?php

namespace Belltastic\Commands;

use Illuminate\Console\Command;

class BelltasticCommand extends Command
{
    public $signature = 'laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
