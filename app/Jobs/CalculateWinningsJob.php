<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\ResultCalculationService;

class CalculateWinningsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $providerId;
    protected $providerDetails;
    protected $result;

    public function __construct($providerId, $providerDetails, $result)
    {
        $this->providerId = $providerId;
        $this->providerDetails = $providerDetails;
        $this->result = $result;
    }

    public function handle()
    {
        $service = new ResultCalculationService();
        $service->calculateWinnings($this->providerId, $this->providerDetails, $this->result);
    }
}
