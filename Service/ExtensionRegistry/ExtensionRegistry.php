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

    public function registerExtension($extension, $name, $viewController = null, $editController = null)
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

    /**
     * @param string $controller
     * @param string $action
     * @param string $extension
     */
    public function registerFileAction($controller, $action = '', $extension = '')
    {
        if (!isset($this->fileTypeActions[$extension])) {
            $this->fileTypeActions[$extension] = [];
        }
        $this->fileTypeActions[$extension][$action] = $controller;
    }

    /**
     * @param string $controller
     * @param string $action
     */
    public function registerDirectoryAction($controller, $action = '')
    {
        $this->directoryActions[$action] = $controller;
    }

    /**
     * {@inheritdoc}
     */
    public function getName($extension)
    {
        if (!isset($this->nameMap[$extension])) {
            return null;
        }

        return $this->nameMap[$extension];
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable($extension)
    {
        if (!isset($this->editableMap[$extension])) {
            return false;
        }

        return $this->editableMap[$extension];
    }

    /**
     * {@inheritdoc}
     */
    public function getEditableExtensions()
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
    public function resolveDirectoryAction($action)
    {
        if (!isset($this->directoryActions[$action])) {
            throw new ResourceNotFoundException();
        }

        return $this->directoryActions[$action];
    }

    /**
     * {@inheritdoc}
     */
    public function resolveFileAction($action, $extension)
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
