<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationProvider;
use Illuminate\Http\Request;

class SmsProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:System Admin');
    }

    /**
     * Show the form for creating a new SMS provider.
     */
    public function create()
    {
        return view('admin.settings.communication.sms-providers.create');
    }

    /**
     * Show the form for editing an SMS provider.
     */
    public function edit(NotificationProvider $provider)
    {
        if ($provider->type !== 'sms') {
            abort(404, 'Provider is not an SMS provider');
        }
        return view('admin.settings.communication.sms-providers.edit', compact('provider'));
    }
}

