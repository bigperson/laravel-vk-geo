<?php

namespace Bigperson\VkGeo\Commands;

use ATehnix\VkClient\Client;
use ATehnix\VkClient\Requests\Request;
use Bigperson\VkGeo\Models\Country;

class ImportCountryCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vk:import-countries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Countries from vk.com api';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->clearTable('countries');

        $this->makeRequest();
    }

    /**
     * @param array $items
     */
    private function addCountries(array $items)
    {
        foreach ($items as $item){
            \DB::transaction(function () use ($item) {
                $country = Country::create([
                    'id' => $item['id'],
                    'title' => $item['title']
                ]);

                if (!$country) {
                    $this->error('Country '.$country->title.' not imported!');
                } else {
                    $this->line('Country '.$country->title.' successfully imported');
                }
            });
        }
    }

    /**
     * @param int $offset
     * @param int $count
     */
    private function makeRequest($offset = 0, $count = 1000)
    {
        $request = new Request(
            'database.getCountries',
            [
                'need_all' => 1,
                'offset' => $offset,
                'count' => $count
            ]
        );

        $response = $this->client->send($request);

        usleep(config('vk-geo.delay', 1000));

        if(isset($response['response']['items']) && count($response['response']['items']) > 0) {
            $this->addCountries($response['response']['items']);
            $this->makeRequest($offset + $count, $count);
        } else {
            $this->info('Country import completed successfully');
        }
    }
}
