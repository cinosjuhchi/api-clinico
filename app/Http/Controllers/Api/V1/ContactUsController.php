<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactUsController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|min:3|string',
            'email' => 'required|min:6|email',
            'number_telephone' => 'required|min:6|string',
            'message' => 'string|min:6',
        ]);

        try {
            Mail::to("verification@clinico.site")->send(new ContactUs($data));
        } catch (Throwable $e) {
            Log::error("email gagal dikirim" . $e->getMessage());
            return response()->json(['message' => 'Email failed'], 500);
        }

        return response()->json(['message' => 'Email success'], 200);
    }
}
