<?php

namespace App\Services\Logging;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SlackLogError
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     *  Send error to slack error channel
     */
    public function logError(Throwable $e)
    {
        Log::channel('slack')->error($e->getMessage(), [
            'url' => $this->request->fullUrl(),
            'method' => $this->request->method(),
            'body' => $this->request->all(),
            'error' => [
                'status code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ],
        ]);
    }
}
