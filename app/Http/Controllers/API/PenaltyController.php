<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\PenaltyService;
use App\Exceptions\ErrorsException;
use Auth;

class PenaltyController extends Controller
{
    protected $penaltyService;

    public function __construct(PenaltyService $penaltyService)
    {
        $this->penaltyService = $penaltyService;
    }

    public function getByUser($userId)
    {
        try {
            $penalties = $this->penaltyService->getPenaltiesByUser($userId);
            return response()->json(['success' => true, 'data' => $penalties], 200);
        } catch (ErrorsException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getUserPenalties()
    {
        try {
            $userId = Auth::id();
            $penalties = $this->penaltyService->getPenaltiesByUser($userId);
            return response()->json(['success' => true, 'data' => $penalties], 200);
        } catch (ErrorsException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->only(keys: ['amount', 'reason',]);

        try {
            $penalty = $this->penaltyService->updatePenalty($id, $data);
            return response()->json(['success' => true, 'data' => $penalty, 'message' => 'Penalty updated successfully'], 200);
        } catch (ErrorsException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $this->penaltyService->deletePenalty($id);
            return response()->json(['success' => true, 'message' => 'Penalty deleted successfully'], 200);
        } catch (ErrorsException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAll()
    {
        try {
            $penalties = $this->penaltyService->getAllPenalties();
            return response()->json(['success' => true, 'data' => $penalties], 200);
        } catch (ErrorsException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
