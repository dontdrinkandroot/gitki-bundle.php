<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Command;

use Dontdrinkandroot\Gitki\BaseBundle\Service\WikiService;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;

abstract class GitkiContainerAwareCommand extends ContainerAwareCommand
{

    /**
     * @return WikiService
     */
    protected function getWikiService()
    {
        return $this->getContainer()->get('ddr.gitki.service.wiki');
    }

    /**
     * @return UserManagerInterface
     */
    protected function getUserManager()
    {
        return $this->getContainer()->get('fos_user.user_manager');
    }

    /**
     * @return QuestionHelper
     */
    protected function getQuestionHelper()
    {
        return $this->getHelper('question');
    }
}
