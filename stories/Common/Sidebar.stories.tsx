import type { Meta, StoryObj } from '@storybook/react-vite';
import { Sidebar } from '@/components/common/Sidebar';
import { SidebarProvider } from '@/components/ui/sidebar';
import { setPageData } from '../mocks/inertia';

const meta = {
  title: 'Common/Sidebar',
  component: Sidebar,
  parameters: {
    layout: 'fullscreen',
  },
  tags: ['autodocs'],
  decorators: [
    (Story) => (
      <SidebarProvider>
        <Story />
      </SidebarProvider>
    ),
  ],
} satisfies Meta<typeof Sidebar>;

export default meta;
type Story = StoryObj<typeof meta>;

/**
 * 正常状態のストーリー
 * すべてのメニュー項目が表示され、現在のページがプロジェクト同期画面
 */
export const Default: Story = {
  decorators: [
    (Story) => {
      setPageData({
        url: '/projects',
        component: 'Project/Index',
        props: {},
      });
      return (
        <SidebarProvider>
          <Story />
        </SidebarProvider>
      );
    },
  ],
};

/**
 * アクティブ状態のストーリー
 * コミット収集画面がアクティブな状態
 */
export const ActiveCollect: Story = {
  decorators: [
    (Story) => {
      setPageData({
        url: '/commits/collect',
        component: 'Commit/Index',
        props: {},
      });
      return (
        <SidebarProvider>
          <Story />
        </SidebarProvider>
      );
    },
  ],
};

/**
 * アクティブ状態のストーリー（部分一致）
 * コミット集計画面の詳細ページがアクティブな状態
 */
export const ActiveAggregationDetail: Story = {
  decorators: [
    (Story) => {
      setPageData({
        url: '/commits/aggregation?project_id=1&branch_name=main',
        component: 'Commit/Aggregation',
        props: {},
      });
      return (
        <SidebarProvider>
          <Story />
        </SidebarProvider>
      );
    },
  ],
};

/**
 * 折りたたみ状態のストーリー
 * サイドバーが折りたたまれた状態
 */
export const Collapsed: Story = {
  decorators: [
    (Story) => {
      setPageData({
        url: '/projects',
        component: 'Project/Index',
        props: { sidebarOpen: false },
      });
      return (
        <SidebarProvider defaultOpen={false}>
          <Story />
        </SidebarProvider>
      );
    },
  ],
};
