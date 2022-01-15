<?php

namespace Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ExtensionRegistry implements ExtensionRegistryInterface
{
    const ACTION_VIEW = '';

    const ACTION_EDIT = 'edit';

    protected $nameMap = [];

    protected $editableMap = [];

    protected $directoryActions = [];

    protected $fileTypeActions = [];

    public function registerExtension($extension, $name, $viewController = null, $editController = null): void
    {
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

    /**
     * {@inheritdoc}
     */
    public function getName(string $extension): ?string
    {
        if (!isset($this->nameMap[$extension])) {
            return null;
        }

        return $this->nameMap[$extension];
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable(string $extension): bool
    {
        if (!isset($this->editableMap[$extension])) {
            return false;
        }

        return $this->editableMap[$extension];
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function resolveDirectoryAction(string $action): string
    {
        if (!isset($this->directoryActions[$action])) {
            throw new ResourceNotFoundException();
        }

        return $this->directoryActions[$action];
    }

    /**
     * {@inheritdoc}
     */
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
