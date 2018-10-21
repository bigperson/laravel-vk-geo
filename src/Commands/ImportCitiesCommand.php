<?php

namespace Bigperson\VkGeo\Commands;

use ATehnix\VkClient\Requests\Request;
use Bigperson\VkGeo\Models\City;
use Bigperson\VkGeo\Models\Country;
use Bigperson\VkGeo\Models\Region;

class ImportCitiesCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vk:import-cities
                            {--countryId=* : Ids countries for import, required in not region Id} 
                            {--regionId=* : Ids region for import, required in not country Id}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Cities from vk.com api';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $countryIds = $this->option('countryId');
        $regionIds = $this->option('regionId');

        if (count($countryIds)) {
            $regions = Region::whereHas('country', function ($query) use ($countryIds) {
                $query->whereIn('id', $countryIds);
            })
                ->with('country')
                ->get();
        } elseif (count($regionIds)) {
            $regions = Region::whereIn('id', $regionIds)
                ->with('country')
                ->get();
        } else {
            $regions = Region::all();
        }

        $regions->each(function ($region) {
            $this->info('Start import cities for country '.$region->country->title." region $region->title");
            $this->makeRequest($region);
        });

        /*
         * Api vk.com do not transfer these two cities by region for Russia
         */
        if (in_array(1, $countryIds)) {
            $this->importMsk();
            $this->importSpb();
        }
    }

    /**
     * @param array $items
     * @param int   $countryId
     * @param int   $regionId
     */
    private function addCities(array $items, int $countryId, int $regionId)
    {
        foreach ($items as $item) {
            $area = null;
            if (isset($item['area'])) {
                $area = $item['area'];
            }

            \DB::transaction(function () use ($item, $countryId, $regionId, $area) {
                $city = City::updateOrCreate([
                    'id'         => $item['id'],
                    'title'      => $item['title'],
                    'country_id' => $countryId,
                    'region_id'  => $regionId,
                    'area'       => $area,
                ]);

                if (!$city) {
                    $this->error('City '.$city->title.' not imported!');
                } else {
                    $this->line($city->title.'imported');
                }
            });
        }
    }

    /**
     * @param Region $region
     * @param int    $offset
     * @param int    $count
     */
    private function makeRequest(Region $region, $offset = 0, $count = 1000)
    {
        $request = new Request(
            'database.getCities',
            [
                'country_id' => $region->country->id,
                'region_id'  => $region->id,
                'offset'     => $offset,
                'count'      => $count,
            ]
        );

        $response = $this->client->send($request);

        usleep(config('vk-geo.delay', 1000));

        if (isset($response['response']['items']) && count($response['response']['items']) > 0) {
            $this->addCities($response['response']['items'], $region->country->id, $region->id);
            $this->makeRequest($region, $offset + $count, $count);
        } else {
            $this->info('Import cities successfully completed for '.$region->title.' '.$region->country->title);
        }
    }

    /**
     * Import Moscow.
     *
     * @return void
     */
    private function importMsk()
    {
        $this->info('Start import Moscow and SPB');

        $request = new Request(
            'database.getCities',
            [
                'country_id' => 1,
                'q'          => 'Москва',
                'count'      => 1,
            ]
        );

        $response = $this->client->send($request);

        usleep(config('vk-geo.delay', 1000));

        $this->addCities($response['response']['items'], 1, 1053480);
    }

    /**
     * Import SPB.
     *
     * @return void
     */
    private function importSpb()
    {
        $this->info('Start import Sankt-Peterburg');

        $request = new Request(
            'database.getCities',
            [
                'country_id' => 1,
                'q'          => 'Санкт-Петербург',
                'count'      => 1,
            ]
        );

        $response = $this->client->send($request);

        usleep(config('vk-geo.delay', 1000));

        $this->addCities($response['response']['items'], 1, 1045244);
    }
}
