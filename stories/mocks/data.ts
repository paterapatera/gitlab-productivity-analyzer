/**
 * ストーリーで使用する共通のモックデータ
 */

export const mockCommitProject = {
  id: 1,
  name_with_namespace: 'group/project1',
} as const;

export const mockCommitProjects = [
  mockCommitProject,
  {
    id: 2,
    name_with_namespace: 'group/project2',
  },
  {
    id: 3,
    name_with_namespace: 'group/project3',
  },
] as const;

export const mockProject = {
  id: 1,
  name_with_namespace: 'group/project1',
  description: 'プロジェクト1の説明',
  default_branch: 'main',
} as const;

export const mockProjects = [
  mockProject,
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
] as const;

export const mockRecollectHistoryItem = {
  project_id: 1,
  project_name_with_namespace: 'group/project1',
  branch_name: 'main',
  latest_committed_date: '2024-01-15T10:30:00Z',
} as const;

export const mockRecollectHistories = [
  mockRecollectHistoryItem,
  {
    project_id: 2,
    project_name_with_namespace: 'group/project2',
    branch_name: 'develop',
    latest_committed_date: '2024-01-14T15:45:00Z',
  },
  {
    project_id: 3,
    project_name_with_namespace: 'group/project3',
    branch_name: 'feature/new-feature',
    latest_committed_date: '2024-01-13T09:20:00Z',
  },
] as const;

export const mockAggregationItem = {
  project_id: 1,
  branch_name: 'main',
  author_email: 'user1@example.com',
  author_name: 'User One',
  year: 2024,
  month: 1,
  total_additions: 100,
  total_deletions: 50,
  commit_count: 5,
} as const;

export const mockAggregationItems = [
  mockAggregationItem,
  {
    project_id: 1,
    branch_name: 'main',
    author_email: 'user1@example.com',
    author_name: 'User One',
    year: 2024,
    month: 2,
    total_additions: 150,
    total_deletions: 75,
    commit_count: 8,
  },
  {
    project_id: 1,
    branch_name: 'main',
    author_email: 'user2@example.com',
    author_name: 'User Two',
    year: 2024,
    month: 1,
    total_additions: 200,
    total_deletions: 100,
    commit_count: 10,
  },
  {
    project_id: 1,
    branch_name: 'main',
    author_email: 'user2@example.com',
    author_name: 'User Two',
    year: 2024,
    month: 2,
    total_additions: 180,
    total_deletions: 90,
    commit_count: 9,
  },
] as const;

export const mockChartData = [
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
  {
    month: '3月',
    'User One_additions': 0,
    'User One_deletions': 0,
    'User Two_additions': 0,
    'User Two_deletions': 0,
  },
  {
    month: '4月',
    'User One_additions': 0,
    'User One_deletions': 0,
    'User Two_additions': 0,
    'User Two_deletions': 0,
  },
  {
    month: '5月',
    'User One_additions': 0,
    'User One_deletions': 0,
    'User Two_additions': 0,
    'User Two_deletions': 0,
  },
  {
    month: '6月',
    'User One_additions': 0,
    'User One_deletions': 0,
    'User Two_additions': 0,
    'User Two_deletions': 0,
  },
  {
    month: '7月',
    'User One_additions': 0,
    'User One_deletions': 0,
    'User Two_additions': 0,
    'User Two_deletions': 0,
  },
  {
    month: '8月',
    'User One_additions': 0,
    'User One_deletions': 0,
    'User Two_additions': 0,
    'User Two_deletions': 0,
  },
  {
    month: '9月',
    'User One_additions': 0,
    'User One_deletions': 0,
    'User Two_additions': 0,
    'User Two_deletions': 0,
  },
  {
    month: '10月',
    'User One_additions': 0,
    'User One_deletions': 0,
    'User Two_additions': 0,
    'User Two_deletions': 0,
  },
  {
    month: '11月',
    'User One_additions': 0,
    'User One_deletions': 0,
    'User Two_additions': 0,
    'User Two_deletions': 0,
  },
  {
    month: '12月',
    'User One_additions': 0,
    'User One_deletions': 0,
    'User Two_additions': 0,
    'User Two_deletions': 0,
  },
] as const;

export const mockTableData = [
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
  {
    userKey: '1-main-user2@example.com',
    userName: 'User Two',
    months: {
      1: 300,
      2: 270,
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
] as const;

export const mockBranches = [
  {
    project_id: 1,
    branch_name: 'main',
  },
  {
    project_id: 2,
    branch_name: 'develop',
  },
] as const;

export const mockYears = [2024, 2023] as const;

export const mockUserNames = ['User One', 'User Two'] as const;

export const mockUserInfo = {
  author_email: 'user1@example.com',
  author_name: 'User One',
} as const;

export const mockUserInfos = [
  mockUserInfo,
  {
    author_email: 'user2@example.com',
    author_name: 'User Two',
  },
  {
    author_email: 'user3@example.com',
    author_name: null,
  },
] as const;

export const mockUserProductivityChartData = [
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
] as const;

export const mockUserProductivityTableData = [
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
  {
    userKey: 'user2@example.com',
    userName: 'User Two',
    months: {
      1: 300,
      2: 270,
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
] as const;
