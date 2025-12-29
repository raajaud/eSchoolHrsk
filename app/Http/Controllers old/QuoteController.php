<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Quote;
use DB;
use Illuminate\Http\Request;


class QuoteController extends Controller {

    public function __construct() {

    }

    public function index()
    {
        $quotes = Quote::all();
        return view('quotes.index', compact('quotes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'quote' => 'required|string',
            'author' => 'required|string',
        ]);

        $quote = Quote::create([
            'quote' => $request->quote,
            'author' => $request->author,
            'published' => $request->has('published') ? 1 : 0,
        ]);

        if ($request->has('published') && $request->published) {
            $announcement = Announcement::create([
                'title' => 'Thought of the Day',
                'description' => $quote->quote . ' - ' . $quote->author,
                'session_year_id' => 4, // Replace with dynamic value if needed
                'school_id' => 5, // Replace with dynamic value if needed
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assuming you want to publish to specific class_sections
            // Replace with your actual logic for class_section_ids
            $classSectionIds = [8, 9, 10, 11, 12, 13, 14, 15, 16];
            foreach ($classSectionIds as $classSectionId) {
                DB::table('announcement_classes')->insert([
                    'announcement_id' => $announcement->id,
                    'class_section_id' => $classSectionId,
                    'class_subject_id' => null, // Or your dynamic value
                    'school_id' => 5, // Replace with dynamic value if needed
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Quote added successfully!');
    }
}
