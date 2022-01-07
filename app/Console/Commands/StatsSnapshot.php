<?php namespace BB\Console\Commands;

use BB\Services\EquipmentCharge;
use Illuminate\Console\Command;


class StatsSnapshot extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'bb:stats-snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a snapshot for statistical purposes';


    /**
     * @var \BB\Repo\EquipmentLogRepository
     */
    protected $equipmentLogRepository;

    /**
     * @var \BB\Repo\PaymentRepository
     */
    protected $paymentRepository;


    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->equipmentLogRepository = \App::make('\BB\Repo\EquipmentLogRepository');
        $this->paymentRepository = \App::make('\BB\Repo\PaymentRepository');
        $this->equipmentRepository = \App::make('\BB\Repo\EquipmentRepository');
    }

        
    

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $deviceCharging = new EquipmentCharge();
        $deviceCharging->calculatePendingFees();
    }


}