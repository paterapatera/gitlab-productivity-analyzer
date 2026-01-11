<?php

namespace App\Presentation\Controller;

use App\Application\Contract\CollectCommits;
use App\Application\Port\ProjectRepository;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use App\Presentation\Request\Commit\CollectRequest;
use App\Presentation\Response\Commit\IndexResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class CommitController extends BaseController
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly CollectCommits $collectCommits
    ) {}

    /**
     * コミット収集ページを表示
     */
    public function index(Request $httpRequest): Response
    {
        return $this->renderWithErrorHandling(function () use ($httpRequest) {
            $projects = $this->projectRepository->findAll();
            $response = new IndexResponse($projects, null);

            $props = $this->addFlashMessages($response->toArray(), $httpRequest);

            return Inertia::render('Commit/Index', $props);
        }, 'コミット収集ページの取得に失敗しました。');
    }

    /**
     * コミット収集を実行
     */
    public function collect(Request $httpRequest): RedirectResponse
    {
        try {
            $collectRequest = new CollectRequest($httpRequest);

            // バリデーション
            $validator = Validator::make($httpRequest->all(), $collectRequest->rules());
            if ($validator->fails()) {
                return redirect()->route('commits.collect')
                    ->withErrors($validator)
                    ->withInput();
            }

            // 値オブジェクトを作成
            $projectId = new ProjectId($collectRequest->getProjectId());
            $branchName = new BranchName($collectRequest->getBranchName());
            $sinceDate = $collectRequest->getSinceDate();

            // コミット収集を実行
            $result = $this->collectCommits->execute($projectId, $branchName, $sinceDate);

            if ($result->hasErrors) {
                return redirect()->route('commits.collect')
                    ->with('error', $result->errorMessage ?? 'コミット収集処理中にエラーが発生しました。');
            }

            return redirect()->route('commits.collect')
                ->with('success', "コミット収集が完了しました。収集: {$result->collectedCount}件、保存: {$result->savedCount}件");
        } catch (\Exception $e) {
            abort(500, 'コミット収集処理に失敗しました。');
        }
    }
}
