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
