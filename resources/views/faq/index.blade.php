@extends('layouts.master')
@section('title')
    FAQ
@endsection
@section('content')
    @component('components.breadcrumb')
        @section('add-route')
            {{ route('admin.faqs.create') }}
        @endsection
        @slot('li_1')
            Dashboard
        @endslot
        @slot('title')
            Frequently Asked Questions
        @endslot
    @endcomponent
    <div class="row">
        @include('components.add-button')
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <table id="faqs-table" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                        style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Question</th>
                                <th>Answer</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($faqs ?? [] as $key => $faq)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td class="text-wrap text-break">
                                        {{ $faq->question }}
                                    </td>
                                    <td class="text-wrap text-break">{{ $faq->answer }}</td>
                                    <td>{{ $faq->type }}</td>
                                    <td>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item edit-item-btn"
                                                        href="{{ route('admin.faqs.edit', $faq->id) }}">
                                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i>
                                                        Edit</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item remove-item-btn" href="javascript:void(0);"
                                                        onclick="confirmDelete({{ $faq->id }})">
                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i>
                                                        <span class="text-danger">Delete</span>
                                                    </a>
                                                </li>
                                                <div>
                                                    <form id="delete-form-{{ $faq->id }}"
                                                        action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST"
                                                        style="display: none;">
                                                        @csrf
                                                    </form>
                                                </div>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if (!$faqs->hasPages() && $faqs->total() > 0)
                        <p>Showing {{ $faqs->firstItem() }} to {{ $faqs->lastItem() }} of {{ $faqs->total() }} results</p>
                    @endif
                    {{ isset($faqs) ? $faqs->appends(request()->query())->links() : '' }}
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
            $('#faqs-table').DataTable({
                searching: false,
                ordering: true,
                paging: false,
                bInfo: false,
                lengthChange: false
            });
        });
    </script>
    <script>
        function confirmDelete(adminID) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + adminID).submit();
                }
            })
        }
    </script>
@endsection
