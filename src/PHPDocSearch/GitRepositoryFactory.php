<?php

namespace PHPDocSearch;

class GitRepositoryFactory
{
    public function create($baseDir)
    {
        return new GitRepository($baseDir);
    }
}
