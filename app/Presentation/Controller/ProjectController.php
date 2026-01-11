<?php

namespace App\Presentation\Controller;

use App\Application\Contract\SyncProjects;
use App\Application\Port\ProjectRepository;
use App\Presentation\Request\Project\ListRequest;
use App\Presentation\Response\Project\ListResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends BaseController
{
    public function __construct(
        private readonly ProjectRepository $repository,
        private readonly SyncProjects $syncProjects
    ) {}

    /**
     * プロジェクト一覧を表示
     */
    public function index(Request $httpRequest): Response
    {
        return $this->renderWithErrorHandling(function () use ($httpRequest) {
            $request = new ListRequest($httpRequest);
            $projects = $this->repository->findAll();
            $response = new ListResponse($projects);

            $props = $this->addFlashMessages($response->toArray(), $httpRequest);

            return Inertia::render('Project/Index', $props);
        }, 'プロジェクト一覧の取得に失敗しました。');
    }

    /**
     * プロジェクト情報を同期
     */
    public function sync(): RedirectResponse
    {
        try {
            $result = $this->syncProjects->execute();

            if ($result->hasErrors) {
                return redirect()->route('projects.index')
                    ->with('error', $result->errorMessage ?? '同期処理中にエラーが発生しました。');
            }

            return redirect()->route('projects.index')
                ->with('success', "同期が完了しました。同期: {$result->syncedCount}件、削除: {$result->deletedCount}件");
        } catch (\Exception $e) {
            abort(500, '同期処理に失敗しました。');
        }
    }
}
