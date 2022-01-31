@extends('layouts.app')
@section('title', 'App Setting')
@section('content')

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <span>Gameweek</span>
                </div>
                <div class="card-body">
                    <p>Current Gameweek</p>
                    <h4>Gameweek {{ $options->current_gameweek }}</h4>
                    <hr>
                    <form action="{{ route('settings.update_current_gameweek') }}" method="POST">
                        @csrf
                        <label for="gameweek">Change Gameweek</label>
                        <select name="gameweek" id="gameweek" class="form-control select2">
                            <option>Select</option>
                            @for ($i = 1; $i < 39; $i++)
                                <option value="{{ $i }}">Gameweek {{ $i }}</option>
                            @endfor
                        </select>
                        <button type="submit"
                            class="float-right btn mt-2 btn-xs waves-effect waves-light btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <span>Wining Pts Management</span>
                </div>
                <div class="card-body">
                    <form action="">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="">Win / Lose / Draw</label>
                                    <input type="number" name="win_lose_draw" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="">Goal Different</label>
                                    <input type="number" name="goal_different" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="">Home Goals</label>
                                    <input type="number" name="home_goals" class="form-control">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="">Away Goals</label>
                                    <input type="number" name="away_goals" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="">Underdog Bonus</label>
                                    <input type="number" name="underdog_bonus" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="">2x Booster</label>
                                    <input type="number" name="2x_booster" class="form-control">
                                </div>
                            </div>
                        </div>
                        <button type="submit"
                            class="float-right btn mt-2 btn-xs waves-effect waves-light btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('scripts')

    <script>
        $(document).ready(function() {
            $(".select2").select2({});

        })
    </script>

@endsection
