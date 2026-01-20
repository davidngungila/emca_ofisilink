<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationProvider;
use Illuminate\Http\Request;

class EmailProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:System Admin');
    }

    /**
     * Show the form for creating a new email provider.
     */
    public function create()
    {
        return view('admin.settings.communication.email-providers.create');
    }

    /**
     * Show the form for editing an email provider.
     */
    public function edit(NotificationProvider $provider)
    {
        if ($provider->type !== 'email') {
            abort(404, 'Provider is not an email provider');
        }
        return view('admin.settings.communication.email-providers.edit', compact('provider'));
    }
}

