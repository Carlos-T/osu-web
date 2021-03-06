<?php

/**
 *    Copyright (c) ppy Pty Ltd <contact@ppy.sh>.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers;

use App\Exceptions\ModelNotSavedException;
use App\Exceptions\ValidationException;
use App\Libraries\CommentBundle;
use App\Libraries\MorphMap;
use App\Models\Comment;
use App\Models\Log;
use App\Models\Notification;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentsController extends Controller
{
    protected $section = 'community';
    protected $actionPrefix = 'comments-';

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        priv_check('CommentDestroy', $comment)->ensureCan();

        $comment->softDelete(auth()->user());

        if ($comment->user_id !== auth()->user()->getKey()) {
            $this->logModerate('LOG_COMMENT_DELETE', $comment);
        }

        return json_item($comment, 'Comment', ['editor', 'user', 'commentable_meta']);
    }

    public function index()
    {
        $type = request('commentable_type');
        $id = request('commentable_id');

        if (isset($type) && isset($id)) {
            if (!Comment::isValidType($type)) {
                abort(422);
            }

            $class = MorphMap::getClass($type);
            $commentable = $class::findOrFail($id);
        }

        $commentBundle = new CommentBundle(
            $commentable ?? null,
            ['params' => request()->all()]
        );

        if (request()->expectsJson()) {
            return $commentBundle->toArray();
        } else {
            $commentBundle->depth = 0;
            $commentBundle->includeCommentableMeta = true;
            $commentBundle->includeParent = true;

            $commentPagination = new LengthAwarePaginator(
                [],
                Comment::count(),
                $commentBundle->params->limit,
                $commentBundle->params->page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => $commentBundle->params->forUrl(),
                ]
            );

            return view('comments.index', compact('commentBundle', 'commentPagination'));
        }
    }

    public function report($id)
    {
        $comment = Comment::findOrFail($id);

        try {
            $comment->reportBy(auth()->user(), [
                'comments' => trim(request('comments')),
            ]);
        } catch (ValidationException $e) {
            return error_popup($e->getMessage());
        }

        return response(null, 204);
    }

    public function restore($id)
    {
        $comment = Comment::findOrFail($id);

        priv_check('CommentRestore', $comment)->ensureCan();

        $comment->restore();

        $this->logModerate('LOG_COMMENT_RESTORE', $comment);

        return json_item($comment, 'Comment', ['editor', 'user', 'commentable_meta']);
    }

    public function show($id)
    {
        $comment = Comment::findOrFail($id);

        $commentBundle = new CommentBundle($comment->commentable, [
            'params' => ['parent_id' => $comment->getKey()],
            'additionalComments' => [$comment],
            'includeCommentableMeta' => true,
        ]);

        $commentJson = json_item($comment, 'Comment', [
            'editor', 'user', 'commentable_meta', 'parent.user',
        ]);

        return view('comments.show', compact('commentJson', 'commentBundle'));
    }

    public function store()
    {
        $user = auth()->user();

        $params = get_params(request(), 'comment', [
            'commentable_id:int',
            'commentable_type',
            'message',
            'parent_id:int',
        ]);
        $params['user_id'] = optional($user)->getKey();

        $comment = new Comment($params);

        priv_check('CommentStore', $comment)->ensureCan();

        try {
            $comment->saveOrExplode();
        } catch (ModelNotSavedException $e) {
            return error_popup($e->getMessage());
        }

        broadcast_notification(Notification::COMMENT_NEW, $comment, $user);

        $comments = collect([$comment]);

        if ($comment->parent !== null) {
            $comments[] = $comment->parent;
        }

        $bundle = new CommentBundle($comment->commentable, [
            'comments' => $comments,
            'includeCommentableMeta' => true,
        ]);

        return $bundle->toArray();
    }

    public function update($id)
    {
        $comment = Comment::findOrFail($id);

        priv_check('CommentUpdate', $comment)->ensureCan();

        $params = get_params(request(), 'comment', ['message']);
        $params['edited_by_id'] = auth()->user()->getKey();
        $params['edited_at'] = Carbon::now();
        $comment->update($params);

        if ($comment->user_id !== auth()->user()->getKey()) {
            $this->logModerate('LOG_COMMENT_UPDATE', $comment);
        }

        return json_item($comment, 'Comment', ['editor', 'user', 'commentable_meta']);
    }

    public function voteDestroy($id)
    {
        $comment = Comment::findOrFail($id);

        priv_check('CommentVote', $comment)->ensureCan();

        $vote = $comment->votes()->where([
            'user_id' => auth()->user()->getKey(),
        ])->first();

        optional($vote)->delete();

        return json_item($comment->fresh(), 'Comment', ['editor', 'user', 'commentable_meta']);
    }

    public function voteStore($id)
    {
        $comment = Comment::findOrFail($id);

        priv_check('CommentVote', $comment)->ensureCan();

        try {
            $comment->votes()->create([
                'user_id' => auth()->user()->getKey(),
            ]);
        } catch (Exception $e) {
            if (!is_sql_unique_exception($e)) {
                throw $e;
            }
        }

        return json_item($comment->fresh(), 'Comment', ['editor', 'user', 'commentable_meta']);
    }

    private function logModerate($operation, $comment)
    {
        $this->log([
            'log_type' => Log::LOG_COMMENT_MOD,
            'log_operation' => $operation,
            'log_data' => [
                'commentable_type' => $comment->commentable_type,
                'commentable_id' => $comment->commentable_id,
                'id' => $comment->getKey(),
            ],
        ]);
    }
}
