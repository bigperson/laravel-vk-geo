<?php

namespace Bigperson\VkGeo\Models;

/**
 * Class Country.
 */
class Country extends AbstractModel
{
    /**
     * @var array
     */
    protected $fillable = ['id', 'title'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
