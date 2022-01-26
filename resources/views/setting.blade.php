@extends('layouts.app')
@section('title', 'App Setting')
@section('content')

    <div class="row">
        <div class="col-md-3">
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
                        <p>Change Gameweek</p>
                        <select name="gameweek" class="form-control select2">
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
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <span>Wining Pts Setting</span>
                </div>
                <div class="card-body">

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
