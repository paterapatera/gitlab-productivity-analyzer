/**
 * テスト用のモックデータを作成するヘルパー関数
 */

export interface MockCommitProject {
    id: number;
    name_with_namespace: string;
}

export interface MockProject {
    id: number;
    name_with_namespace: string;
    description: string | null;
    default_branch: string | null;
}

/**
 * コミット収集ページ用のプロジェクトデータを作成
 */
export function createMockCommitProject(
    overrides: Partial<MockCommitProject> = {},
): MockCommitProject {
    return {
        id: 1,
        name_with_namespace: 'group/project1',
        ...overrides,
    };
}

/**
 * プロジェクト一覧ページ用のプロジェクトデータを作成
 */
export function createMockProject(
    overrides: Partial<MockProject> = {},
): MockProject {
    return {
        id: 1,
        name_with_namespace: 'group/project1',
        description: 'Description 1',
        default_branch: 'main',
        ...overrides,
    };
}
