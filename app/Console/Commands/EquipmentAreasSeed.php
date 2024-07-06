<?php

namespace BB\Console\Commands;

use BB\Entities\EquipmentArea;
use Illuminate\Console\Command;

class EquipmentAreasSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'equipment_areas:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the default set of equipment areas';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        EquipmentArea::firstOrCreate(['slug' => '3d-printing'], ['name' => '3D Printing', 'description' => '']);
        EquipmentArea::firstOrCreate(['slug' => 'woodwork'], ['name' => 'Woodwork', 'description' => '']);
        EquipmentArea::firstOrCreate(['slug' => 'visual-arts'], ['name' => 'Visual Arts', 'description' => '']);
        EquipmentArea::firstOrCreate(['slug' => 'metalwork'], ['name' => 'Metalwork', 'description' => '']);
        EquipmentArea::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics', 'description' => '']);
        EquipmentArea::firstOrCreate(['slug' => 'cnc'], ['name' => 'CNC', 'description' => '']);
        EquipmentArea::firstOrCreate(['slug' => 'welding'], ['name' => 'Welding', 'description' => '']);
        EquipmentArea::firstOrCreate(['slug' => 'bikespace'], ['name' => 'Bikespace', 'description' => '']);
    }
}
