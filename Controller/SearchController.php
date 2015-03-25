<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Service\ElasticsearchServiceInterface;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends BaseController
{

    public function searchAction(Request $request)
    {
        $this->assertWatcher();

        $form = $this->createFormBuilder(null, array('csrf_protection' => false))
            ->add('searchString', 'text', array('label' => 'Text'))
            ->add('search', 'submit')
            ->getForm();

        $form->handleRequest($request);

        $results = array();
        $searchString = null;
        if ($form->isValid()) {
            $searchString = $form->get('searchString')->getData();
            $results = $this->getElasticsearchService()->search($searchString);
        }

        return $this->render(
            'DdrGitkiBundle:Search:search.html.twig',
            array('form' => $form->createView(), 'searchString' => $searchString, 'results' => $results)
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
