<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        return DocumentResource::collection(
            request()->user()
                ->documents()
                ->orderBy('expires_at', 'asc')
                ->get()
        );
    }

    public function store(Request $request)
    {
       $validated = $request->validate([
            'document'   => ['required', 'file'],
            'expires_at' => ['required', 'date'],
        ]);

        $user = $request->user();
        $file = $validated['document'];

        $document = Document::create([
            'name'       => $file->getClientOriginalName(),
            'path'       => $file->store("documents"),
            'owner_id'   => $user->id,
            'expires_at' => $validated['expires_at'],
        ]);

        return new DocumentResource($document);
    }

    public function show(Document $document)
    {
        if ($document->owner_id !== request()->user()->id) {
            abort(403, "Not found");
        }

        return DocumentResource::make($document);
    }

    public function archive(Document $document)
    {
        if ($document->owner_id !== request()->user()->id) {
            abort(403, "Not found");
        }

        if ($document->archived_at) {
            abort(409, "Document is already archived");
        }

        $document->archived_at = now();
        $document->save();

        return DocumentResource::make($document);
    }
}
