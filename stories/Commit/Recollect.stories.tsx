import type { Meta, StoryObj } from '@storybook/react-vite';
import Recollect from '@/pages/Commit/Recollect';
import {
  mockRecollectHistoryItem,
  mockRecollectHistories,
} from '../mocks/data';
import { withDefaultProcessing, withProcessing } from '../mocks/decorators';

const meta = {
  title: 'Pages/Commit/Recollect',
  component: Recollect,
  parameters: {
    layout: 'fullscreen',
  },
  tags: ['autodocs'],
  argTypes: {
    histories: {
      control: 'object',
      description: '収集履歴一覧',
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
} satisfies Meta<typeof Recollect>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    histories: [...mockRecollectHistories],
  },
  decorators: [withDefaultProcessing],
};

export const Empty: Story = {
  args: {
    histories: [],
  },
  decorators: [withDefaultProcessing],
  parameters: {
    docs: {
      description: {
        story:
          '収集履歴が存在しない場合の空状態を表示します。「収集履歴が存在しません」のメッセージが表示されます。',
      },
    },
  },
};

export const Loading: Story = {
  args: {
    histories: [mockRecollectHistoryItem],
  },
  decorators: [withProcessing(true)],
  parameters: {
    docs: {
      description: {
        story:
          '再収集ボタンをクリックした後のローディング状態を表示します。「再収集中...」の表示とボタンの無効化を確認できます。\n\n**注意**: Docsではローディング状態が表示されない場合があります。個別のストーリーページで確認してください。',
      },
    },
  },
};

export const Error: Story = {
  args: {
    histories: [...mockRecollectHistories],
    error: '再収集に失敗しました。指定されたブランチが存在しません',
  },
  decorators: [withDefaultProcessing],
};

export const Success: Story = {
  args: {
    histories: [...mockRecollectHistories],
    success: '再収集が完了しました。新規: 10件、更新: 5件',
  },
  decorators: [withDefaultProcessing],
};

export const WithUncollected: Story = {
  args: {
    histories: [
      {
        ...mockRecollectHistoryItem,
        latest_committed_date: '',
      },
      {
        project_id: 2,
        project_name_with_namespace: 'group/project2',
        branch_name: 'develop',
        latest_committed_date: '2024-01-14T15:45:00Z',
      },
    ],
  },
  decorators: [withDefaultProcessing],
  parameters: {
    docs: {
      description: {
        story:
          '未収集の履歴（latest_committed_dateが空）を含む状態を表示します。未収集の項目は「未収集」と表示され、テキストがグレーアウトされます。',
      },
    },
  },
};
