import type { Meta, StoryObj } from '@storybook/react-vite';
import React from 'react';
import Index from '@/pages/Project/Index';
import { setProcessing } from '../mocks/inertia';

const meta = {
    title: 'Pages/Project/Index',
    component: Index,
    parameters: {
        layout: 'fullscreen',
    },
    tags: ['autodocs'],
    argTypes: {
        projects: {
            control: 'object',
            description: 'プロジェクト一覧',
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
} satisfies Meta<typeof Index>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        projects: [
            {
                id: 1,
                name_with_namespace: 'group/project1',
                description: 'プロジェクト1の説明',
                default_branch: 'main',
            },
            {
                id: 2,
                name_with_namespace: 'group/project2',
                description: 'プロジェクト2の説明',
                default_branch: 'develop',
            },
            {
                id: 3,
                name_with_namespace: 'group/project3',
                description: null,
                default_branch: null,
            },
        ],
    },
    decorators: [
        (Story) => {
            React.useEffect(() => {
                setProcessing(false);
            }, []);
            return <Story />;
        },
    ],
};

export const Empty: Story = {
    args: {
        projects: [],
    },
    decorators: [
        (Story) => {
            React.useEffect(() => {
                setProcessing(false);
            }, []);
            return <Story />;
        },
    ],
};

export const Loading: Story = {
    args: {
        projects: [
            {
                id: 1,
                name_with_namespace: 'group/project1',
                description: 'プロジェクト1の説明',
                default_branch: 'main',
            },
        ],
    },
    decorators: [
        (Story) => {
            React.useEffect(() => {
                setProcessing(true);
                return () => {
                    setProcessing(false);
                };
            }, []);
            return <Story />;
        },
    ],
};

export const Error: Story = {
    args: {
        projects: [],
        error: 'GitLab APIへの接続に失敗しました。',
    },
    decorators: [
        (Story) => {
            React.useEffect(() => {
                setProcessing(false);
            }, []);
            return <Story />;
        },
    ],
};

export const Success: Story = {
    args: {
        projects: [
            {
                id: 1,
                name_with_namespace: 'group/project1',
                description: 'プロジェクト1の説明',
                default_branch: 'main',
            },
        ],
        success: '同期が完了しました。同期: 5件、削除: 2件',
    },
    decorators: [
        (Story) => {
            React.useEffect(() => {
                setProcessing(false);
            }, []);
            return <Story />;
        },
    ],
};
