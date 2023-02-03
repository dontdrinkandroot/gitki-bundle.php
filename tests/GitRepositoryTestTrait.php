<?php


namespace Dontdrinkandroot\GitkiBundle\Tests;

use Symfony\Component\Filesystem\Filesystem;
use Symplify\GitWrapper\GitWorkingCopy;
use Symplify\GitWrapper\GitWrapper;

trait GitRepositoryTestTrait
{
    /**
     * Init the git repository used for the tests.
     */
    protected function setUpRepo(): void
    {
        $repositoryTargetPath = $this->getRepositoryTargetPath();

        $fileSystem = new Filesystem();
        $fileSystem->remove($repositoryTargetPath);

        $fileSystem->mkdir($this->getRepositoryTargetPath());

        $git = new GitWrapper('/usr/bin/git');
        $workingCopy = $git->init($this->getRepositoryTargetPath());
        $workingCopy->config('user.email', 'gitki@example.com');
        $workingCopy->config('user.name', 'Gitki');
        $this->initRepository($workingCopy);
    }

    /**
     * Tear down the git repository used for the tests.
     */
    protected function tearDownRepo(): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->getRepositoryTargetPath());
    }

    /**
     * Fills the created git repository with test data.
     */
    protected function initRepository(GitWorkingCopy $workingCopy): void
    {
        $fileSystem = new Filesystem();
        $testRepoPath = $this->getRepositoryTemplatePath();

        $fileSystem->mirror($testRepoPath, $this->getRepositoryTargetPath());
        $workingCopy->add('', ['A' => '']);
        $workingCopy->commit('Initial commit');
    }

    /**
     * Get the path to the repository test data.
     *
     * @return string
     */
    protected function getRepositoryTemplatePath()
    {
        $targetPath = realPath(__DIR__ . '/Data/repo/');

        return $targetPath;
    }

    abstract protected function getRepositoryTargetPath();
}
