@extends('layouts.master')
@section('title')
    Products
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/classic.min.css') }}" />
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/monolith.min.css') }}" />
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/nano.min.css') }}" />
@endsection
@section('content')
    @component('components.breadcrumb')
        @section('add-route')
            {{ route('admin.products.create') }}
        @endsection
        @slot('li_1')
            Products
        @endslot
        @slot('title')
            Manage Products
        @endslot
    @endcomponent
    <div class="row">
        @include('components.add-button')
        @foreach ($products as $product)
            <div class="col-xxl-4 col-lg-6">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0 fw-bold">{{ $product->title }}</h5>
                        <span><i class="ri-time-line me-1"></i>{{ $product->duration }}</span>
                    </div>
                    {!! $product->description !!}
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            @if ($product->is_gst == 1)
                                ₹ {{ number_format($product->total_price, 2) }}
                            @else
                                ₹ {{ number_format($product->price, 2) }}
                            @endif
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input flexSwitchCheckDefault" type="checkbox" role="switch"
                                    id="flexSwitchCheckDefault" data-product-id="{{ $product->id }}"
                                    {{ $product->status === 1 ? 'checked' : '' }}>
                            </div>

                            <div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item edit-item-btn"
                                            href="{{ route('admin.products.edit', $product->id) }}">
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item remove-item-btn cursor-pointer"
                                            onclick="confirmDelete({{ $product->id }})">
                                            <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i>
                                            <span class="text-danger">Delete</span>
                                        </a>
                                        <form id="delete-form-{{ $product->id }}"
                                            action="{{ route('admin.products.destroy', $product->id) }}" method="POST"
                                            style="display: none;">
                                            @csrf
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        function confirmDelete(productID) {
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
                    document.getElementById('delete-form-' + productID).submit();
                }
            })
        }
    </script>
    <script>
        $(document).ready(function() {
            $('.flexSwitchCheckDefault').change(function() {
                var status = $(this).is(':checked') ? 1 : 0;
                var productId = $(this).data('product-id');

                $.ajax({
                    url: "{{ route('admin.update-product-status') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        product_id: productId,
                        status: status
                    },
                    success: function(response) {
                        toastr.success(response.message);
                    },
                    error: function() {
                        toastr.error('Could not update status.');
                    }
                });
            });
        });
    </script>
@endsection
