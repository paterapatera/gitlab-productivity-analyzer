import { BasePageProps } from './common';

export interface CommitProject {
    id: number;
    name_with_namespace: string;
}

export interface CommitPageProps extends BasePageProps {
    projects: CommitProject[];
}

export interface RecollectHistoryItem {
    project_id: number;
    project_name_with_namespace: string;
    branch_name: string;
    latest_committed_date: string; // ISO 8601 format (always present, not null)
}

export interface RecollectPageProps extends BasePageProps {
    histories: RecollectHistoryItem[]; // 収集履歴テーブルに存在するレコードのみ
}

/**
 * 集計画面のリクエストパラメータ
 */
export interface AggregationShowRequest {
    project_id?: number;
    branch_name?: string;
    year?: number;
}

/**
 * 集計データの1件
 */
export interface AggregationItem {
    project_id: number;
    branch_name: string;
    author_email: string;
    author_name: string | null;
    year: number;
    month: number;
    total_additions: number;
    total_deletions: number;
    commit_count: number;
}

/**
 * 集計画面のレスポンス
 */
export interface AggregationShowResponse {
    projects: CommitProject[];
    branches: Array<{
        project_id: number;
        branch_name: string;
    }>;
    years: number[];
    aggregations: AggregationItem[];
}

/**
 * グラフ用データの1件
 */
export interface ChartDataItem {
    month: string;
    [key: string]: string | number;
}

/**
 * 表用データの1件
 */
export interface TableDataItem {
    userKey: string;
    userName: string;
    months: Record<number, number>;
}

/**
 * 集計画面のページプロップス
 */
export interface AggregationPageProps extends BasePageProps {
    projects: CommitProject[];
    branches: Array<{
        project_id: number;
        branch_name: string;
    }>;
    years: number[];
    aggregations: AggregationItem[];
    chartData: ChartDataItem[];
    tableData: TableDataItem[];
    userNames: string[];
    selectedProjectId: number | null;
    selectedBranchName: string | null;
    selectedYear: number | null;
    selectedBranch: {
        project_id: number;
        branch_name: string;
    } | null;
}
