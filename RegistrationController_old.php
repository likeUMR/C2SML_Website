<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function submit(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'school' => 'required|string|max:255',
            'email' => 'required|email|unique:registrations|max:255',
            'title' => 'required|in:老师,学生,其它',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'supervisor' => 'nullable|string|max:255',
        ]);

        DB::table('registrations')->insert($validatedData);

        return redirect()->route('login')->with('success', '注册成功！');
    }
}