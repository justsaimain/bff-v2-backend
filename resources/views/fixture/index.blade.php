@extends('layouts.app')
@section('title', 'Fixtures')
@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <table id="datatable" class="table table-responsive table-striped table-bordered " style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Gameweek</th>
                                <th>Home</th>
                                <th>Away</th>
                                <th>Result</th>
                                <th>Kickoff Time</th>
                                <th>Minutes</th>
                                <th>Provisional Start Time</th>
                                <th>Finished Provisional</th>
                                <th>Started</th>
                                <th>Finished</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')

    <script>
        $(document).ready(function() {
            const table = $('#datatable').DataTable({
                processing: true,
                responsive: true,
                serverSide: true,
                ajax: "ssd/fixtures",
                columns: [{
                        data: 'id',
                        name: 'id'
                    }, {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'event',
                        name: 'event'
                    }, {
                        data: 'team_h',
                        name: 'team_h'
                    },
                    {
                        data: 'team_a',
                        name: 'team_a'
                    },
                    {
                        data: 'result',
                        name: 'result'
                    },
                    {
                        data: 'kickoff_time',
                        name: 'kickoff_time'
                    },
                    {
                        data: 'minutes',
                        name: 'minutes',
                    },
                    {
                        data: 'provisional_start_time',
                        name: 'provisional_start_time',
                    },
                    {
                        data: 'finished_provisional',
                        name: 'finished_provisional',
                    },
                    {
                        data: 'started',
                        name: 'started'
                    },
                    {
                        data: 'finished',
                        name: 'finished',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        })
    </script>

@endsection
