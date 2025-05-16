<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ContactSupport;
use Illuminate\Support\Facades\Auth;

class ContactSupportController extends Controller
{
    public function store(Request $request)
    {
        // Get the authenticated user's ID
        $user_id = Auth::id();

        $support = new ContactSupport();
        $support->user_id = $user_id;
        $support->category = $request->category;
        $support->topic = $request->topic;
        if ($request->priority == "Urgent (Service Down)") {
            $support->priority = "urgent";
        } else {
           $support->priority = $request->priority;
        }
        $support->description = $request->description;
        $support->status = "Open";
        $support->save();
        // Return the simplified response
        return response()->json([
            'message' => 'Support ticket created successfully',
            'ticket_id' => $support->id,
        ], 201);
    }

    public function index(Request $request)
    {
        $tickets = ContactSupport::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'category' => $ticket->category,
                    'topic' => $ticket->topic,
                    'description' => $ticket->description,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'created_at' => $ticket->created_at->toDateTimeString(),
                    'updated_at' => $ticket->updated_at ? $ticket->updated_at->toDateTimeString() : null,
                ];
            })
        ]);
    }

    // In Laravel controller
    public function updateStatus(Request $request, $id)
    {
        $ticket = ContactSupport::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

        $ticket->status = 'Resolved';
        $ticket->resolved_at = now();
        $ticket->save();

        return response()->json([
            'message' => 'Ticket status updated',
            'ticket' => $ticket
        ]);
    }

public function getFaqs()
{
    $faqs = [
        [
            'question' => 'How do I request a payout?',
            'answer' => 'Go to Monetization Settings → Payment Method and follow the instructions to set up your payout account.',
            'category' => 'Payment Problem'
        ],
        [
            'question' => 'Why is my payment delayed?',
            'answer' => 'Payments are processed within 7 business days. Delays may occur during holidays or if additional verification is needed.',
            'category' => 'Payment Problem'
        ],
        [
            'question' => 'How do I reset my password?',
            'answer' => 'Go to Account Settings → Security → Change Password. You\'ll receive an email with reset instructions.',
            'category' => 'Account Help'
        ],
        [
            'question' => 'Why can\'t I log in?',
            'answer' => 'Ensure you\'re using the correct credentials. If locked out, use the "Forgot Password" feature or contact support.',
            'category' => 'Account Help'
        ],
        [
            'question' => 'How do I report a bug?',
            'answer' => 'Use the "Report Issue" option in settings or describe the problem in detail when submitting a support ticket.',
            'category' => 'Technical Issue'
        ],
        [
            'question' => 'Why is the app crashing?',
            'answer' => 'Try updating to the latest version. If issues persist, clear app cache or reinstall the application.',
            'category' => 'Technical Issue'
        ],
        [
            'question' => 'How do I report inappropriate content?',
            'answer' => 'Tap the three dots next to the content and select "Report". Our team will review within 24 hours.',
            'category' => 'Content Issue'
        ],
        [
            'question' => 'What isn\'t covered by support?',
            'answer' => 'General usage questions should first consult our Help Center. Business decisions and third-party integrations may have limited support.',
            'category' => 'Other'
        ],
    ];

    return response()->json([
        'data' => $faqs
    ]);
}
}
