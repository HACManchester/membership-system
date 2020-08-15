<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use BB\Entities\Role;

class CreateNewMemberRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('description')->nullable()->after('title');
        });

        Role::create(['name'=>'membership', 'title'=>'Membership', 'description' => 'Helping new members get started at Hackspace Manchester']);
        Role::create(['name'=>'comms', 'title'=>'Comms', 'description' => 'Letting the rest of the world know about Hackspace Manchester ']);
        Role::create(['name'=>'metalworking', 'title'=>'Metalworking', 'The Machinists group']);
        Role::create(['name'=>'woodworking', 'title'=>'Woodworking']);
        Role::create(['name'=>'safety', 'title'=>'Health and Safety']);
        Role::create(['name'=>'board', 'title'=>'board', 'description' => 'The members (directors) with legal responsibility for Hackspace Manchester']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Role::where('name', 'membership')->delete();
        Role::where('name', 'comms')->delete();
        Role::where('name', 'metalworking')->delete();
        Role::where('name', 'woodworking')->delete();
        Role::where('name', 'safety')->delete();
        Role::where('name', 'board')->delete();

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
