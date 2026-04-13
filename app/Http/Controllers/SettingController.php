<?php

namespace App\Http\Controllers;

use App\Traits\AwsS3Trait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    use AwsS3Trait;
    public function editProfile() 
    {
        return view('settings.edit-profile', [
            'timezones' => \DateTimeZone::listIdentifiers(\DateTimeZone::ALL),
        ]);
    }

    public function editProfileStore (Request $request) 
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'timezone' => ['nullable', 'timezone'],
        ]);
        $admin = Auth::user();

        if ($request->hasFile('profile_picture')) {
            if ($admin->profile_picture) {
                $this->deleteFileFromS3($admin->profile_picture);
            }
    
            $filePath =  $this->uploadFileToS3($request->file('profile_picture'), 'admin/profile-picture');
            $admin->profile_picture = $filePath;
        }
        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->timezone = $request->filled('timezone') ? $request->timezone : null;
        $admin->save();
        
        return redirect()->back()->with('success', 'Profile Updated successfully!');
    }

    public function changePasswordstore(Request $request) {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:8',
        ]);

        $admin = Auth::user();
        if (!Hash::check($request->old_password, $admin->password)) {
            return redirect()->back()->withErrors(['old_password' => 'The old password is incorrect.']);
        }
        $admin->password = Hash::make($request->password);
        $admin->save();
        
        return redirect()->back()->with('success', 'Password changed successfully!');
    }
}
