import { ChartDataItem, TableDataItem } from './commit';
import { BasePageProps } from './common';

/**
 * ユーザー情報
 */
export interface UserInfo {
    author_email: string;
    author_name: string | null;
}

/**
 * ユーザー生産性画面のページプロップス
 */
export interface UserProductivityPageProps extends BasePageProps {
    users: UserInfo[];
    years: number[];
    chartData: ChartDataItem[];
    tableData: TableDataItem[];
    userNames: string[];
    selectedYear?: number;
    selectedUsers?: string[];
}
