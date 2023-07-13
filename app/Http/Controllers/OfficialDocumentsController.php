<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OfficialDocument;
use App\Models\File;
use App\Models\User;
use App\Models\Comment;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OfficialDocumentsController extends Controller
{
    function getOfficialDocuments()
    {
        $officialDocuments = OfficialDocument::where('archived', false)
            ->get();

        $officialDocuments->load(['comments', 'files', 'created_by', 'created_by.person']);

        $totalDocuments = count($officialDocuments);

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'official_documents' => $officialDocuments,
                'total' => $totalDocuments
            ]
        ], 200);
    }

    public function getArchivedOfficialDocuments()
    {
        $officialDocuments = OfficialDocument::where('archived', true)
            ->get();

        $officialDocuments->load('comments', 'files', 'created_by', 'created_by.person', 'archived_by.person');

        $totalDocuments = count($officialDocuments);

        return response()->json([
            'status' => 'success',
            'data' => [
                'officialDocuments' => $officialDocuments,
                'total' => $totalDocuments
            ]
        ]);
    }

    public function searchArchivedOfficialDocumentsByTerm($term = '')
    {
        $officialDocuments = OfficialDocument::where('subject', 'like', '%' . $term . '%')->where('archived', true)->get();

        $officialDocuments->load(['comments', 'files', 'created_by', 'created_by.person', 'archived_by.person']);

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'officialDocuments' => $officialDocuments,
            ],
        ], 200);
    }

    public function restoreOfficialDocument($id)
    {
        $officialDocuments = OfficialDocument::findOrFail($id);

        $officialDocuments->update([
            'archived' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Oficio restaurada correctamente',
            'data' => [
                'officialDocuments' => $officialDocuments,
            ],
        ], 200);
    }


    public function getOfficialDocumentById($id)
    {
        $officialDocument = OfficialDocument::where('id', $id)->first();

        $officialDocument->load(['comments', 'files', 'created_by.person', 'comments.created_by.person']);

        return new JsonResponse([
            'status' => 'success',
            'data' => ['official_document' => $officialDocument]
        ], 200);
    }

    public function searchOfficialDocumentsByTerm($term = '')
    {
        $officialDocuments = OfficialDocument::where('subject', 'like', '%' . $term . '%')->where('archived', false)->get();

        $officialDocuments->load(['comments', 'files', 'created_by', 'created_by.person']);

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'official_documents' => $officialDocuments,
            ],
        ], 200);
    }

    public function filterOfficialDocumentsByStatus($status = '')
    {
        if ($status === 'recibido') {
            return $this->getReceivedOfficialDocuments();
        }

        if ($status === 'creado') {
            return $this->getSentOfficialDocuments();
        }

        $officialDocuments = OfficialDocument::where('status', $status)
            ->whereHas('recipients', function ($query) {
                $query->where('users.id', Auth::user()->id);
            })
            ->get();

        $officialDocuments->load(['comments', 'files', 'created_by', 'created_by.person']);

        $totalDocuments = count($officialDocuments);

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'official_documents' => $officialDocuments,
                'total' => $totalDocuments
            ],
        ], 200);
    }

    public function getReceivedOfficialDocuments()
    {
        $userId = Auth::user()->id;

        $officialDocuments = OfficialDocument::whereHas('recipients', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->get();

        $officialDocuments->load(['comments', 'files', 'created_by', 'created_by.person']);

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'official_documents' => $officialDocuments,
            ],
        ], 200);
    }

    public function getSentOfficialDocuments()
    {
        $userId = Auth::user()->id;

        $officialDocuments = OfficialDocument::where('created_by', $userId)
            ->get();

        $officialDocuments->load(['comments', 'files', 'created_by', 'created_by.person']);

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'official_documents' => $officialDocuments,
            ],
        ], 200);
    }

    public function createOfficialDocument(Request $request)
    {
        $request->validate([
            'subject' => 'required|string',
            'description' => 'required|string',
        ]);

        try {
            // Crear registro del documento oficial
            $officialDocument = OfficialDocument::create(array_merge(
                $request->except('files', 'comments'),
                ['created_by' => auth()->user()->id]
            ));

            // Guardar comentario
            if ($request->comment) {
                Comment::create([
                    'comment' => $request->comment,
                    'official_document' => $officialDocument->id,
                    'created_by' => auth()->user()->id
                ]);
            }


            return response()->json([
                'status' => 'success',
                'data' => [
                    'official_document' => $officialDocument
                ],
                'message' => 'Oficio creado con éxito'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el oficio: ' . $e->getMessage()
            ]);
        }
    }


    public function updateOfficialDocument(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        try {
            $officialDocument = OfficialDocument::findOrFail($id);
            $officialDocument->status = $request->status;
            $officialDocument->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Oficio actualizado con éxito',
                'data' => [
                    'official_document' => $officialDocument
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el oficio: ' . $e->getMessage()
            ], 500);
        }
    }



    public function archiveOfficialDocument($id)
    {
        $oficio = OfficialDocument::find($id);

        if (!$oficio) {
            return response()->json([
                'message' => 'Oficio no encontrado'
            ]);
        }

        $oficio->archived = true;
        $oficio->archived_at = now();
        $oficio->archived_by = auth()->user()->id;

        $oficio->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Oficio archivado correctamente'
        ]);
    }



    public function deleteOfficialDocument($id)
    {
        $oficio = OfficialDocument::find($id);

        if (!$oficio) {
            return response()->json([
                'message' => 'Oficio no encontrado'
            ]);
        }

        $oficio->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Oficio eliminado correctamente'
        ]);
    }

    public function shareOfficialDocument(Request $request, $oficioId)
    {
        $usuariosCompartir = $request->input('usuarios');

        $documentoOficial = OfficialDocument::findOrFail($oficioId);

        $usuarios = User::whereIn('id', $usuariosCompartir)->get();

        $documentoOficial->update(['status' => 'en proceso']);

        foreach ($usuarios as $usuario) {
            $existeRelacion = $documentoOficial->recipients()->where('users', $usuario->id)->exists();
            if (!$existeRelacion) {
                $documentoOficial->recipients()->attach($usuario);
            }
        }

        return response()->json(['message' => 'El oficio ha sido compartido exitosamente']);
    }
    public function listArchivedOfficialDocuments()
    {
        $archivedOficios = OfficialDocument::where('archived', true)->get();

        if ($archivedOficios->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron oficios archivados'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $archivedOficios
        ]);
    }
}
