<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SubAdminController extends Controller
{
    public function index()
    {
        $subadmins = User::where('user_type', 'subadmin')->get();
        return view('admin.subadmin.index', compact('subadmins'));
    }

    public function create()
    {
        return view('admin.subadmin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required',
            'mobile'   => 'required',
            'password' => 'required|min:6',
        ]);

       $check= User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'mobile'    => $request->mobile,
            'password'  => Hash::make($request->password),
            'user_type' => 'subadmin',
            'status'    => 1,
        ]);
       

        return redirect()
            ->route('admin.subadmin.index')
            ->with('success', 'Sub Admin created successfully');
    }
    public function edit($id)
{
    $subadmin = User::where('user_type', 'subadmin')->findOrFail($id);
    return view('admin.subadmin.edit', compact('subadmin'));
}



public function update(Request $request, $id)
{
    $subadmin = User::where('user_type', 'subadmin')->findOrFail($id);

    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email,' . $subadmin->id,
        'mobile'   => 'required|unique:users,mobile,' . $subadmin->id,
        'status'   => 'required|boolean',
        'password' => 'nullable|min:6',
    ]);

    $data = [
        'name'   => $request->name,
        'email'  => $request->email,
        'mobile' => $request->mobile,
        'status' => $request->status,
    ];

    // Update password only if entered
    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

    $subadmin->update($data);

    return redirect()
        ->route('admin.subadmin.index')
        ->with('success', 'Sub Admin updated successfully');
}
  public function destroy($id)
{
    User::where('id', $id)->delete();

    return redirect()->back()->with('success', 'Sub Admin deleted successfully');
}


}