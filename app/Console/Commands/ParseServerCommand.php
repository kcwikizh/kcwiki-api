<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ParseServer extends Command
{

    protected $signature = 'parse:server';

    protected $description = 'Generate server data';

    public function handle()
    {
        $this->info("Fetching server data from http://203.104.209.7/gadget/js/kcs_const.js ...");
        preg_match_all("/ConstServerInfo\.World_.*\"http:\/\/(.*)\/\";/",
            file_get_contents("http://203.104.209.7/gadget/js/kcs_const.js"),
            $raw);
        foreach ( $raw[1] as $key=>$value ){
            $ipList[$key]["id"] = $key + 1;
            $ipList[$key]["ip"] = $value;
        }
        Storage::put("api_servers.json",json_encode($ipList));
        $this->info(count($ipList)." server info successfully fetched.");
    }
}