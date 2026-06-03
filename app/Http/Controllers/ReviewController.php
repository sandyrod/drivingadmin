<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Store a newly created review in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'instructor_id' => 'required|exists:instructors,id',
            'rating' => 'required|integer|min:1|max:5',
            'vehicle_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $review = Review::create([
                'instructor_id' => $validated['instructor_id'],
                'rating' => $validated['rating'],
                'vehicle_rating' => $validated['vehicle_rating'],
                'comment' => $validated['comment'] ?? 'Sin comentarios adicionales',
            ]);

            DB::commit();

            // Load instructor model
            $review->load('instructorModel');

            return response()->json([
                'success' => true,
                'review' => [
                    'id' => 'REV-' . str_pad($review->id, 3, '0', STR_PAD_LEFT),
                    'db_id' => $review->id,
                    'instructor' => $review->instructorModel ? ($review->instructorModel->first_name . ' ' . $review->instructorModel->last_name) : 'Instructor Eliminado',
                    'instructor_id' => $review->instructor_id,
                    'rating' => (int) $review->rating,
                    'vehicleRating' => (int) $review->vehicle_rating,
                    'comment' => $review->comment,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error registrando calificación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
