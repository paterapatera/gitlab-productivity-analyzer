import type { Meta, StoryObj } from '@storybook/react-vite';
import { AppLayout } from '@/layouts/AppLayout';
import { PageLayout } from '@/components/common/PageLayout';
import { setPageData } from '../mocks/inertia';

const meta = {
  title: 'Layouts/AppLayout',
  component: AppLayout,
  parameters: {
    layout: 'fullscreen',
  },
  tags: ['autodocs'],
  argTypes: {
    children: {
      control: false,
      description: 'ページコンテンツ',
    },
  },
} satisfies Meta<typeof AppLayout>;

export default meta;
type Story = StoryObj<typeof meta>;

/**
 * サイドバー表示のストーリー
 * サイドバーが開いた状態で表示
 */
export const Default: Story = {
  args: {
    children: (
      <PageLayout title="プロジェクト一覧">
        <div className="rounded-md border p-6">
          <p>これはサンプルのページコンテンツです。</p>
          <p>サイドバーが左側に表示され、メインコンテンツエリアが右側に表示されます。</p>
        </div>
      </PageLayout>
    ),
  },
  decorators: [
    (Story) => {
      setPageData({
        url: '/projects',
        component: 'Project/Index',
        props: { sidebarOpen: true },
      });
      return <Story />;
    },
  ],
};

/**
 * 折りたたみ状態のストーリー
 * サイドバーが折りたたまれた状態で表示
 */
export const Collapsed: Story = {
  args: {
    children: (
      <PageLayout title="プロジェクト一覧">
        <div className="rounded-md border p-6">
          <p>これはサンプルのページコンテンツです。</p>
          <p>サイドバーが折りたたまれた状態で表示されます。</p>
          <p>SidebarTrigger ボタンをクリックするとサイドバーを開閉できます。</p>
        </div>
      </PageLayout>
    ),
  },
  decorators: [
    (Story) => {
      setPageData({
        url: '/projects',
        component: 'Project/Index',
        props: { sidebarOpen: false },
      });
      return <Story />;
    },
  ],
};

/**
 * アクティブなメニュー項目がある状態のストーリー
 * コミット収集画面がアクティブな状態
 */
export const ActiveMenuItem: Story = {
  args: {
    children: (
      <PageLayout title="コミット収集">
        <div className="rounded-md border p-6">
          <p>これはサンプルのページコンテンツです。</p>
          <p>サイドバーの「コミット収集」メニュー項目がアクティブ状態として表示されます。</p>
        </div>
      </PageLayout>
    ),
  },
  decorators: [
    (Story) => {
      setPageData({
        url: '/commits/collect',
        component: 'Commit/Index',
        props: { sidebarOpen: true },
      });
      return <Story />;
    },
  ],
};
