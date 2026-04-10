<div class="card-body">
    <table id="withdrawRequests-table" class="table table-bordered dt-responsive nowrap table-striped align-middle"
        style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Contact Details</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($withdrawRequests as $key => $withdrawRequest)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center position-relative">
                            <div class="position-relative">
                                <img src="{{ $withdrawRequest->astrologer->profile_picture }}"
                                    onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                    class="rounded-circle avatar-sm me-2" alt="Profile Picture" />
                            </div>
                            <div>
                                <div>
                                    <a href="{{ route('admin.experts.show', $withdrawRequest->astrologer->id) . '?tab=appointments' }}">{{ $withdrawRequest->astrologer->first_name }}
                                        {{ $withdrawRequest->astrologer->last_name }}</a>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        {{ $withdrawRequest->astrologer->email }} <br>
                        {{ $withdrawRequest->astrologer->mobile_number }}
                    </td>
                    <td>
                        {{ $currencySymbol }} {{ $withdrawRequest->amount }}
                    </td>
                    <td>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-more-fill align-middle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item edit-item-btn view-withdrawRequest-btn" href="#"
                                        data-id="{{ $withdrawRequest->id }}" 
                                        data-amount="{{ $withdrawRequest?->amount }}" 
                                        data-astrologer-id="{{ $withdrawRequest?->astrologer?->id }}"
                                        data-name="{{ $withdrawRequest?->astrologer?->full_name }}"
                                        data-email="{{ $withdrawRequest?->astrologer?->email }}"
                                        data-phone="{{ $withdrawRequest?->astrologer?->mobile_number }}"
                                        data-avatar="{{ $withdrawRequest?->astrologer?->profile_picture }}"
                                        data-holder-name="{{ $withdrawRequest?->astrologer?->bankDetails?->beneficiary_name }}"
                                        data-account-number="{{ $withdrawRequest?->astrologer?->bankDetails?->account_number }}"
                                        data-bank-name="{{ $withdrawRequest?->astrologer?->bankDetails?->name }}"
                                        data-ifsc-code="{{ $withdrawRequest?->astrologer?->bankDetails?->ifsc_code }}"
                                        data-pan-number="{{ $withdrawRequest?->astrologer?->bankDetails?->pan_number }}"
                                        data-status="{{ $withdrawRequest?->status }}"
                                        data-rejected-reason="{{ $withdrawRequest?->reject_reason }}"
                                        data-bs-toggle="modal" data-bs-target="#viewwithdrawRequestModal">
                                        <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if (!$withdrawRequests->hasPages() && $withdrawRequests->total() > 0)
        <p>Showing {{ $withdrawRequests->firstItem() }} to {{ $withdrawRequests->lastItem() }} of
            {{ $withdrawRequests->total() }} results</p>
    @endif
    {{ $withdrawRequests->appends(request()->query())->links() }}
</div>