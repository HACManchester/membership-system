<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('expenses');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::create('expenses', function(Blueprint $table)
		{
            $table->increments('id');
            $table->integer('user_id');
            $table->string('category', 50);
            $table->string('description', 255);
            $table->integer('amount');

            $table->boolean('approved')->default(false);
            $table->boolean('declined')->default(false);
            $table->integer('approved_by_user');

            $table->string('file', 255);

            $table->date('expense_date');

            $table->timestamps();
		});
    }
}
