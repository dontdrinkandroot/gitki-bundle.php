<?php

namespace Dontdrinkandroot\GitkiBundle\Request\ValueResolver;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\Path\DirectoryPath;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DirectoryPathValueResolver implements ValueResolverInterface
{
    #[Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== DirectoryPath::class) {
            return [];
        }

        return [DirectoryPath::parse(Asserted::string($request->attributes->get($argument->getName())))];
    }
}
