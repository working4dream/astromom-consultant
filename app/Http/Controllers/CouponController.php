<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function index(Request $request) 
    {
        $coupons = Coupon::query();
        if (request()->filled('name')) {
            $coupons->where('name', 'LIKE', "%".$request->name."%");
        }
        if (request()->filled('code')) {
            $coupons->where('code', 'LIKE', "%".$request->code."%");
        }
        if($request->filled('used_type')){
            $used_types =request()->used_type;
            $coupons->where(function($q) use($used_types) { 
                foreach($used_types as $type){
                    $q->orWhere('used_type', 'LIKE', "%{$type}%");
                }
           });
        }
        $coupons=$coupons->orderBy('id','Desc')->paginate(request('items') ?? 20)->withQueryString();
        return view('coupons.index', compact('coupons'));
    }
    public function create() 
    {
       
        return view('coupons.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:coupons',
            'code' => 'required|unique:coupons',
            'discount_type' => 'required',
            'discount_value' => 'required',
            'used_type' => 'required',
            'min_order_amount' => 'required',
            'max_discount' => 'required',
            'start_date' => 'required',
            'expiry_date' => 'required',
            'used_counts' => 'required',
        ]);
        
         $coupon =Coupon::create([
            'name' => $request->name,
            'code' => $request->code,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value??0,
            'min_order_amount' => $request->min_order_amount??0,
            'max_discount' => $request->max_discount??0,
            'start_date' => $request->start_date,
            'expiry_date' => $request->expiry_date,
            'active' => isset($request->active)?1:0,
            'creator_id' =>Auth::user()->id,
            'used_counts'=>$request->used_counts,
            'used_type'=>implode(',',$request->used_type),
        ]);
        return redirect()->route('admin.coupons.index')->with("success", 'Coupon Added successfully');
    }
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
       return view('coupons.edit', compact('coupon'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:coupons,name,'.$id,
            'code' => 'required|unique:coupons,code,'.$id,
            'discount_type' => 'required',
            'discount_value' => 'required',
            'used_type' => 'required',
            'min_order_amount' => 'required',
            'max_discount' => 'required',
            'start_date' => 'required',
            'expiry_date' => 'required',
            'used_counts' => 'required',
        ]);
        $coupon = Coupon::findOrFail($id);
        $coupon->update([
            'name' => $request->name,
            'code' => $request->code,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value??0,
            'min_order_amount' => $request->min_order_amount??0,
            'max_discount' => $request->max_discount??0,
            'start_date' => $request->start_date,
            'expiry_date' => $request->expiry_date,
            'active' => isset($request->active)?1:0,
            'used_counts'=>$request->used_counts,
            'used_type'=>implode(',',$request->used_type),
        ]);
        return redirect()->route('admin.coupons.index')->with("success", 'Coupon updated successfully');
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully');
    }

    public function checkCoupon(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)->where('used_type', 'LIKE' , '%'.strtolower($request->used_type).'%')->where('active',1)->first();
        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.']);
        }

        $currentDate = now();
        if($coupon->start_date && $coupon->expiry_date) {
            if ($currentDate < $coupon->start_date || $currentDate > $coupon->expiry_date) {
                return response()->json(['success' => false, 'message' => 'This coupon has expired or is not yet active.']);
            }
        }

        $discount = 0;
        if ($coupon->discount_type == 'percentage') {
            $discount = ($request->order_amount * $coupon->discount_value) / 100;
            if ($coupon->max_discount !== "0.00" && $discount > $coupon->max_discount) {
                $discount = $coupon->max_discount;
            }
        } elseif ($coupon->discount_type == 'fixed') {
            $discount = $coupon->discount_value;
        }
        // used_count validation
        if ((int)$coupon->used_counts === 0) {
            return response()->json(['success' => false, 'message' => 'This coupon has reached its usage limit.']);
        }
        elseif((int)$coupon->used_counts > 0){
            $coupon->decrement('used_counts');
        }

        $originalPrice = 0;

        $discountAmount = ($coupon->discount_type === 'percentage')
            ? ($originalPrice * $coupon->discount_value / 100)
            : $coupon->discount_value;

        $finalPrice = max(0, $originalPrice - $discountAmount);

        return response()->json([
            'success' => true,
            'final_price' => $finalPrice,
            'discount_amount' => $discountAmount,
            'coupon_id' => $coupon->id,
            'original_price' => $originalPrice,
            'message' => 'Coupon applied successfully.'
        ]);
    }
}
