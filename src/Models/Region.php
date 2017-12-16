<?php

namespace Bigperson\VkGeo\Models;

/**
 * Class Region.
 */
class Region extends AbstractModel
{
    /**
     * @var array
     */
    protected $fillable = ['id', 'title', 'country_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
