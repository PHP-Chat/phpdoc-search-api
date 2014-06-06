<?php

namespace PHPDocSearch;

class GitRepositoryFactory
{
    /**
     * Create a GitRepository instance
     *
     * @param string $baseDir
     * @return GitRepository
     */
    public function create($baseDir)
    {
        return new GitRepository($baseDir);
    }
}
