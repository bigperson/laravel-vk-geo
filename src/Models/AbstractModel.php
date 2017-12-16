<?php

namespace Bigperson\VkGeo\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractModel.
 */
class AbstractModel extends Model
{
    /**
     * AbstractModel constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('vk-geo.prefix', '').$this->getTable();

        parent::__construct($attributes);
    }
}
