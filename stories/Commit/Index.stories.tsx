import type { Meta, StoryObj } from '@storybook/react-vite';
import Index from '@/pages/Commit/Index';
import { mockCommitProject, mockCommitProjects } from '../mocks/data';
import { withDefaultProcessing, withProcessing } from '../mocks/decorators';

const meta = {
  title: 'Pages/Commit/Index',
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
    projects: [...mockCommitProjects],
  },
  decorators: [withDefaultProcessing],
};

export const Empty: Story = {
  args: {
    projects: [],
  },
  decorators: [withDefaultProcessing],
};

export const Loading: Story = {
  args: {
    projects: [mockCommitProject],
  },
  decorators: [withProcessing(true)],
  parameters: {
    docs: {
      description: {
        story:
          'フォーム送信後のローディング状態を表示します。「コミット収集中...」の表示とボタンの無効化を確認できます。\n\n**注意**: Docsではローディング状態が表示されない場合があります。個別のストーリーページで確認してください。',
      },
    },
  },
};

export const Error: Story = {
  args: {
    projects: [mockCommitProject],
    error: '指定されたブランチが存在しません',
  },
  decorators: [withDefaultProcessing],
};

export const Success: Story = {
  args: {
    projects: [mockCommitProject],
    success: '同期が完了しました。収集: 150件、保存: 150件',
  },
  decorators: [withDefaultProcessing],
};
