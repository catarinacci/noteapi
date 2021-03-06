<?php

namespace App\Http\Controllers;

use App\Helpers\UpdateStoreFiles;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CommentCollection;
use App\Http\Requests\Comments\GuardarComentarioRequest;
use App\Http\Requests\Comments\ActualizarComentarioRequest;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    public function index()
    {
        $comment = Comment::orderBy('updated_at', 'desc')->paginate(10);
        return new CommentCollection($comment);
    }

    public function commentUser(){

         $comments1 = Comment::where('user_id', Auth::user()->id)->get();
        // $comments1 = Auth::user()->comments;
        if (!$comments1->isEmpty()) {

            $comments = Comment::where('user_id', Auth::user()->id)->orderBy('updated_at', 'desc')->paginate(10);
            return new CommentCollection($comments);
        }
        return response()->json([
            'msg' => 'No tiene ningun comentario',
       ], 400);
    }

    public function commentNote($nota_id){

        $nota_exists = Comment::where('id', $nota_id)->exists();

        if($nota_exists){
            $comments1 = Comment::where('note_id', $nota_id )->get();

            if (!$comments1->isEmpty()) {

                $comments = Comment::where('note_id', $nota_id)->orderBy('updated_at', 'desc')->paginate(10);
                return new CommentCollection($comments);
            }
            return response()->json([
                'msg' => 'No tiene ningun comentario',
           ], 400);
        }
        return response()->json([
            'msg' => 'La nota no existe',
       ], 400);
    }

    public function store(GuardarComentarioRequest $request)
    {
            $coment = Comment::create([
                'content' => $request->content,
                'user_id' => Auth::user()->id,
                'note_id' => $request->note_id
            ]);

            return new CommentResource($coment);
    }

    public function show($comment_id)
    {
        $comment_exists = Comment::where('id', $comment_id)->exists();

        if ($comment_exists) {
            $comment = Comment::find($comment_id);
            return new CommentResource($comment) ;
        }
        return response()->json([
            'msj' => 'El comentario '.$comment_id.' no existe '
        ],400);

    }


    public function update(ActualizarComentarioRequest $request, $comment_id)
    {
        $comment = UpdateStoreFiles::UpdateComment($request, $comment_id);
        return $comment;

    }


    public function destroy(Request $request, Comment $comment)
    {

        if(Auth::user()->id == $comment->user_id){
            $comment->delete();

            return response()->json([
                'res' => 'El comentario se elimin?? correctamente'
            ], 200);
        }else{
            return response()->json([
                'res' => 'Usted no es el autor de ??ste comentario, no lo puede borrar'
            ], 200);
        }

    }
}
