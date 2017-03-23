<?php


namespace Dontdrinkandroot\GitkiBundle\Tests;

use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
trait GitRepositoryTestTrait
{
    /**
     * Init the git repository used for the tests.
     */
    protected function setUpRepo()
    {
        $repositoryTargetPath = $this->getRepositoryTargetPath();

        $fileSystem = new Filesystem();
        $fileSystem->remove($repositoryTargetPath);

        $fileSystem->mkdir($this->getRepositoryTargetPath());

        $git = new GitWrapper();
        $workingCopy = $git->init($this->getRepositoryTargetPath());
        $workingCopy->config('user.email', 'gitki@example.com');
        $workingCopy->config('user.name', 'Gitki');
        $this->initRepository($workingCopy);
    }

    /**
     * Tear down the git repository used for the tests.
     */
    protected function tearDownRepo()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->getRepositoryTargetPath());
    }

    /**
     * Fills the created git repository with test data.
     *
     * @param GitWorkingCopy $workingCopy
     */
    protected function initRepository(GitWorkingCopy $workingCopy)
    {
        $fileSystem = new Filesystem();
        $testRepoPath = $this->getRepositoryDataPath();

        $fileSystem->mirror($testRepoPath, $this->getRepositoryTargetPath());
        $workingCopy->add('', ['A' => '']);
        $workingCopy->commit('Initial commit');
    }

    /**
     * Get the path to the repository test data.
     *
     * @return string
     */
    protected function getRepositoryDataPath()
    {
        $targetPath = realPath(__DIR__ . '/Data/repo/');

        return $targetPath;
    }

    abstract protected function getRepositoryTargetPath();
}
