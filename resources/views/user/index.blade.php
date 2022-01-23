@extends('layouts.app')
@section('title', 'Users')
@section('content')
    <table id="datatable" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Region</th>
                <th>Fav Team</th>
                <th>Joined</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
@endsection


@section('scripts')

    <script>
        $(document).ready(function() {
            const table = $('#datatable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "ssd/users",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'phone',
                        name: 'phone',
                    },
                    {
                        data: 'region',
                        name: 'region'
                    },
                    {
                        data: 'fav_team',
                        name: 'fav_team'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $(document).on('click', '.delete-btn', function() {
                let id = $(this).data('id');
                $.ajax({
                    type: 'POST',
                    url: `/users/${id}`,
                    data: {
                        _method: "DELETE"
                    },
                    success: function(data) {
                        table.ajax.reload();
                        toastr.success('Account Deleted.')
                    }
                });
            });
        })
    </script>

@endsection
