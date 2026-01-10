import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Index from '@/pages/Project/Index';

// Inertia.jsのモック
vi.mock('@inertiajs/react', () => ({
    useForm: vi.fn(() => ({
        post: vi.fn(),
        processing: false,
        errors: {},
    })),
    router: {
        post: vi.fn(),
    },
    Head: ({ children }: { children: React.ReactNode }) => <>{children}</>,
}));

describe('Project/Index', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('プロジェクト一覧を表示する', () => {
        const projects = [
            {
                id: 1,
                name_with_namespace: 'group/project1',
                description: 'Description 1',
                default_branch: 'main',
            },
            {
                id: 2,
                name_with_namespace: 'group/project2',
                description: 'Description 2',
                default_branch: 'develop',
            },
        ];

        render(<Index projects={projects} />);

        expect(screen.getByText('group/project1')).toBeInTheDocument();
        expect(screen.getByText('group/project2')).toBeInTheDocument();
    });

    it('空のプロジェクト一覧の場合、空状態メッセージを表示する', () => {
        render(<Index projects={[]} />);

        expect(screen.getByText(/プロジェクトが存在しません/i)).toBeInTheDocument();
    });

    it('同期ボタンを表示する', () => {
        render(<Index projects={[]} />);

        expect(screen.getByRole('button', { name: /同期/i })).toBeInTheDocument();
    });

    it('同期ボタンをクリックすると同期リクエストを送信する', async () => {
        const user = userEvent.setup();
        const { useForm } = await import('@inertiajs/react');
        const mockPost = vi.fn();
        vi.mocked(useForm).mockReturnValue({
            post: mockPost,
            processing: false,
            errors: {},
        } as any);

        render(<Index projects={[]} />);

        const syncButton = screen.getByRole('button', { name: /同期/i });
        await user.click(syncButton);

        expect(mockPost).toHaveBeenCalledWith('/projects/sync');
    });

    it('同期処理中はローディング状態を表示する', () => {
        const { useForm } = require('@inertiajs/react');
        vi.mocked(useForm).mockReturnValue({
            post: vi.fn(),
            processing: true,
            errors: {},
        } as any);

        render(<Index projects={[]} />);

        expect(screen.getByText(/同期中/i)).toBeInTheDocument();
    });

    it('エラーメッセージがある場合、エラー状態を表示する', () => {
        render(<Index projects={[]} error="GitLab APIへの接続に失敗しました。" />);

        expect(screen.getByText(/GitLab APIへの接続に失敗しました。/i)).toBeInTheDocument();
    });

    it('成功メッセージがある場合、成功状態を表示する', () => {
        render(<Index projects={[]} success="同期が完了しました。" />);

        expect(screen.getByText(/同期が完了しました。/i)).toBeInTheDocument();
    });
});
