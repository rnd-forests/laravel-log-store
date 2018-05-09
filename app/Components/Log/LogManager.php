<?php

namespace App\Components\Log;

use App\Exceptions\LogStoringException;
use App\PracticeLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Jenssegers\Mongodb\Eloquent\Model;

class LogManager
{
    /**
     * Map each log type to its associated model class.
     */
    const DOCUMENT_TYPES = [
        'practice' => PracticeLog::class,
    ];

    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validator;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $user;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $documents;

    /**
     * @var array
     */
    protected $beforeHooks = [];

    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Contracts\Validation\Factory $validator
     */
    public function __construct($app, $validator)
    {
        $this->app = $app;
        $this->validator = $validator;
    }

    /**
     * Store log documents.
     *
     * @param  array|string $data
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \App\Exceptions\LogStoringException
     */
    public function write($data)
    {
        $this->performGuard();

        $this->associateWithUser(
            $this->parseDocuments($data)
        );

        $model = $this->newModelInstance();
        $model->insert($this->documents);

        $instances = $model->hydrate($this->documents);
        LogsHaveBeenStored::dispatch($this->user, $instances, $this->type);

        return $instances;
    }

    /**
     * Set the user associated with log documents.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return self
     */
    public function user($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set the log document type.
     *
     * @param  string $type
     * @return self
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the list of log document types as a string used for validation.
     *
     * @return string
     */
    public function typesAsString()
    {
        return implode(',', array_keys(self::DOCUMENT_TYPES));
    }

    /**
     * Add an documents preprocessing step.
     *
     * @param  callable|string $callback
     * @return self
     */
    public function before($callback)
    {
        // If the given callback is a string, we'll assume that it's in the
        // form of 'class@method'. As a result, we just need to append
        // it to our current list of hooks.
        if (is_string($callback)) {
            $this->beforeHooks[] = $callback;

            return $this;
        }

        // If a closure is given, we're going to wrap
        // it inside another callback function so that we can
        // call it later.
        $this->beforeHooks[] = function () use ($callback) {
            return call_user_func_array($callback, [$this->documents]);
        };

        return $this;
    }

    /**
     * Parse the given data into log documents.
     *
     * @param  string|array $data
     * @return array
     * @throws \App\Exceptions\LogStoringException
     */
    protected function parseDocuments($data)
    {
        if (empty($data)) {
            throw new LogStoringException('Invalid log data provided.');
        }

        // If the data is an associative array, we'll assume that
        // it contains the attributes of a single log instance. We're
        // going to wrap it in another array in order to create the same
        // interface for later processing steps.
        if (Arr::accessible($data) && Arr::isAssoc($data)) {
            // If the data contains a single key named 'data'. We'll assume
            // that the value associated with that key is a JSON string.
            if (array_keys($data) === ['data']) {
                $this->documents = $this->parseJson(Arr::get($data, 'data'));
            } else {
                $this->documents = [$data];
            }
        } else {
            // If the data is a string, we'll assume that a JSON string
            // is presented. We're going to parse it into attributes of
            // a single of multiple log instances.
            $this->documents = $this->parseJson($data);
        }

        $this->validate($this->documents);

        // Here we'll check for a method named 'transformAttributes' on model class.
        // If that method exists, we're going to call it and pass the documents along.
        // This give us an opportunity to transform document attributes before saving
        // to the database, for example, storing uploaded files.
        $model = $this->newModelInstance();
        if (method_exists($model, 'transformAttributes')) {
            $this->documents = $this->app->call(
                get_class($model).'@transformAttributes',
                [$this->documents]
            );
        }

        // Transform documents using the provided 'before' hooks.
        // This is another way to transform documents' attributes
        // before saving to the database.
        $this->processBeforeHooks();

        return $this->documents;
    }

    /**
     * Parse the JSON data into documents.
     *
     * @param  string $data
     * @return array
     * @throws \App\Exceptions\LogStoringException
     */
    protected function parseJson($data)
    {
        if (!is_string($data)) {
            throw new LogStoringException('Log data must be a JSON string.');
        }

        $documents = json_decode($data, true);

        if (is_null($documents) || json_last_error() !== JSON_ERROR_NONE) {
            throw new LogStoringException(json_last_error_msg());
        }

        // If the given documents is an associative array,
        // we're going to assume that a single log instance
        // was provided. As such, we're going to wrap it.
        if (Arr::isAssoc($documents)) {
            $documents = [$documents];
        }

        return $documents;
    }

    /**
     * Create new log document from its type.
     *
     * @return \App\Document
     */
    protected function newModelInstance()
    {
        return resolve(Arr::get(self::DOCUMENT_TYPES, $this->type));
    }

    /**
     * Validate the parsed documents.
     *
     * @param  array $documents
     * @return void
     */
    protected function validate($documents)
    {
        $rules = $this->newModelInstance()->getValidationRules();

        foreach ($documents as $document) {
            $this->validator->validate($document, $rules);
        }
    }

    /**
     * Attach user to log documents.
     *
     * @param  array $documents
     * @return void
     */
    protected function associateWithUser($documents)
    {
        $userId = $this->user->id;
        $model = $this->newModelInstance();

        $this->documents = array_map(function ($document) use ($userId, $model) {
            Arr::set($document, Model::CREATED_AT, $model->freshTimestamp());

            return Arr::prepend($document, $userId, 'user_id');
        }, $documents);
    }

    /**
     * Transform documents using 'before' hooks.
     *
     * @return void
     */
    protected function processBeforeHooks()
    {
        foreach ($this->beforeHooks as $hook) {
            if (is_callable($hook)) {
                $this->documents = call_user_func($hook);
            }

            if (is_string($hook)) {
                $this->documents = $this->app->call($hook, [$this->documents]);
            }
        }
    }

    /**
     * Check validity of properties.
     *
     * @return void
     * @throws LogStoringException
     */
    protected function performGuard()
    {
        $this->ensureValidType();
        $this->ensureValidUser();
    }

    /**
     * @return void
     * @throws LogStoringException
     */
    protected function ensureValidType()
    {
        if (!(is_string($this->type) && Arr::exists(self::DOCUMENT_TYPES, $this->type))) {
            throw new LogStoringException('Invalid log type provided.');
        }
    }

    /**
     * @return void
     * @throws LogStoringException
     */
    protected function ensureValidUser()
    {
        if (!($this->user && $this->user instanceof Authenticatable)) {
            throw new LogStoringException('Invalid user provided.');
        }
    }
}
