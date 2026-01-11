import type { Meta, StoryObj } from '@storybook/react-vite';
import Aggregation from '@/pages/Commit/Aggregation';
import {
    mockAggregationItem,
    mockAggregationItems,
    mockBranches,
    mockChartData,
    mockCommitProjects,
    mockTableData,
    mockUserNames,
    mockYears,
} from '../mocks/data';
import { withDefaultProcessing } from '../mocks/decorators';

const meta = {
    title: 'Pages/Commit/Aggregation',
    component: Aggregation,
    parameters: {
        layout: 'fullscreen',
    },
    tags: ['autodocs'],
    argTypes: {
        projects: {
            control: 'object',
            description: 'プロジェクト一覧',
        },
        branches: {
            control: 'object',
            description: 'ブランチ一覧',
        },
        years: {
            control: 'object',
            description: '年一覧',
        },
        aggregations: {
            control: 'object',
            description: '集計データ',
        },
        chartData: {
            control: 'object',
            description: 'グラフ用データ',
        },
        tableData: {
            control: 'object',
            description: '表用データ',
        },
        userNames: {
            control: 'object',
            description: 'ユーザー名リスト',
        },
        error: {
            control: 'text',
            description: 'エラーメッセージ',
        },
        success: {
            control: 'text',
            description: '成功メッセージ',
        },
    },
} satisfies Meta<typeof Aggregation>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        projects: [...mockCommitProjects],
        branches: [...mockBranches],
        years: [...mockYears],
        aggregations: [...mockAggregationItems],
        chartData: [...mockChartData],
        tableData: [...mockTableData],
        userNames: [...mockUserNames],
        selectedProjectId: null,
        selectedBranchName: null,
        selectedYear: null,
        selectedBranch: null,
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    '正常状態の集計画面を表示します。プロジェクト・ブランチと年のセレクトボックス、棒グラフ、表が表示されます。',
            },
        },
    },
};

export const Empty: Story = {
    args: {
        projects: [...mockCommitProjects],
        branches: [...mockBranches],
        years: [],
        aggregations: [],
        chartData: [],
        tableData: [],
        userNames: [],
        selectedProjectId: null,
        selectedBranchName: null,
        selectedYear: null,
        selectedBranch: null,
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    '集計データが存在しない場合の空状態を表示します。「集計データが存在しません」のメッセージが表示されます。',
            },
        },
    },
};

export const SingleUser: Story = {
    args: {
        projects: [...mockCommitProjects],
        branches: [...mockBranches],
        years: [2024],
        aggregations: [
            {
                ...mockAggregationItem,
                author_email: 'user1@example.com',
                author_name: 'User One',
            },
            {
                ...mockAggregationItem,
                month: 2,
                total_additions: 150,
                total_deletions: 75,
                commit_count: 8,
            },
        ],
        chartData: [
            {
                month: '1月',
                'User One_additions': 100,
                'User One_deletions': 50,
            },
            {
                month: '2月',
                'User One_additions': 150,
                'User One_deletions': 75,
            },
            ...Array.from({ length: 10 }, (_, i) => ({
                month: `${i + 3}月`,
                'User One_additions': 0,
                'User One_deletions': 0,
            })),
        ],
        tableData: [
            {
                userKey: '1-main-user1@example.com',
                userName: 'User One',
                months: {
                    1: 150,
                    2: 225,
                    3: 0,
                    4: 0,
                    5: 0,
                    6: 0,
                    7: 0,
                    8: 0,
                    9: 0,
                    10: 0,
                    11: 0,
                    12: 0,
                },
            },
        ],
        userNames: ['User One'],
        selectedProjectId: null,
        selectedBranchName: null,
        selectedYear: null,
        selectedBranch: null,
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    '単一ユーザーの集計データを表示します。グラフと表に1人のユーザーのデータのみが表示されます。',
            },
        },
    },
};

export const MultipleUsers: Story = {
    args: {
        projects: [...mockCommitProjects],
        branches: [...mockBranches],
        years: [2024],
        aggregations: [
            ...mockAggregationItems,
            {
                project_id: 1,
                branch_name: 'main',
                author_email: 'user3@example.com',
                author_name: 'User Three',
                year: 2024,
                month: 1,
                total_additions: 300,
                total_deletions: 150,
                commit_count: 15,
            },
            {
                project_id: 1,
                branch_name: 'main',
                author_email: 'user3@example.com',
                author_name: 'User Three',
                year: 2024,
                month: 2,
                total_additions: 250,
                total_deletions: 125,
                commit_count: 12,
            },
        ],
        chartData: mockChartData.map((item) => ({
            ...item,
            'User Three_additions': item.month === '1月' ? 300 : item.month === '2月' ? 250 : 0,
            'User Three_deletions': item.month === '1月' ? 150 : item.month === '2月' ? 125 : 0,
        })),
        tableData: [
            ...mockTableData,
            {
                userKey: '1-main-user3@example.com',
                userName: 'User Three',
                months: {
                    1: 450,
                    2: 375,
                    3: 0,
                    4: 0,
                    5: 0,
                    6: 0,
                    7: 0,
                    8: 0,
                    9: 0,
                    10: 0,
                    11: 0,
                    12: 0,
                },
            },
        ],
        userNames: ['User One', 'User Three', 'User Two'],
        selectedProjectId: null,
        selectedBranchName: null,
        selectedYear: null,
        selectedBranch: null,
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    '複数ユーザーの集計データを表示します。グラフでは各ユーザーが集合縦棒で表示され、表では複数行で表示されます。',
            },
        },
    },
};

export const WithUnknownUser: Story = {
    args: {
        projects: [...mockCommitProjects],
        branches: [...mockBranches],
        years: [2024],
        aggregations: [
            {
                ...mockAggregationItem,
                author_name: null,
            },
        ],
        chartData: [
            {
                month: '1月',
                'Unknown_additions': 100,
                'Unknown_deletions': 50,
            },
            ...Array.from({ length: 11 }, (_, i) => ({
                month: `${i + 2}月`,
                'Unknown_additions': 0,
                'Unknown_deletions': 0,
            })),
        ],
        tableData: [
            {
                userKey: '1-main-user1@example.com',
                userName: 'Unknown',
                months: {
                    1: 150,
                    2: 0,
                    3: 0,
                    4: 0,
                    5: 0,
                    6: 0,
                    7: 0,
                    8: 0,
                    9: 0,
                    10: 0,
                    11: 0,
                    12: 0,
                },
            },
        ],
        userNames: ['Unknown'],
        selectedProjectId: null,
        selectedBranchName: null,
        selectedYear: null,
        selectedBranch: null,
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    'author_nameがnullの場合、「Unknown」として表示されます。グラフと表の両方で「Unknown」が表示されます。',
            },
        },
    },
};

export const Error: Story = {
    args: {
        projects: [...mockCommitProjects],
        branches: [...mockBranches],
        years: [...mockYears],
        aggregations: [...mockAggregationItems],
        chartData: [...mockChartData],
        tableData: [...mockTableData],
        userNames: [...mockUserNames],
        error: '集計データの取得に失敗しました',
        selectedProjectId: null,
        selectedBranchName: null,
        selectedYear: null,
        selectedBranch: null,
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    'エラー状態を表示します。エラーメッセージが表示され、データは正常に表示されます。',
            },
        },
    },
};

export const Success: Story = {
    args: {
        projects: [...mockCommitProjects],
        branches: [...mockBranches],
        years: [...mockYears],
        aggregations: [...mockAggregationItems],
        chartData: [...mockChartData],
        tableData: [...mockTableData],
        userNames: [...mockUserNames],
        success: '集計データが更新されました',
        selectedProjectId: null,
        selectedBranchName: null,
        selectedYear: null,
        selectedBranch: null,
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    '成功メッセージを表示します。成功メッセージが表示され、データは正常に表示されます。',
            },
        },
    },
};

export const Loading: Story = {
    args: {
        projects: [...mockCommitProjects],
        branches: [...mockBranches],
        years: [...mockYears],
        aggregations: [...mockAggregationItems],
        chartData: [...mockChartData],
        tableData: [...mockTableData],
        userNames: [...mockUserNames],
        selectedProjectId: null,
        selectedBranchName: null,
        selectedYear: null,
        selectedBranch: null,
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    'セレクトボックスの変更時にページ遷移が発生する際のローディング状態を表示します。Inertia.jsのrouter.get()による非同期処理中は、ページ全体がローディング状態になります。\n\n**注意**: Docsではローディング状態が表示されない場合があります。個別のストーリーページで確認してください。',
            },
        },
    },
};

export const WithSelectedFilters: Story = {
    args: {
        projects: [...mockCommitProjects],
        branches: [...mockBranches],
        years: [2024],
        aggregations: [...mockAggregationItems],
        chartData: [...mockChartData],
        tableData: [...mockTableData],
        userNames: [...mockUserNames],
        selectedProjectId: 1,
        selectedBranchName: 'main',
        selectedYear: 2024,
        selectedBranch: {
            project_id: 1,
            branch_name: 'main',
        },
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    'セレクトボックスでプロジェクト・ブランチと年が選択されている状態を表示します。バックエンドから受け取った`selectedProjectId`、`selectedBranchName`、`selectedYear`プロップスを使用して、選択された値がセレクトボックスに反映されます。',
            },
        },
    },
};
