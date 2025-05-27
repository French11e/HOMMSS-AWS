<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Rules\TurnstileRule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        return view('index');
    }

    /**
     * Search for products
     */
    public function search(Request $request)
    {
        // Validate search input
        $validated = $request->validate([
            'query' => 'required|string|min:3|max:100',
        ]);

        $searchTerm = $validated['query'];

        // Use proper parameter binding for search to prevent SQL injection
        $results = Product::where(function ($query) use ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
        })
            ->with(['category:id,name', 'brand:id,name'])
            ->select(['id', 'name', 'slug', 'description', 'regular_price', 'image', 'category_id', 'brand_id'])
            ->limit(10)
            ->get();

        return response()->json($results);
    }

    /**
     * Show the about page
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Show the contact page
     */
    public function contact()
    {
        $subjectOptions = $this->getContactSubjects();
        return view('contact', compact('subjectOptions'));
    }

    /**
     * Get available contact form subjects
     */
    private function getContactSubjects(): array
    {
        return [
            'General Inquiry' => 'General Inquiry',
            'Product Information' => 'Product Information',
            'Quote Request' => 'Quote Request',
            'Technical Support' => 'Technical Support',
            'Order Inquiry' => 'Order Inquiry',
            'Installation Support' => 'Installation Support',
            'Warranty Claim' => 'Warranty Claim',
            'Complaint' => 'Complaint',
            'Partnership Inquiry' => 'Partnership Inquiry',
            'Other' => 'Other',
        ];
    }

    /**
     * Handle contact form submission
     */
    public function contactSubmit(Request $request)
    {
        // Validate the contact form
        $subjectOptions = array_keys($this->getContactSubjects());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|in:' . implode(',', $subjectOptions),
            'message' => 'required|string|max:2000',
            'honeypot' => 'max:0', // Anti-spam honeypot field
	    'cf-turnstile-response' => ['nullable', new TurnstileRule()],
        ], [
            'honeypot.max' => 'Invalid form submission',
            'subject.in' => 'Please select a valid subject from the dropdown.',
        ]);

        try {
            // Send email to admin
            $adminEmail = config('mail.admin_email', 'admin@hommss.com');

            Mail::send('emails.contact', $validated, function ($message) use ($validated, $adminEmail) {
                $message->to($adminEmail)
                    ->subject('New Contact Form Submission: ' . $validated['subject'])
                    ->replyTo($validated['email'], $validated['name']);
            });

            // Log the contact form submission for security monitoring
            Log::info('Contact form submitted', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('contact')->with('success', 'Thank you for your message! We will get back to you soon.');

        } catch (\Exception $e) {
            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'email' => $validated['email'],
                'ip' => $request->ip(),
            ]);

            return redirect()->route('contact')->with('error', 'Sorry, there was an error sending your message. Please try again later.');
        }
    }
}
