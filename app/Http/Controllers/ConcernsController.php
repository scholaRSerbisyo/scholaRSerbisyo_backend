<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Reply;
use App\Models\Event;
use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\ReplyStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConcernsController extends Controller
{
    /**
     * Display comments for a specific event with pagination.
     */
    public function getComments(Request $request, $eventId)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        try {
            $comments = Comment::where('event_id', $eventId)
                ->with(['scholar:scholar_id,firstname,lastname', 'replies.scholar:scholar_id,firstname,lastname'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'comments' => $comments->items(),
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'last_page' => $comments->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch comments: ' . $e->getMessage()], 500);
        }
    }

    public function storeComment(CommentStoreRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $comment = Comment::create($validatedData);
            $comment->load('scholar:scholar_id,firstname,lastname');
            return response()->json($comment, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store comment: ' . $e->getMessage()], 500);
        }
    }

    public function storeReply(ReplyStoreRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $reply = Reply::create($validatedData);
            $reply->load('scholar:scholar_id,firstname,lastname');
            return response()->json($reply, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store reply: ' . $e->getMessage()], 500);
        }
    }

    public function getReplies(Request $request, $commentId)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        try {
            $replies = Reply::where('comment_id', $commentId)
                ->with('scholar:scholar_id,firstname,lastname')
                ->orderBy('created_at', 'asc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'replies' => $replies->items(),
                'current_page' => $replies->currentPage(),
                'per_page' => $replies->perPage(),
                'total' => $replies->total(),
                'last_page' => $replies->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch replies: ' . $e->getMessage()], 500);
        }
    }

    public function deleteComment($commentId)
    {
        try {
            $comment = Comment::findOrFail($commentId);
            $comment->replies()->delete();
            $comment->delete();
            return response()->json(['message' => 'Comment and associated replies deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Comment not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete comment: ' . $e->getMessage()], 500);
        }
    }

    public function deleteReply($replyId)
    {
        try {
            $reply = Reply::findOrFail($replyId);
            $reply->delete();
            return response()->json(['message' => 'Reply deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Reply not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete reply: ' . $e->getMessage()], 500);
        }
    }
}

