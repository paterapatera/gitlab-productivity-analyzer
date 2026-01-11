<?php

namespace App\Presentation\Response;

use App\Domain\Project;
use Illuminate\Support\Collection;

/**
 * プロジェクトを配列に変換する機能を提供するトレイト
 *
 * このトレイトは、Responseクラスでプロジェクトのコレクションを
 * Inertia.js用の配列に変換する共通ロジックを提供します。
 */
trait ConvertsProjectsToArray
{
    /**
     * プロジェクトのコレクションを配列に変換
     *
     * @param  Collection<int, Project>  $projects  プロジェクトのコレクション
     * @param  array<string>  $fields  含めるフィールド（デフォルト: すべて）
     * @return array<int, array<string, mixed>> 変換された配列
     */
    protected function projectsToArray(Collection $projects, array $fields = []): array
    {
        $defaultFields = ['id', 'name_with_namespace', 'description', 'default_branch'];
        $fieldsToInclude = empty($fields) ? $defaultFields : $fields;

        return $projects->map(function (Project $project) use ($fieldsToInclude) {
            $data = [];

            if (in_array('id', $fieldsToInclude)) {
                $data['id'] = $project->id->value;
            }
            if (in_array('name_with_namespace', $fieldsToInclude)) {
                $data['name_with_namespace'] = $project->nameWithNamespace->value;
            }
            if (in_array('description', $fieldsToInclude)) {
                $data['description'] = $project->description->value;
            }
            if (in_array('default_branch', $fieldsToInclude)) {
                $data['default_branch'] = $project->defaultBranch->value;
            }

            return $data;
        })->toArray();
    }
}
