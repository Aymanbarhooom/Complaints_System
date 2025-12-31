<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\ComplaintHistory;

class CommentObserver
{
    /**
     * بعد إنشاء تعليق
     */
    public function created(Comment $comment)
    {
        ComplaintHistory::create([
            'complaint_id' => $comment->complaint_id,
            'user_id' => $comment->user_id,
            'action' => 'comment_added',
            'old_value' => null,
            'new_value' => $comment->content,
        ]);
    }

    /**
     * بعد تعديل تعليق
     */
    public function updated(Comment $comment)
    {
        ComplaintHistory::create([
            'complaint_id' => $comment->complaint_id,
            'user_id' => auth()->id(),
            'action' => 'comment_updated',
            'old_value' => $comment->getOriginal('content'),
            'new_value' => $comment->content,
        ]);
    }

    /**
     * بعد حذف تعليق
     */
    public function deleted(Comment $comment)
    {
        ComplaintHistory::create([
            'complaint_id' => $comment->complaint_id,
            'user_id' => auth()->id(),
            'action' => 'comment_deleted',
            'old_value' => $comment->content,
            'new_value' => null,
        ]);
    }
}