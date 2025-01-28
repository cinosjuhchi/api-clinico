<?php
namespace App\Http\Controllers;

use App\Http\Requests\StorePushNotificationRequest;
use App\Http\Requests\UpdatePushNotificationRequest;
use App\Models\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class PushNotificationController extends Controller
{

    public function saveSubscription(Request $request)
    {
        $validated = $request->validate([
            'endpoint'    => 'required',
            'keys'        => 'required|array',
            'keys.p256dh' => 'required',
            'keys.auth'   => 'required',
        ]);

        $user = Auth::user();

        // Periksa apakah subscription dengan endpoint yang sama sudah ada
        $existingSubscription = $user->pushSubscriptions()
            ->where('endpoint', $validated['endpoint']) // Periksa berdasarkan endpoint
            ->first();

        if ($existingSubscription) {
            // Jika sudah ada, tidak perlu menambahkan lagi
            return response()->json(['message' => 'Subscription already exists'], 200);
        }

        // Simpan subscription baru
        $user->pushSubscriptions()->create([
            'endpoint' => $validated['endpoint'],
            'p256dh'   => $validated['keys']['p256dh'],
            'auth'     => $validated['keys']['auth'],
        ]);

        return response()->json(['message' => 'Subscription saved successfully!'], 201);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePushNotificationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PushNotification $pushNotification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PushNotification $pushNotification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePushNotificationRequest $request, PushNotification $pushNotification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PushNotification $pushNotification)
    {
        //
    }
}
