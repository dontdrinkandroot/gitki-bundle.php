<?php


namespace Dontdrinkandroot\GitkiBundle\Command;

use Dontdrinkandroot\GitkiBundle\Service\WikiService;
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
     * @return QuestionHelper
     */
    protected function getQuestionHelper()
    {
        return $this->getHelper('question');
    }
}
