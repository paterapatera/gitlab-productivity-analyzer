import type { Meta, StoryObj } from '@storybook/react-vite';
import Index from '@/pages/Project/Index';
import { mockProject, mockProjects } from '../mocks/data';
import { withDefaultProcessing, withProcessing } from '../mocks/decorators';

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
    projects: [...mockProjects],
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
    projects: [mockProject],
  },
  decorators: [withProcessing(true)],
};

export const Error: Story = {
  args: {
    projects: [],
    error: 'GitLab APIへの接続に失敗しました。',
  },
  decorators: [withDefaultProcessing],
};

export const Success: Story = {
  args: {
    projects: [mockProject],
    success: '同期が完了しました。同期: 5件、削除: 2件',
  },
  decorators: [withDefaultProcessing],
};
