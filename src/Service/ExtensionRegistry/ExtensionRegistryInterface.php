<?php

namespace Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry;

interface ExtensionRegistryInterface
{
    public function getName(string $extension): ?string;

    public function isEditable(string $extension): bool;

    /** @return array<string, string> */
    public function getEditableExtensions(): array;

    public function resolveDirectoryAction(string $action): string;

    public function resolveFileAction(string $action, string $extension): string;
}
