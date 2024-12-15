<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Http\Requests\FAQStoreRequest;

class FAQController extends Controller
{
    public function addFAQ(FAQStoreRequest $request) {
        try {
            $faqrequest = $request->validated(); 

            if ($faqrequest) {
                $faq = FAQ::create($faqrequest);

                return response()->json(['message' => 'FAQ Added Successfully!', 'FAQ' => $faq], 201);
            }
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getAllFAQ()
    {
        try {
            $faqs = FAQ::all();
            
            if ($faqs->isEmpty()) {
                return response()->json(['message' => 'No FAQs found', 'FAQs' => []], 200);
            }
            
            return response()->json(['message' => 'FAQs retrieved successfully', 'faqs' => $faqs], 200);
        } catch (\Throwable $th) {
            \Log::error('Error in getAllFAQ: ' . $th->getMessage());
            return response()->json(['message' => 'An error occurred while retrieving FAQs', 'error' => $th->getMessage()], 500);
        }
    }
}
