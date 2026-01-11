<?php

namespace App\Presentation\Controller;

use App\Application\Port\CommitUserMonthlyAggregationRepository;
use App\Presentation\Request\Commit\UserProductivityShowRequest;
use App\Presentation\Response\Commit\UserProductivityShowResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class UserProductivityController extends BaseController
{
    public function __construct(
        private readonly CommitUserMonthlyAggregationRepository $aggregationRepository
    ) {}

    /**
     * ユーザー生産性画面を表示
     */
    public function show(Request $httpRequest): Response
    {
        return $this->renderWithErrorHandling(function () use ($httpRequest) {
            $userProductivityShowRequest = new UserProductivityShowRequest($httpRequest);

            // バリデーション
            $validator = Validator::make($httpRequest->all(), $userProductivityShowRequest->rules());
            if ($validator->fails()) {
                abort(400, 'リクエストパラメータが無効です。');
            }

            // ユーザー一覧を取得
            $users = $this->aggregationRepository->findAllUsers();

            // 年一覧を取得
            $years = $this->aggregationRepository->findAvailableYears();

            // リクエストパラメータを取得
            $selectedYear = $userProductivityShowRequest->getYear();
            $selectedUsers = $userProductivityShowRequest->getUsers();

            // ユーザーまたは年が指定されていない場合は空のコレクションを返す（メモリーオーバーフローを防ぐため）
            $aggregations = collect([]);
            if (count($selectedUsers) > 0 && $selectedYear !== null) {
                // 集計データを取得
                $aggregations = $this->aggregationRepository->findByUsersAndYear(
                    $selectedUsers,
                    $selectedYear
                );
            }

            // Response DTOを作成
            $response = new UserProductivityShowResponse(
                $users,
                $years,
                $aggregations,
                $selectedYear,
                $selectedUsers
            );

            $props = $this->addFlashMessages($response->toArray(), $httpRequest);

            return Inertia::render('Commit/UserProductivity', $props);
        }, 'ユーザー生産性画面の取得に失敗しました。');
    }
}
