<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Security\SecurityAttribute;
use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends BaseController
{
    public function __construct(
        SecurityService $securityService,
        private readonly ElasticsearchServiceInterface $elasticsearchService
    ) {
        parent::__construct($securityService);
    }

    public function searchAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::READ_PATH);

        $options = [];
        if ($this->has('security.csrf.token_manager')) {
            $options['csrf_protection'] = false;
        }

        $form = $this->createFormBuilder(null, $options)
            ->setMethod('GET')
            ->add('searchString', TextType::class, ['label' => 'Text'])
            ->add('search', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        $results = [];
        $searchString = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $searchString = $form->get('searchString')->getData();
            $results = $this->elasticsearchService->search($searchString);
        }

        return $this->render(
            '@DdrGitki/Search/search.html.twig',
            ['form' => $form->createView(), 'searchString' => $searchString, 'results' => $results]
        );
    }
}
