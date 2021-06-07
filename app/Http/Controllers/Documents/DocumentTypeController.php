<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Database\Seeders\DocumentTypeSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index (): JsonResponse
    {
        $documentTypes = DocumentType::all();
        return response()->json(['documentTypes' => $documentTypes]);
    }
}
