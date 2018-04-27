<?php

namespace App;

use Illuminate\Support\Str;
use Jenssegers\Mongodb\Eloquent\Model;

class Document extends Model
{
    /**
     * The name of the "updated at" column.
     * Here we disable this column because it's unnecessary for log instances.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Build the collection name dynamically.
     * For example, if document name is "PracticeLog" then the collection name will be "practice_logs"
     *
     * @return string
     */
    public function getTable()
    {
        if (isset($this->collection)) {
            return $this->collection;
        }

        return str_replace('\\', '', Str::snake(Str::plural(class_basename($this))));
    }
}
