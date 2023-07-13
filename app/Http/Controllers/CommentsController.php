<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Symfony\Component\HttpFoundation\JsonResponse;


class CommentsController extends Controller
{
    public function getCommentsByOfficialDocument($id)
    {
        $comments = Comment::where('official_document', $id)->get();

        return new JsonResponse([
            'status' => 'success',
            'data' => ['comments' => $comments]
        ], 200);
    }


    public function createComment(Request $request, $official_document_id)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        try {
            $comment = Comment::create(array_merge(
                $request->all(),
                [
                    'created_by' => auth()->user()->id,
                    'official_document' => $official_document_id
                ]
            ));

            $comment->load(['created_by.person']);


            return response()->json([
                'status' => 'success',
                'message' => 'Comentario agregado con éxito',
                'data' => [
                    'comment' => $comment
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el comentario: ' . $e->getMessage()
            ]);
        }
    }
}
