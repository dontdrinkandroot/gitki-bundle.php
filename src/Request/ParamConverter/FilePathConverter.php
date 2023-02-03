<?php

namespace Dontdrinkandroot\GitkiBundle\Request\ParamConverter;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\Path\FilePath;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class FilePathConverter implements ParamConverterInterface
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

        $request->attributes->set($param, FilePath::parse(Asserted::string($request->attributes->get($param))));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === FilePath::class;
    }
}
