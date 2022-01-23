<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->integer('total_gameweek')->default(38);
            $table->integer('current_gameweek')->nullable();
            $table->string('overall_pts')->nullable();
            $table->string('goal_difference_pts')->nullable();
            $table->string('home_goals_pts')->nullable();
            $table->string('away_goals_pts')->nullable();
            $table->string('underdog_bonus_pts')->nullable();
            $table->string('2x_booster_pts')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('options');
    }
}
