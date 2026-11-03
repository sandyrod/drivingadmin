<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Placeholder stored when the student leaves no comment.
     */
    public const EMPTY_COMMENT = 'Sin comentarios adicionales';

    /**
     * Store a newly created review in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'instructor_id' => 'required|exists:instructors,id',
            'author_name' => 'nullable|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'vehicle_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $review = Review::create([
                'instructor_id' => $validated['instructor_id'],
                'author_name' => $validated['author_name'] ?? null,
                'rating' => $validated['rating'],
                'vehicle_rating' => $validated['vehicle_rating'],
                'comment' => $validated['comment'] ?? self::EMPTY_COMMENT,
            ]);

            DB::commit();

            // Load instructor model
            $review->load('instructorModel');

            return response()->json([
                'success' => true,
                'review' => self::toDashboardArray($review),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error registrando calificación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $this->errorMessage($e)
            ], 500);
        }
    }

    /**
     * Toggle whether a review is shown on the public website.
     */
    public function toggleVisibility($id)
    {
        try {
            $review = Review::findOrFail($id);
            $review->is_public = !$review->is_public;
            $review->save();

            return response()->json([
                'success' => true,
                'is_public' => $review->is_public,
            ]);

        } catch (\Exception $e) {
            Log::error('Error cambiando visibilidad de calificación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $this->errorMessage($e)
            ], 500);
        }
    }

    /**
     * Approved reviews for the public website (GET /api/reviews/public).
     */
    public function publicIndex()
    {
        $reviews = Review::with('instructorModel')
            ->where('is_public', true)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->where('comment', '!=', self::EMPTY_COMMENT)
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->map(function ($r) {
                return [
                    'author' => $r->author_name ?: null,
                    'rating' => (int) $r->rating,
                    'comment' => $r->comment,
                    // Only the first name is exposed publicly
                    'instructor' => $r->instructorModel ? $r->instructorModel->first_name : null,
                    'date' => optional($r->created_at)->toDateString(),
                ];
            });

        return response()->json(['data' => $reviews])
            ->header('Cache-Control', 'public, max-age=300');
    }

    /**
     * Shape used by the admin dashboard.
     */
    public static function toDashboardArray(Review $r): array
    {
        return [
            'id' => 'REV-' . str_pad($r->id, 3, '0', STR_PAD_LEFT),
            'db_id' => $r->id,
            'instructor' => $r->instructorModel ? ($r->instructorModel->first_name . ' ' . $r->instructorModel->last_name) : 'Instructor Eliminado',
            'instructor_id' => $r->instructor_id,
            'author_name' => $r->author_name,
            'rating' => (int) $r->rating,
            'vehicleRating' => (int) $r->vehicle_rating,
            'comment' => $r->comment,
            'is_public' => (bool) $r->is_public,
        ];
    }
}
