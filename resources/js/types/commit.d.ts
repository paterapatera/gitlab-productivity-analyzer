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
