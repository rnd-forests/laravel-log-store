<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\PracticeLog;

class PracticeLogsController extends Controller
{
    /**
     * Get all practice log instances (for demo only).
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return PracticeLog::latest()->get();
    }
}
