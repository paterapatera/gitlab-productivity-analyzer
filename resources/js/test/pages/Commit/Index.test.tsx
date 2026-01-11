import Index from '@/pages/Commit/Index';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createMockCommitProject } from '../../helpers/data';
import { createMockUseForm } from '../../mocks/inertia';

// Inertia.js のモック
const mockPost = vi.fn();
const mockClearErrors = vi.fn();

const mockUseForm = vi.fn(() =>
    createMockUseForm({
        post: mockPost,
        clearErrors: mockClearErrors,
    }),
);

vi.mock('@inertiajs/react', () => ({
    Form: ({
        children,
        action,
        method,
    }: {
        children: (props: {
            errors: Record<string, string>;
            processing: boolean;
            clearErrors: (key?: string) => void;
        }) => React.ReactNode;
        action: string;
        method?: string;
    }) => {
        const form = mockUseForm();
        return (
            <form
                action={action}
                method={method || 'post'}
                onSubmit={(e) => {
                    e.preventDefault();
                    const postFn = form.post as (url: string) => void;
                    if (typeof postFn === 'function') {
                        postFn(action);
                    }
                }}
            >
                {children({
                    errors: form.errors || {},
                    processing: form.processing || false,
                    clearErrors:
                        (form.clearErrors as (key?: string) => void) ||
                        mockClearErrors,
                })}
            </form>
        );
    },
    useForm: mockUseForm,
    router: {
        post: vi.fn(),
    },
    Head: ({ children }: { children: React.ReactNode }) => <>{children}</>,
}));

describe('Commit/Index', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        mockPost.mockClear();
        mockClearErrors.mockClear();
    });

    it('プロジェクト一覧を表示する', () => {
        const projects = [
            createMockCommitProject({
                id: 1,
                name_with_namespace: 'group/project1',
            }),
            createMockCommitProject({
                id: 2,
                name_with_namespace: 'group/project2',
            }),
        ];

        render(<Index projects={projects} />);

        expect(screen.getByText('group/project1')).toBeInTheDocument();
        expect(screen.getByText('group/project2')).toBeInTheDocument();
    });

    it('プロジェクト選択UIを表示する', () => {
        const projects = [createMockCommitProject()];

        render(<Index projects={projects} />);

        expect(screen.getByLabelText(/プロジェクト/i)).toBeInTheDocument();
    });

    it('ブランチ名入力フィールドを表示する', () => {
        render(<Index projects={[]} />);

        const branchInput = screen.getByLabelText(/ブランチ名/i);
        expect(branchInput).toBeInTheDocument();
        expect(branchInput).toHaveAttribute('type', 'text');
        expect(branchInput).toHaveAttribute('required');
    });

    it('開始日入力フィールドを表示する', () => {
        render(<Index projects={[]} />);

        const dateInput = screen.getByLabelText(/開始日/i);
        expect(dateInput).toBeInTheDocument();
        expect(dateInput).toHaveAttribute('type', 'date');
    });

    it('プロジェクト選択UIが表示される', () => {
        const projects = [createMockCommitProject()];

        render(<Index projects={projects} />);

        // プロジェクト選択のラベルが表示されることを確認
        expect(screen.getByText(/プロジェクト/i)).toBeInTheDocument();
        // プロジェクトオプションが存在することを確認
        expect(screen.getByText('group/project1')).toBeInTheDocument();
    });

    it('ブランチ名を入力できる', async () => {
        const user = userEvent.setup();
        render(<Index projects={[]} />);

        const branchInput = screen.getByLabelText(/ブランチ名/i);
        await user.type(branchInput, 'main');

        expect(branchInput).toHaveValue('main');
    });

    it('開始日を入力できる', async () => {
        const user = userEvent.setup();
        render(<Index projects={[]} />);

        const dateInput = screen.getByLabelText(/開始日/i);
        await user.type(dateInput, '2025-01-01');

        expect(dateInput).toHaveValue('2025-01-01');
    });

    it('コミット収集ボタンを表示する', () => {
        render(<Index projects={[]} />);

        expect(
            screen.getByRole('button', { name: /コミット収集/i }),
        ).toBeInTheDocument();
    });

    it('コミット収集ボタンをクリックするとフォームを送信する', async () => {
        const user = userEvent.setup();
        const mockPostFn = vi.fn();
        mockUseForm.mockReturnValue(createMockUseForm({ post: mockPostFn }));

        const projects = [createMockCommitProject()];

        render(<Index projects={projects} />);

        // ブランチ名を入力
        const branchInput = screen.getByLabelText(/ブランチ名/i);
        await user.type(branchInput, 'main');

        // フォームを送信
        const submitButton = screen.getByRole('button', {
            name: /コミット収集/i,
        });
        await user.click(submitButton);

        await waitFor(() => {
            expect(mockPostFn).toHaveBeenCalled();
        });
    });

    it('開始日パラメータを含めてコミット収集を実行できる', async () => {
        const user = userEvent.setup();
        const mockPostFn = vi.fn();
        mockUseForm.mockReturnValue(createMockUseForm({ post: mockPostFn }));

        const projects = [createMockCommitProject()];

        render(<Index projects={projects} />);

        // ブランチ名を入力
        const branchInput = screen.getByLabelText(/ブランチ名/i);
        await user.type(branchInput, 'main');

        // 開始日を入力
        const dateInput = screen.getByLabelText(/開始日/i);
        await user.type(dateInput, '2025-01-01');

        // フォームを送信
        const submitButton = screen.getByRole('button', {
            name: /コミット収集/i,
        });
        await user.click(submitButton);

        await waitFor(() => {
            expect(mockPostFn).toHaveBeenCalled();
        });
    });

    it('ローディング状態の表示をテストする', () => {
        mockUseForm.mockReturnValue(createMockUseForm({ processing: true }));

        render(<Index projects={[]} />);

        expect(screen.getByText(/コミット収集中/i)).toBeInTheDocument();
        const submitButton = screen.getByRole('button', {
            name: /コミット収集中/i,
        });
        expect(submitButton).toBeDisabled();
    });

    it('エラーメッセージがある場合、エラー状態を表示する', () => {
        render(
            <Index projects={[]} error="指定されたブランチが存在しません" />,
        );

        expect(
            screen.getByText(/指定されたブランチが存在しません/i),
        ).toBeInTheDocument();
    });

    it('成功メッセージがある場合、成功状態を表示する', () => {
        render(
            <Index
                projects={[]}
                success="コミット収集が完了しました。収集: 150件、保存: 150件"
            />,
        );

        expect(
            screen.getByText(
                /コミット収集が完了しました。収集: 150件、保存: 150件/i,
            ),
        ).toBeInTheDocument();
    });

    it('バリデーションエラーがある場合、エラーメッセージを表示する', () => {
        mockUseForm.mockReturnValue(
            createMockUseForm({
                errors: {
                    branch_name: 'ブランチ名は必須です',
                },
            }),
        );

        render(<Index projects={[]} />);

        expect(screen.getByText(/ブランチ名は必須です/i)).toBeInTheDocument();
    });
});
