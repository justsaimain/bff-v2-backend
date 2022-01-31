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
            $table->string('win_lose_draw_pts')->default(3);
            $table->string('goal_difference_pts')->default(1);
            $table->string('home_goals_pts')->default(1);
            $table->string('away_goals_pts')->default(1);
            $table->string('underdog_bonus_pts')->default(2);
            $table->string('twox_booster_pts')->default(2);
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
