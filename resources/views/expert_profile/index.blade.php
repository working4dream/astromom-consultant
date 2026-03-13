@extends('layouts.master')
@section('title')
    Expert Profile
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Expert Profile
        @endslot
        @slot('title')
            Expert Profile
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <table id="experts-table" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                        style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile Number</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expertProfiles as $key => $expertProfile)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <img src="{{ $expertProfile->profile_picture }}"
                                            onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                            class="rounded-circle avatar-sm me-2" alt="Profile Picture" />
                                        {{ $expertProfile->first_name }} {{ $expertProfile->last_name }}
                                    </td>
                                    <td>{{ $expertProfile->email }}</td>
                                    <td>{{ $expertProfile->mobile_number }}</td>
                                    <td>
                                        <a href="{{ route('admin.experts-profile.download', $expertProfile->id) }}"
                                            class="btn btn-primary btn-sm">
                                            <i class="ri-download-2-line"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $expertProfile->id }}" 
                                            data-url="{{ route('admin.experts-profile.delete', $expertProfile->id) }}">
                                            <i class="ri-delete-bin-6-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if (!$expertProfiles->hasPages() && $expertProfiles->total() > 0)
                    <p>Showing {{ $expertProfiles->firstItem() }} to {{ $expertProfiles->lastItem() }} of {{ $expertProfiles->total() }} results</p>
                    @endif
                    {{ $expertProfiles->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#experts-table').DataTable({
                searching: false,
                ordering: true,
                paging: false,
                bInfo: false,
                lengthChange: false
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    let deleteUrl = this.getAttribute('data-url');
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(deleteUrl, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute(
                                        'content')
                                }
                            }).then(response => response.json()).then(data => {
                                if (data.success) {
                                    Swal.fire("Deleted!",
                                        "The profile has been deleted.",
                                        "success").then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire("Error!", "Something went wrong.",
                                        "error");
                                }
                            });
                        }
                    });
                });
            });
        });
    </script>
@endsection
