<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExtractPrefectureRequest;
use App\UseCases\ExtractPrefectureUseCase;
use Illuminate\Http\JsonResponse;
use Exception;

class PrefectureController extends Controller
{
    private ExtractPrefectureUseCase $extractPrefectureUseCase;

    public function __construct(ExtractPrefectureUseCase $extractPrefectureUseCase)
    {
        $this->extractPrefectureUseCase = $extractPrefectureUseCase;
    }

    public function extract(ExtractPrefectureRequest $request): JsonResponse
    {
        try {
            $address = $request->input('address');
            $prefecture = $this->extractPrefectureUseCase->execute($address);

            return response()->json(['prefecture' => $prefecture]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}