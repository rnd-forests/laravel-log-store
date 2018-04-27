<?php

namespace App;

use App\Components\Log\LogManager;
use Illuminate\Support\Arr;

trait Loggable
{
    /**
     * A log belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the custom 'type' attribute.
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        return Arr::get(array_flip(LogManager::DOCUMENT_TYPES), static::class);
    }

    /**
     * Get validation rules used when storing instances.
     *
     * @return array
     */
    public function getValidationRules()
    {
        return [];
    }

    /**
     * Get all of the appendable values that are arrayable.
     * Override the original method to add custom attribute
     * for the model. Defining $appends attribute not working
     * for trait.
     *
     * @return array
     */
    protected function getArrayableAppends()
    {
        $this->appends = array_unique(array_merge($this->appends, ['type']));

        return parent::getArrayableAppends();
    }
}
