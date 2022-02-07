@extends('layouts.app')
@section('title', 'Predictions')
@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <table id="datatable" class="table  table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pts</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Gameweek</th>
                                <th>Fixture ID</th>
                                <th>Updated</th>
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
                ajax: "ssd/predictions",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'total_pts',
                        name: 'total_pts'
                    },
                    {
                        data: 'user.name',
                        name: 'user.name'
                    },
                    {
                        data: 'user.phone',
                        name: 'user.phone'
                    },
                    {
                        data: 'user.email',
                        name: 'user.email'
                    },
                    {
                        data: 'fixture_event',
                        name: 'fixture_event'
                    },
                    {
                        data: 'fixture_id',
                        name: 'fixture_id'
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            setInterval(function() {
                table.ajax.reload();
            }, 30000);
        })
    </script>

@endsection
