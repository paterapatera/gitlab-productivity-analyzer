<?php

namespace App\Infrastructure\GitLab;

use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * GitLab APIリクエストの共通処理を提供するトレイト
 *
 * このトレイトは、HTTPリクエストの作成、エラーハンドリング、レート制限処理などの
 * 共通ロジックを提供します。
 */
trait HandlesGitLabApiRequests
{
    /**
     * GitLab APIへのHTTPリクエストを実行
     *
     * @param  string  $method  HTTPメソッド（get, post, put, deleteなど）
     * @param  string  $url  リクエストURL
     * @param  array<string, mixed>  $params  クエリパラメータまたはリクエストボディ
     * @return Response HTTPレスポンス
     *
     * @throws GitLabApiException
     */
    protected function makeGitLabRequest(string $method, string $url, array $params = []): Response
    {
        $baseUrl = $this->getGitLabBaseUrl();
        $token = $this->getGitLabToken();

        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'PRIVATE-TOKEN' => $token,
            ])->{$method}("{$baseUrl}{$url}", $params);

            $this->checkAuthenticationError($response);

            return $response;
        } catch (ConnectionException $e) {
            throw new GitLabApiException(
                "GitLab API connection error: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * レート制限エラーを処理し、必要に応じてリトライする
     *
     * @param  Response  $response  HTTPレスポンス
     * @param  int  $currentPage  現在のページ番号（リトライ回数の計算に使用）
     * @return bool リトライが必要な場合 true、そうでない場合 false
     */
    protected function handleRateLimit(Response $response, int $currentPage = 1): bool
    {
        if ($response->status() !== 429) {
            return false;
        }

        // レート制限エラー: 指数バックオフでリトライ
        $retryAfter = (int) $response->header('Retry-After') ?: 1;
        $delay = (int) min($retryAfter * (2 ** ($currentPage - 1)), 60); // 最大60秒
        sleep($delay);

        return true;
    }

    /**
     * 認証エラーをチェック
     *
     * @param  Response  $response  HTTPレスポンス
     *
     * @throws GitLabApiException 認証エラーの場合
     */
    protected function checkAuthenticationError(Response $response): void
    {
        if ($response->status() === 401) {
            throw new GitLabApiException('GitLab API authentication failed');
        }
    }

    /**
     * APIエラーをチェックし、必要に応じて例外をスロー
     *
     * @param  Response  $response  HTTPレスポンス
     * @param  string|null  $customMessage  カスタムエラーメッセージ
     *
     * @throws GitLabApiException APIエラーの場合
     */
    protected function checkApiError(Response $response, ?string $customMessage = null): void
    {
        if (! $response->successful()) {
            $message = $customMessage ?? "GitLab API error: {$response->status()} - {$response->body()}";
            throw new GitLabApiException($message);
        }
    }

    /**
     * GitLab APIのベースURLを取得
     */
    abstract protected function getGitLabBaseUrl(): string;

    /**
     * GitLab APIの認証トークンを取得
     */
    abstract protected function getGitLabToken(): string;
}
