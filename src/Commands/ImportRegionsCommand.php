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

        if(count($countryIds)) {
            $countries = Country::whereIn('id', $countryIds)->get();
        } else {
            $countries = Country::all();
        }

        $countries->each(function ($country) {
            echo "Start import regions for $country->title\n";

            $this->makeRequest($country->id);
        });
    }

    /**
     * @param array $items
     * @param int   $countryId
     */
    private function addRegions(array $items, int $countryId)
    {
        foreach ($items as $item) {
            $region = Region::create([
                'id'         => $item['id'],
                'title'      => $item['title'],
                'country_id' => $countryId,
            ]);

            echo $region->title." imported \n";
        }
    }

    /**
     * @param     $countryId
     * @param int $offset
     * @param int $count
     */
    private function makeRequest($countryId, $offset = 0, $count = 1000)
    {
        $request = new Request(
            'database.getRegions',
            [
                'country_id' => $countryId,
                'offset'     => $offset,
                'count'      => $count,
            ]
        );

        $response = $this->client->send($request);

        usleep(config('vk-geo.delay', 1000));

        if (isset($response['response']['items']) && count($response['response']['items']) > 0) {
            $this->addRegions($response['response']['items'], $countryId);
            $this->makeRequest($countryId, $offset + $count, $count);
        } else {
            echo "end\n";
        }
    }
}
