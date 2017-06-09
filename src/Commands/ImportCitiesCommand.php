<?php

namespace Bigperson\VkGeo\Commands;

use ATehnix\VkClient\Client;
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

        $this->clearTable('cities');

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
            echo "Start import cities for country ".$region->country->title." region $region->title\n";
            $this->makeRequest($region->country->id, $region->id);
        });
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
            $city = City::create([
                'id'         => $item['id'],
                'title'      => $item['title'],
                'country_id' => $countryId,
                'region_id'  => $regionId,
                'area'       => $area,
            ]);

            echo $city->title." imported \n";
        }
    }

    /**
     * @param      $countryId
     * @param null $regionId
     * @param int  $offset
     * @param int  $count
     */
    private function makeRequest($countryId, $regionId, $offset = 0, $count = 1000)
    {
        $request = new Request(
            'database.getCities',
            [
                'country_id' => $countryId,
                'region_id'  => $regionId,
                'offset'     => $offset,
                'count'      => $count,
            ]
        );

        $response = $this->client->send($request);

        usleep(config('vk-geo.delay', 1000));

        if (isset($response['response']['items']) && count($response['response']['items']) > 0) {
            $this->addCities($response['response']['items'], $countryId, $regionId);
            $this->makeRequest($countryId, $offset + $count, $count);
        } else {
            echo "end\n";
        }
    }
}
