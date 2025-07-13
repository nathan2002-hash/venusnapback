<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactFormSubmitted;
use App\Mail\ContactFormUserSubmitted;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ContactFormController extends Controller
{

    private function verifyRecaptcha($token)
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $token,
        ]);

        $result = $response->json();

        return isset($result['success']) && $result['success'] === true;
    }

    public function submit(Request $request)
    {
         if ($request->filled('website')) {
            return response()->json([
                'success' => false,
                'message' => 'Bot detected.'
            ], 403);
        }

       if (!$this->verifyRecaptcha($request->input('g-recaptcha-response'))) {
            return response()->json(['success' => false, 'message' => 'reCAPTCHA failed.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $formData = $request->only(['name', 'email', 'phone', 'subject', 'message']);

        // Add investment flag if detected
        $formData['is_investment'] = $this->isInvestmentRequest($formData);

        // Send to admin (queued)
        Mail::to('quixnes@proton.me')
            ->queue(new ContactFormSubmitted($formData));

        // Send confirmation to user (queued)
        Mail::to($formData['email'])
            ->queue(new ContactFormUserSubmitted($formData));

        return response()->json(['success' => true]);
    }

    protected function isInvestmentRequest(array $data): bool
    {
        $investmentKeywords = ['invest', 'funding', 'capital', 'investment'];

        $subject = strtolower($data['subject']);
        $message = strtolower($data['message']);

        foreach ($investmentKeywords as $keyword) {
            if (str_contains($subject, $keyword) || str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }
}
