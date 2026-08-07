<?php

namespace App\Services;

use App\Actions\Api\Dashboard\DestroyDashboardAction;
use App\Actions\Api\Dashboard\IndexDashboardAction;
use App\Actions\Api\Dashboard\ShowDashboardAction;
use App\Actions\Api\Dashboard\StoreDashboardAction;
use App\Actions\Api\Dashboard\UpdateDashboardAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardService
{
    public function __construct(
        private IndexDashboardAction $indexAction,
        private StoreDashboardAction $storeAction,
        private ShowDashboardAction $showAction,
        private UpdateDashboardAction $updateAction,
        private DestroyDashboardAction $destroyAction,
    ) {
    }

    public function index(Request $request): array
    {
        $response = $this->indexAction->handle($request);
        return $this->decodeResponse($response);
    }

    public function store(Request $request): array
    {
        $response = $this->storeAction->handle($request);
        return $this->decodeResponse($response);
    }

    public function show($id): array
    {
        $response = $this->showAction->handle($id);
        return $this->decodeResponse($response);
    }

    public function update($id, Request $request): array
    {
        $response = $this->updateAction->handle($id, $request);
        return $this->decodeResponse($response);
    }

    public function destroy($id): array
    {
        $response = $this->destroyAction->handle($id);
        return $this->decodeResponse($response);
    }

    private function decodeResponse(JsonResponse $response): array
    {
        return json_decode($response->getContent(), true) ?? [];
    }
}
