<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ContactInquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    /**
     * Display the public website for a business.
     */
    public function show(string $slug): View
    {
        $business = Business::with(['sections' => function ($q) {
            $q->where('is_visible', true)->orderBy('order_index');
        }])->where('slug', $slug)->firstOrFail();

        // In draft mode, only display preview banner if accessed directly
        return view('site.show', compact('business'));
    }

    /**
     * Handle public contact inquiries and store in MySQL.
     */
    public function submitInquiry(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:3000',
        ]);

        $inquiry = ContactInquiry::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'],
            'status' => 'new',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Thank you {$inquiry->name}! Your message has been sent to {$business->name}.",
        ]);
    }
}
