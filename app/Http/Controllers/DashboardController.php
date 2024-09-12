<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index($uid)
    {
        $userId = Auth::id();

        if (md5($userId) !== $uid) {
            abort(403, 'Forbidden');
        }

        return view('dashboard.index', ['id' => $uid]);
    }
}
