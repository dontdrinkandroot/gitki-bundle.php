<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class SearchController extends BaseController
{
    public function searchAction(Request $request)
    {
        $this->assertWatcher();

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
            $results = $this->getElasticsearchService()->search($searchString);
        }

        return $this->render(
            'DdrGitkiBundle:Search:search.html.twig',
            ['form' => $form->createView(), 'searchString' => $searchString, 'results' => $results]
        );
    }

    /**
     * @return ElasticsearchServiceInterface
     */
    protected function getElasticsearchService()
    {
        return $this->get('ddr.gitki.service.elasticsearch');
    }
}
