<?php

namespace Bigperson\VkGeo\Commands;

use ATehnix\VkClient\Client;
use Illuminate\Console\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * ImportCountryCommand constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    /**
     * @param $table
     */
    protected function clearTable($table)
    {
        \DB::table(config('vk-geo.prefix', '').$table)->delete();
    }
}
