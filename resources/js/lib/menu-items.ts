import {
    aggregation,
    collect,
    recollect,
    userProductivity,
} from '@/routes/commits';
import { index as projectsIndex } from '@/routes/projects';
import type { MenuItem } from '@/types/navigation';

/**
 * メニュー項目の配列
 * 順序: プロジェクト同期 → コミット収集 → コミット再収集 → コミット集計 → ユーザー生産性
 */
export const menuItems: MenuItem[] = [
    {
        label: 'プロジェクト同期',
        route: projectsIndex,
        path: '/projects',
    },
    {
        label: 'コミット収集',
        route: collect,
        path: '/commits/collect',
    },
    {
        label: 'コミット再収集',
        route: recollect,
        path: '/commits/recollect',
    },
    {
        label: 'コミット集計',
        route: aggregation,
        path: '/commits/aggregation',
    },
    {
        label: 'ユーザー生産性',
        route: userProductivity,
        path: '/commits/user-productivity',
    },
];
