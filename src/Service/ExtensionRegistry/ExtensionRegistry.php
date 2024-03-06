<?php

namespace Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry;

use Override;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ExtensionRegistry implements ExtensionRegistryInterface
{
    private const string ACTION_VIEW = '';

    private const string ACTION_EDIT = 'edit';

    /** @var array <string, string> */
    protected $nameMap = [];

    /** @var array <string, string> */
    protected array $editableMap = [];

    /** @var array <string, string> */
    protected array $directoryActions = [];

    /** @var array<string, array> */
    protected array $fileTypeActions = [];

    public function registerExtension(
        string $extension,
        string $name,
        ?string $viewController = null,
        ?string $editController = null
    ): void {
        $this->nameMap[$extension] = $name;

        if (null !== $viewController) {
            $this->registerFileAction($viewController, self::ACTION_VIEW, $extension);
        }

        if (null !== $editController) {
            $this->editableMap[$extension] = true;
            $this->registerFileAction($editController, self::ACTION_EDIT, $extension);
        }
    }

    public function registerFileAction(string $controller, string $action = '', string $extension = ''): void
    {
        if (!isset($this->fileTypeActions[$extension])) {
            $this->fileTypeActions[$extension] = [];
        }
        $this->fileTypeActions[$extension][$action] = $controller;
    }

    public function registerDirectoryAction(string $controller, string $action = ''): void
    {
        $this->directoryActions[$action] = $controller;
    }

    #[Override]
    public function getName(string $extension): ?string
    {
        if (!isset($this->nameMap[$extension])) {
            return null;
        }

        return $this->nameMap[$extension];
    }

    #[Override]
    public function isEditable(string $extension): bool
    {
        if (!isset($this->editableMap[$extension])) {
            return false;
        }

        return $this->editableMap[$extension];
    }

    #[Override]
    public function getEditableExtensions(): array
    {
        $editableExtensions = [];
        foreach ($this->nameMap as $extension => $name) {
            if ($this->isEditable($extension)) {
                $editableExtensions[$extension] = $name;
            }
        }

        return $editableExtensions;
    }

    #[Override]
    public function resolveDirectoryAction(string $action): string
    {
        if (!isset($this->directoryActions[$action])) {
            throw new ResourceNotFoundException();
        }

        return $this->directoryActions[$action];
    }

    #[Override]
    public function resolveFileAction(string $action, string $extension): string
    {
        if (isset($this->fileTypeActions[$extension][$action])) {
            return $this->fileTypeActions[$extension][$action];
        }

        if (!isset($this->fileTypeActions[''][$action])) {
            throw new ResourceNotFoundException();
        }

        return $this->fileTypeActions[''][$action];
    }
}
