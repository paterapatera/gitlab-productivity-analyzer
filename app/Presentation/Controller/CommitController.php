<?php

namespace App\Presentation\Controller;

use App\Application\Contract\CollectCommits;
use App\Application\Port\CommitCollectionHistoryRepository;
use App\Application\Port\ProjectRepository;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use App\Presentation\Request\Commit\CollectRequest;
use App\Presentation\Request\Commit\RecollectRequest;
use App\Presentation\Request\Commit\RecollectShowRequest;
use App\Presentation\Response\Commit\CollectShowResponse;
use App\Presentation\Response\Commit\RecollectResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class CommitController extends BaseController
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly CollectCommits $collectCommits,
        private readonly CommitCollectionHistoryRepository $commitCollectionHistoryRepository
    ) {}

    /**
     * コミット収集ページを表示
     */
    public function collectShow(Request $httpRequest): Response
    {
        return $this->renderWithErrorHandling(function () use ($httpRequest) {
            $projects = $this->projectRepository->findAll();
            $response = new CollectShowResponse($projects, null);

            $props = $this->addFlashMessages($response->toArray(), $httpRequest);

            return Inertia::render('Commit/Index', $props);
        }, 'コミット収集ページの取得に失敗しました。');
    }

    /**
     * コミット収集を実行
     */
    public function collect(Request $httpRequest): RedirectResponse
    {
        return $this->redirectWithErrorHandling(function () use ($httpRequest) {
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
        }, 'コミット収集処理に失敗しました。');
    }

    /**
     * 再収集ページを表示
     */
    public function recollectShow(Request $httpRequest): Response
    {
        return $this->renderWithErrorHandling(function () use ($httpRequest) {
            $recollectShowRequest = new RecollectShowRequest($httpRequest);

            $histories = $this->commitCollectionHistoryRepository->findAll();
            $projects = $this->projectRepository->findAll();
            $response = new RecollectResponse($histories, $projects);

            $props = $this->addFlashMessages($response->toArray(), $httpRequest);

            return Inertia::render('Commit/Recollect', $props);
        }, '再収集ページの取得に失敗しました。');
    }

    /**
     * 再収集を実行
     */
    public function recollect(Request $httpRequest): RedirectResponse
    {
        return $this->redirectWithErrorHandling(function () use ($httpRequest) {
            $recollectRequest = new RecollectRequest($httpRequest);

            // バリデーション
            $validator = Validator::make($httpRequest->all(), $recollectRequest->rules());
            if ($validator->fails()) {
                return redirect()->route('commits.recollect')
                    ->withErrors($validator)
                    ->withInput();
            }

            // 値オブジェクトを作成
            $projectId = new ProjectId($recollectRequest->getProjectId());
            $branchName = new BranchName($recollectRequest->getBranchName());

            // 再収集を実行（sinceDateは省略して自動判定）
            $result = $this->collectCommits->execute($projectId, $branchName);

            if ($result->hasErrors) {
                return redirect()->route('commits.recollect')
                    ->with('error', $result->errorMessage ?? '再収集処理中にエラーが発生しました。');
            }

            return redirect()->route('commits.recollect')
                ->with('success', "再収集が完了しました。収集: {$result->collectedCount}件、保存: {$result->savedCount}件");
        }, '再収集処理に失敗しました。');
    }
}
