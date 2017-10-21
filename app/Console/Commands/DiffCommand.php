<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Util;

class Diff extends Command
{
    protected $signature = 'diff {src} {dst}';

    protected $description = 'compare two start2 json files';

    public function handle() {
        $src = Util::load('start2/' . $this->argument('src'));
        $dst = Util::load('start2/' . $this->argument('dst'));
        $result = Util::compareJson($src, $dst) ? 'same' : 'different';
        $this->info("These two files are $result.");
        print_r(Util::$trace);
    }
}