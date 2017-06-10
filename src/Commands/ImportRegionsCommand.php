<?php

namespace Bigperson\VkGeo\Commands;

use ATehnix\VkClient\Client;
use ATehnix\VkClient\Requests\Request;
use Bigperson\VkGeo\Models\Country;
use Bigperson\VkGeo\Models\Region;

class ImportRegionsCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vk:import-regions {--countryId=* : Ids countries for import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Regions from vk.com api';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->clearTable('regions');

        $countryIds = $this->option('countryId');

        if (count($countryIds)) {
            $countries = Country::whereIn('id', $countryIds)->get();
        } else {
            $countries = Country::all();
        }

        $countries->each(function ($country) {
            $this->info('Start import regions for'.$country->title);
            $this->makeRequest($country);
        });
    }

    /**
     * @param array $items
     * @param int   $countryId
     */
    private function addRegions(array $items, int $countryId)
    {
        foreach ($items as $item) {

            \DB::transaction(function () use ($item, $countryId) {
                $region = Region::create([
                    'id'         => $item['id'],
                    'title'      => $item['title'],
                    'country_id' => $countryId,
                ]);

                if (!$region) {
                    $this->error('Region '.$region->title.' not imported!');
                } else {
                    $this->line('Region '.$region->title.' successfully imported');
                }
            });
        }
    }

    /**
     * @param Country $country
     * @param int     $offset
     * @param int     $count
     */
    private function makeRequest(Country $country, $offset = 0, $count = 1000)
    {
        $request = new Request(
            'database.getRegions',
            [
                'country_id' => $country->id,
                'offset'     => $offset,
                'count'      => $count,
            ]
        );

        $response = $this->client->send($request);

        usleep(config('vk-geo.delay', 1000));

        if (isset($response['response']['items']) && count($response['response']['items']) > 0) {
            $this->addRegions($response['response']['items'], $country->id);
            $this->makeRequest($country, $offset + $count, $count);
        } else {
            $this->info('Import regions successfully completed for '.$country->title);
        }
    }
}
