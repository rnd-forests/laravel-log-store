<?php

namespace App\Http\Controllers\Api;

use App\Components\Log\LogManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LogsController extends Controller
{
    /**
     * @var \App\Components\Log\LogManager
     */
    protected $manager;

    /**
     * @param \App\Components\Log\LogManager $manager
     */
    public function __construct(LogManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Store new log documents.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:'.$this->manager->typesAsString(),
        ]);

        $this->manager
            ->user($request->user())
            ->type($request->input('type'))
            ->write($request->except(['type']));

        return response()->json([
            'message' => Str::studly($request->input('type')). ' logs has been saved.',
        ], 201);
    }
}
