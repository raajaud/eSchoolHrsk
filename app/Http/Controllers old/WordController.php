<?php

namespace App\Http\Controllers;

use App\Models\DailyWord;
use Illuminate\Http\Request;

class WordController extends Controller
{
    public function index()
    {
        $words = DailyWord::orderByDesc('publish_date')->get();
        return view('words.index', compact('words'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'english_word' => 'required|string|max:255',
            'hindi_word' => 'required|string|max:255',
            'pronunciation' => 'nullable|string|max:255',
            'hindi_meaning' => 'nullable|string|max:255',
            'publish_date' => 'nullable|date',
        ]);

        DailyWord::create([
            'english_word' => $request->english_word,
            'hindi_word' => $request->hindi_word,
            'pronunciation' => $request->pronunciation,
            'hindi_meaning' => $request->hindi_meaning,
            'publish_date' => $request->publish_date,
        ]);

        return redirect()->back()->with('success', 'Word added successfully!');
    }
}
