<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePredictionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('team_h');
            $table->integer('team_a');
            $table->boolean('2x_booster')->default(false);
            $table->string('fixture_code');
            $table->string('fixture_event');
            $table->string('fixture_id');
            $table->string('fixture_kickoff_time');
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
        Schema::dropIfExists('predictions');
    }
}
