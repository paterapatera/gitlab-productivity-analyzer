<?php

namespace App\Infrastructure\GitLab;

use App\Domain\Commit;
use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitMessage;
use App\Domain\ValueObjects\CommitSha;
use App\Domain\ValueObjects\CommittedDate;
use App\Domain\ValueObjects\Deletions;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Support\Collection;

trait FetchesCommits
{
    use HandlesGitLabApiRequests;

    /**
     * GitLab APIからコミットを取得
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @param  \DateTime|null  $sinceDate  開始日（オプショナル）
     * @return Collection<int, Commit>
     *
     * @throws GitLabApiException
     */
    protected function fetchCommits(ProjectId $projectId, BranchName $branchName, ?\DateTime $sinceDate = null): Collection
    {
        $allCommits = collect();
        $page = 1;

        do {
            $response = $this->fetchCommitsPage($projectId, $branchName, $page, $sinceDate);

            if ($response->successful()) {
                /** @var array<int, array<string, mixed>> $commitDataArray */
                $commitDataArray = $response->json();
                $commits = collect($commitDataArray)
                    ->map(fn (array $commitData) => $this->convertToCommit($commitData, $projectId, $branchName));
                $allCommits = $allCommits->concat($commits);

                $nextPage = $response->header('X-Next-Page');
                if (empty($nextPage)) {
                    break;
                }
                $page = (int) $nextPage;
            } elseif ($this->handleRateLimit($response, $page)) {
                // レート制限エラー: 同じページを再試行
                continue;
            } else {
                $this->checkApiError($response);
            }
        } while (true);

        return $allCommits;
    }

    /**
     * 指定されたページのコミットを取得
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @param  int  $page  ページ番号
     * @param  \DateTime|null  $sinceDate  開始日（オプショナル）
     *
     * @throws GitLabApiException
     */
    protected function fetchCommitsPage(ProjectId $projectId, BranchName $branchName, int $page, ?\DateTime $sinceDate = null): \Illuminate\Http\Client\Response
    {
        $params = [
            'ref_name' => $branchName->value,
            'with_stats' => 'true',
            'page' => $page,
            'per_page' => 100,
        ];

        if ($sinceDate !== null) {
            $params['since'] = $sinceDate->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
        }

        return $this->makeGitLabRequest('get', "/api/v4/projects/{$projectId->value}/repository/commits", $params);
    }

    /**
     * APIレスポンスをCommitエンティティに変換
     *
     * @param  array<string, mixed>  $commitData
     */
    protected function convertToCommit(array $commitData, ProjectId $projectId, BranchName $branchName): Commit
    {
        $stats = $commitData['stats'] ?? null;
        $additions = $stats['additions'] ?? 0;
        $deletions = $stats['deletions'] ?? 0;

        return new Commit(
            projectId: $projectId,
            branchName: $branchName,
            sha: new CommitSha($commitData['id']),
            message: new CommitMessage($commitData['message'] ?? ''),
            committedDate: new CommittedDate(new \DateTime($commitData['committed_date'])),
            authorName: new AuthorName($commitData['author_name'] ?? null),
            authorEmail: new AuthorEmail($commitData['author_email'] ?? null),
            additions: new Additions((int) $additions),
            deletions: new Deletions((int) $deletions)
        );
    }
}
