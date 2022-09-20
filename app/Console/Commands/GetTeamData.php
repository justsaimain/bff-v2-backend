<?php

namespace App\Console\Commands;

use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GetTeamData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:team';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Team and Store in Database';

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
     * @return int
     */
    public function handle()
    {
        $teams = Http::withHeaders([
            'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
            'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
        ])->get('https://fantasy-premier-league3.p.rapidapi.com/teams/simple', [])->json();


        foreach ($teams as $team) {
            $new_team = new Team();
            $new_team->code = $team['code'];
            $new_team->name = $team['name'];
            $new_team->short_name = $team['short_name'];

            $filename = $team['code'] . '.png';
            $new_team->logo = Storage::url('teams/' . $filename);
            $contents = file_get_contents('https://resources.premierleague.com/premierleague/badges/70/t' . $team['code'].  '.png');
            Storage::disk('public')->put('teams/' . $filename, $contents);
            $new_team->save();
        }

        $this->info('Stored');

    }
}
