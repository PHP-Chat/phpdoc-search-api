<?php

namespace PHPDocSearch\Indexer;

use PHPDocSearch\Logger,
    PHPDocSearch\Environment,
    PHPDocSearch\GitRepository,
    PHPDocSearch\GitRepositoryFactory;

class ManualXMLBuilder
{
    /**
     * @var Environment
     */
    private $env;

    /**
     * @var GitRepositoryFactory
     */
    private $repoFactory;

    /**
     * @var ManualXMLWrapperFactory
     */
    private $xmlWrapperFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Environment $env
     * @param GitRepositoryFactory $repoFactory
     * @param ManualXMLWrapperFactory $xmlWrapperFactory
     * @param Logger $logger
     */
    public function __construct(
        Environment $env,
        GitRepositoryFactory $repoFactory,
        ManualXMLWrapperFactory $xmlWrapperFactory,
        Logger $logger
    ) {
        $this->env = $env;
        $this->repoFactory = $repoFactory;
        $this->xmlWrapperFactory = $xmlWrapperFactory;
        $this->logger = $logger;
    }

    /**
     * Create the required repos
     *
     * @return GitRepository[]
     */
    private function createRepos()
    {
        $baseDir = $this->env->getBaseDir() . DIRECTORY_SEPARATOR;

        return [
            'base' => $this->repoFactory->create($baseDir . 'base'),
            'en' => $this->repoFactory->create($baseDir . 'en'),
        ];
    }

    /**
     * Sync the repos
     *
     * @param GitRepository[] $repos
     * @return bool
     */
    private function syncRepos($repos)
    {
        $changed = false;

        foreach ($repos as $repo) {
            $oldHead = $repo->getLastCommit();

            $repo->checkout();
            $repo->clean();
            $repo->pull();

            if ($repo->getLastCommit() !== $oldHead) {
                $changed = true;
            }

            $this->logger->log('  Repository ' . $repo->getName() . ' synced');
        }

        return $changed;
    }

    /**
     * Clean the repos
     *
     * @param GitRepository[] $repos
     */
    private function cleanRepos($repos)
    {
        foreach ($repos as $repo) {
            $repo->checkout();
            $repo->clean();

            $this->logger->log('  Repository ' . $repo->getName() . ' clean');
        }
    }

    /**
     * Compile the XML document
     *
     * @param string $tempFile
     * @return bool
     */
    private function buildXML($tempFile)
    {
        $this->logger->log('  Building manual XML document');

        $cmd = 'php configure.php "--output=' . $tempFile . '"';

        $oldWorkingDir = getcwd();
        chdir($this->env->getBaseDir() . '/base');
        exec($cmd, $output, $exitCode);
        chdir($oldWorkingDir);

        $this->logger->log('  Build process exited with code ' . $exitCode);

        return $exitCode === 0;
    }

    /**
     * Build the manual XML document and load it into DOM
     *
     * @return ManualXMLWrapper
     * @throws \RuntimeException
     */
    public function build()
    {
        $srcFile = $this->env->getBaseDir() . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . '.manual.xml';

        $repos = $this->createRepos();

        if (!$this->env->hasArg('nosync')) {
            $this->logger->log('Syncing local repositories with remote sources...');

            $changed = $this->syncRepos($repos);
            if (!$changed) {
                $this->logger->warn('Remote repositories have not changed since last sync');

                if (!$this->env->hasArg('force')) {
                    throw new \RuntimeException('Nothing to do');
                }
            }
        }

        if (!$this->env->hasArg('nobuild')) {
            $this->logger->log('Building manual XML...');
            $buildSuccess = $this->buildXML($srcFile);

            $this->cleanRepos($repos);

            if (!$buildSuccess) {
                throw new \RuntimeException('  Manual build process failed');
            }
        } else if (!is_file($srcFile)) {
            throw new \RuntimeException('Manual source file missing');
        }

        $this->logger->log('Loading manual XML...');
        return $this->xmlWrapperFactory->create($srcFile, $this->env->hasArg('keep'));
    }
}
