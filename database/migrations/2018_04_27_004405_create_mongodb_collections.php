<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Schema\Blueprint;

class CreateMongodbCollections extends Migration
{
    /**
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)
            ->table($this->getCollectionName(App\PracticeLog::class), function (Blueprint $collection) {
                $collection->index('user_id');
                $collection->index('lesson_id');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)
            ->table($this->getCollectionName(App\PracticeLog::class), function (Blueprint $collection) {
                $collection->dropIndex('user_id');
                $collection->dropIndex('lesson_id');
            });
    }

    /**
     * @param  \Jenssegers\Mongodb\Eloquent\Model $model
     * @return string
     */
    protected function getCollectionName($model)
    {
        return (new $model)->getTable();
    }
}
