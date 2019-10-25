<?php

namespace App\Listeners;

use DDB\Stats\StatisticsCollector;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;

abstract class BaseListener
{
    /** @var \DDB\Stats\StatisticsCollector */
    protected $statsCollector;

    /** @var \Illuminate\Database\DatabaseManager */
    protected $database;

    /** @var \Illuminate\Contracts\Events\Dispatcher */
    protected $dispatcher;

    public function __construct(
        StatisticsCollector $statsCollector,
        DatabaseManager $database,
        Dispatcher $dispatcher
    ) {
        $this->statsCollector = $statsCollector;
        $this->database = $database;
        $this->dispatcher = $dispatcher;
    }
}
