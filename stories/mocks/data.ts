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
