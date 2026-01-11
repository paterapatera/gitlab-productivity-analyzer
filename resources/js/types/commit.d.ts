import { BasePageProps } from './common';

export interface CommitProject {
    id: number;
    name_with_namespace: string;
}

export interface CommitPageProps extends BasePageProps {
    projects: CommitProject[];
}
