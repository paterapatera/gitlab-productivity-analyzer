import type { Meta, StoryObj } from '@storybook/react-vite';
import UserProductivity from '@/pages/Commit/UserProductivity';
import {
    mockUserInfos,
    mockYears,
    mockUserProductivityChartData,
    mockUserProductivityTableData,
    mockUserNames,
} from '../mocks/data';
import { withDefaultProcessing, withProcessing } from '../mocks/decorators';

const meta = {
    title: 'Pages/Commit/UserProductivity',
    component: UserProductivity,
    parameters: {
        layout: 'fullscreen',
    },
    tags: ['autodocs'],
    argTypes: {
        users: {
            control: 'object',
            description: 'ユーザー一覧',
        },
        years: {
            control: 'object',
            description: '年一覧',
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
        selectedYear: {
            control: 'number',
            description: '選択された年',
        },
        selectedUsers: {
            control: 'object',
            description: '選択されたユーザー（メールアドレスの配列）',
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
} satisfies Meta<typeof UserProductivity>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        users: [...mockUserInfos],
        years: [...mockYears],
        chartData: [...mockUserProductivityChartData],
        tableData: [...mockUserProductivityTableData],
        userNames: [...mockUserNames],
        selectedYear: 2024,
        selectedUsers: ['user1@example.com', 'user2@example.com'],
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    '正常状態のユーザー生産性画面を表示します。年フィルターとユーザー複数選択フィルター、棒グラフ、表が表示されます。',
            },
        },
    },
};

export const Empty: Story = {
    args: {
        users: [...mockUserInfos],
        years: [...mockYears],
        chartData: [],
        tableData: [],
        userNames: [],
        selectedYear: undefined,
        selectedUsers: undefined,
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    '集計データが存在しない場合の空状態を表示します。「集計データが存在しません。年とユーザーを選択してください。」のメッセージが表示されます。',
            },
        },
    },
};

export const SingleUser: Story = {
    args: {
        users: [
            {
                author_email: 'user1@example.com',
                author_name: 'User One',
            },
        ],
        years: [2024],
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
                userKey: 'user1@example.com',
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
        selectedYear: 2024,
        selectedUsers: ['user1@example.com'],
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
        users: [...mockUserInfos],
        years: [2024],
        chartData: [
            {
                month: '1月',
                'User One_additions': 100,
                'User One_deletions': 50,
                'User Two_additions': 200,
                'User Two_deletions': 100,
            },
            {
                month: '2月',
                'User One_additions': 150,
                'User One_deletions': 75,
                'User Two_additions': 180,
                'User Two_deletions': 90,
            },
            ...Array.from({ length: 10 }, (_, i) => ({
                month: `${i + 3}月`,
                'User One_additions': 0,
                'User One_deletions': 0,
                'User Two_additions': 0,
                'User Two_deletions': 0,
            })),
        ],
        tableData: [...mockUserProductivityTableData],
        userNames: ['User One', 'User Two'],
        selectedYear: 2024,
        selectedUsers: ['user1@example.com', 'user2@example.com'],
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
        users: [
            {
                author_email: 'user3@example.com',
                author_name: null,
            },
        ],
        years: [2024],
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
                userKey: 'user3@example.com',
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
        selectedYear: 2024,
        selectedUsers: ['user3@example.com'],
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
        users: [...mockUserInfos],
        years: [...mockYears],
        chartData: [...mockUserProductivityChartData],
        tableData: [...mockUserProductivityTableData],
        userNames: [...mockUserNames],
        selectedYear: 2024,
        selectedUsers: ['user1@example.com', 'user2@example.com'],
        error: 'ユーザー生産性データの取得に失敗しました',
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
        users: [...mockUserInfos],
        years: [...mockYears],
        chartData: [...mockUserProductivityChartData],
        tableData: [...mockUserProductivityTableData],
        userNames: [...mockUserNames],
        selectedYear: 2024,
        selectedUsers: ['user1@example.com', 'user2@example.com'],
        success: 'ユーザー生産性データが更新されました',
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
        users: [...mockUserInfos],
        years: [...mockYears],
        chartData: [...mockUserProductivityChartData],
        tableData: [...mockUserProductivityTableData],
        userNames: [...mockUserNames],
        selectedYear: 2024,
        selectedUsers: ['user1@example.com', 'user2@example.com'],
    },
    decorators: [withProcessing(true)],
    parameters: {
        docs: {
            description: {
                story:
                    'フィルター変更時にページ遷移が発生する際のローディング状態を表示します。Inertia.jsのrouter.get()による非同期処理中は、ページ全体がローディング状態になります。\n\n**注意**: Docsではローディング状態が表示されない場合があります。個別のストーリーページで確認してください。',
            },
        },
    },
};

export const WithSelectedFilters: Story = {
    args: {
        users: [...mockUserInfos],
        years: [2024, 2023],
        chartData: [...mockUserProductivityChartData],
        tableData: [...mockUserProductivityTableData],
        userNames: [...mockUserNames],
        selectedYear: 2024,
        selectedUsers: ['user1@example.com'],
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    '年フィルターとユーザーフィルターが選択されている状態を表示します。バックエンドから受け取った`selectedYear`と`selectedUsers`プロップスを使用して、選択された値がフィルターに反映されます。',
            },
        },
    },
};

export const NoUsersSelected: Story = {
    args: {
        users: [...mockUserInfos],
        years: [2024],
        chartData: [],
        tableData: [],
        userNames: [],
        selectedYear: 2024,
        selectedUsers: [],
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    '年は選択されているが、ユーザーが選択されていない状態を表示します。空状態メッセージが表示されます。',
            },
        },
    },
};

export const NoYearSelected: Story = {
    args: {
        users: [...mockUserInfos],
        years: [2024, 2023],
        chartData: [],
        tableData: [],
        userNames: [],
        selectedYear: undefined,
        selectedUsers: ['user1@example.com'],
    },
    decorators: [withDefaultProcessing],
    parameters: {
        docs: {
            description: {
                story:
                    'ユーザーは選択されているが、年が選択されていない状態を表示します。空状態メッセージが表示されます。',
            },
        },
    },
};
