export interface Project {
    id: number;
    name_with_namespace: string;
    description: string | null;
    default_branch: string | null;
}

export interface ProjectPageProps {
    projects: Project[];
    error?: string;
    success?: string;
}
