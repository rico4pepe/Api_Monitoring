<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataSource;
use App\Services\Observability\MonitoringIngestionService;    

class PollSmsSources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'observability:poll-sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll bank SMS sources and ingest observability data';

    /**
     * Execute the console command.
     * 
     */
    public function handle(MonitoringIngestionService $service): int
    {
        //
         $sources = DataSource::where('type', 'database')
            ->where('is_active', true)
            ->where('code', 'like', '%_sms')
            ->get();

        foreach ($sources as $source) {
            $this->info("Polling source: {$source->code}");
            $service->ingest($source);
        }

        $this->info('SMS polling completed.');
        $this->info('Total sources: ' . $sources->count());

        return self::SUCCESS;
    }
}
