<?php

namespace App;

class PracticeLog extends Document
{
    use Loggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'lesson_id',
        'start',
        'end',
        'score',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'start',
        'end',
        'created_at',
    ];

    /**
     * {@inheritdoc}
     */
    public function getValidationRules()
    {
        return [
            'lesson_id' => 'required|integer|exists:lessons,id',
            'start' => 'required|date_format:Y-m-d H:i:s',
            'end' => 'required|different:start|after:start|date_format:Y-m-d H:i:s',
            'score' => 'required|integer|between:10,100',
        ];
    }
}
