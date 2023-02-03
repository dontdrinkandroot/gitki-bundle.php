<?php

namespace Dontdrinkandroot\GitkiBundle\Request\ParamConverter;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\Path\DirectoryPath;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class DirectoryPathConverter implements ParamConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $param = $configuration->getName();

        if (!$request->attributes->has($param)) {
            return false;
        }

        $request->attributes->set($param, DirectoryPath::parse(Asserted::string($request->attributes->get($param))));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === DirectoryPath::class;
    }
}
