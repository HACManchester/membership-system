<?php

use Illuminate\Database\Seeder;
use BB\Entities\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courses = [
            'induction_category' => 'Induction Category',
            'LASER' => 'Laser Cutting',
            'Woodwork' => 'Woodwork',
            'Wood-Lathe' => 'Wood Lathe',
            'metalabrasive' => 'Metal Abrasive Tools',
            'metal-mitresaw' => 'Metal Mitre Saw',
            'morticer' => 'Morticer',
            'Woodwork-plane' => 'Woodwork Plane',
            'CNC' => 'CNC Machine',
            '3DPRINTER' => '3D Printer',
            'Crafts' => 'Crafts',
            'mlathe' => 'Metal Lathe',
            'metal-bandsaw' => 'Metal Bandsaw',
            'Grinding' => 'Grinding Equipment',
            'HandheldCircularSaws' => 'Handheld Circular Saws',
            'MetalMill' => 'Metal Mill',
            'BloodyDangerousWood2' => 'Advanced Woodwork Safety',
            'LASER-BLUEY' => 'Laser Cutting (Blue)',
            'testing-inductions-2' => 'Testing Inductions 2',
            'guillotine' => 'Guillotine',
            'Welding' => 'Welding',
            'hydraulic-press' => 'Hydraulic Press',
            'embroidery' => 'Embroidery Machine',
            'test-inductions-2' => 'Test Inductions 2',
            'surface-grinder' => 'Surface Grinder',
            'router-table' => 'Router Table',
        ];

        foreach ($courses as $slug => $name) {
            Course::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => 'This has been automatically set up from historic equipment data and is yet to be updated.',
                    'format' => 'unknown',
                    'frequency' => 'unknown',
                    'wait_time' => 'Unknown',
                ]
            );
        }
    }
}
