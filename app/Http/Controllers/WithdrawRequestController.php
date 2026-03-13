<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AstrologerWithdrawal;
use App\Models\AstrologerWalletHistory;
use App\Models\AstrologerEarning;
use App\Models\User;
use App\Services\NotificationService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WithdrawRequestExport;

class WithdrawRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('tab') && $request->tab === 'payout') {
            $astrologers = User::role('astrologer')
                ->withSum('approvedEarnings as total_earnings', 'amount')
                ->withSum('approvedWithdrawals as total_withdrawals', 'amount')
                ->having('total_earnings', '>', 0)
                ->havingRaw('(COALESCE(total_withdrawals, 0)) = 0')
                ->with('city')
                ->paginate(20);
        
            return view('withdraw_request.index', [
                'astrologers' => $astrologers,
                'tab' => 'payout',
            ]);
        }
        $query = AstrologerWithdrawal::query();
        if ($request->has('tab')) {
            switch ($request->tab) {
                case 'approved':
                    $query->where('status', 28);
                    break;
                case 'pending':
                    $query->where('status', 27);
                    break;
                case 'rejected':
                    $query->where('status', 29);
                    break;
            }
        }

        $withdrawRequests = $query->orderByDesc('id')->paginate(20);
        return view('withdraw_request.index', [
            'withdrawRequests' => $withdrawRequests,
            'tab' => $request->tab,
        ]);
    }
    
    public function updateWithdrawApprovalStatus(Request $request, NotificationService $notificationService)
    {
        if ($request->tab === 'payout') {
            AstrologerWithdrawal::where('astrologer_id', $request->astrologer_id)
                ->where('status', 27)
                ->delete();

            $newWithdrawRequest = AstrologerWithdrawal::create([
                'astrologer_id' => $request->astrologer_id,
                'amount' => $request->amount,
                'status' => $request->status,
                'comment' => $request->comment,
                'reject_reason' => $request->reason,
            ]);

            $walletHistory = AstrologerWalletHistory::create([
                'astrologer_id' => $request->astrologer_id,
                'type' => 2,
                'message' => 'debited to your account for the withdraw',
                'amount' => $request->amount,
            ]);
            $deviceTokens = json_decode($newWithdrawRequest->astrologer->device_token);
            $notificationService->sendNotification('Your payout has been successfully processed!', 'A payout of ₹' . $newWithdrawRequest->amount . ' has been transferred to your account. It should reflect within 24 hours.', $deviceTokens);
            return response()->json(['message' => 'Status updated successfully!']);
        } else {
            $withdrawRequest = AstrologerWithdrawal::find($request->id);
            if (!$withdrawRequest) {
                return response()->json(['message' => 'Withdraw Request not found!'], 404);
            }
        
            $withdrawRequest->update([
                'status' => $request->status,
                'reject_reason' => $request->reason,
                'comment' => $request->comment,
            ]);

            $walletHistory = AstrologerWalletHistory::create([
                'astrologer_id' => $withdrawRequest->astrologer_id,
                'type' => 2,
                'message' => 'debited to your account for the withdraw',
                'amount' => $withdrawRequest->amount,
            ]);
            $deviceTokens = json_decode($withdrawRequest->astrologer->device_token);
            $notificationService->sendNotification('Your payout has been successfully processed!', 'A payout of ₹' . $withdrawRequest->amount . ' has been transferred to your account. It should reflect within 24 hours.', $deviceTokens);
            return response()->json(['message' => 'Status updated successfully!']);
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new WithdrawRequestExport($request->tab), 'payout-withdraw-requests.xlsx');
    }
}
