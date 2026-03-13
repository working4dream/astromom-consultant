<div class="d-flex justify-content-end">
    <a href="{{ route('withdraw.export', ['tab' => 'payout']) }}" class="btn btn-primary waves-effect waves-light mb-3 float-end">
        <i class="ri-file-excel-line"></i> Export
    </a>
</div>
<div class="card-body">
    <table id="withdrawRequests-table" class="table table-bordered dt-responsive nowrap table-striped align-middle"
        style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Contact Details</th>
                <th>Total Amount</th>
                <th>Earning Amount</th>
                <th>Withdrawals</th>
                <th>Due Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($astrologers as $key => $astrologer)
            @php
                $totalWithdrawals = $astrologer->total_withdrawals ?? 0;
                $totalEarnings = $astrologer->total_earnings ?? 0;
                $dueAmount = $totalEarnings - $totalWithdrawals;
            @endphp
                @if ($dueAmount > 0)
                    <tr>
                        <td>{{ ($astrologers->currentPage() - 1) * $astrologers->perPage() + $key + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center position-relative">
                                <div class="position-relative">
                                    <img src="{{ $astrologer->profile_picture }}"
                                        onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                        class="rounded-circle avatar-sm me-2" alt="Profile Picture" />
                                </div>
                                <div>
                                    <div>
                                        <a
                                            href="{{ route('admin.experts.show', $astrologer->id) . '?tab=appointments' }}">
                                            {{ $astrologer->first_name }} {{ $astrologer->last_name }} <br>
                                        </a>
                                        <span class="text-muted">{{ $astrologer->city->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ $astrologer->email }} <br>
                            {{ $astrologer->mobile_number }}
                        </td>
                        <td>₹ {{ number_format($astrologer->total_earnings * 2 ?? 0, 2) }}</td>
                        <td>₹ {{ number_format($astrologer->total_earnings ?? 0, 2) }}</td>
                        <td>₹ {{ number_format($astrologer->total_withdrawals ?? 0, 2) }}</td>
                        <td>
                            ₹ {{ number_format($dueAmount, 2) }}
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
                                            data-id="{{ $astrologer->id }}"
                                            data-amount="{{ $astrologer?->total_earnings }}"
                                            data-astrologer-id="{{ $astrologer?->id }}"
                                            data-name="{{ $astrologer?->full_name }}"
                                            data-email="{{ $astrologer?->email }}"
                                            data-phone="{{ $astrologer?->mobile_number }}"
                                            data-avatar="{{ $astrologer?->profile_picture }}"
                                            data-holder-name="{{ $astrologer?->bankDetails?->beneficiary_name }}"
                                            data-account-number="{{ $astrologer?->bankDetails?->account_number }}"
                                            data-bank-name="{{ $astrologer?->bankDetails?->name }}"
                                            data-ifsc-code="{{ $astrologer?->bankDetails?->ifsc_code }}"
                                            data-pan-number="{{ $astrologer?->bankDetails?->pan_number }}"
                                            data-status="{{ $astrologer?->status }}"
                                            data-rejected-reason="{{ $astrologer?->reject_reason }}"
                                            data-bs-toggle="modal" data-bs-target="#viewwithdrawRequestModal">
                                            <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    @if (!$astrologers->hasPages() && $astrologers->total() > 0)
        <p>Showing {{ $astrologers->firstItem() }} to {{ $astrologers->lastItem() }} of
            {{ $astrologers->total() }} results</p>
    @endif
    {{ $astrologers->appends(request()->query())->links() }}
</div>
