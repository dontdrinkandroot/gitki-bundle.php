<?php


namespace Dontdrinkandroot\GitkiBundle\Service;

interface ExtensionRegistryInterface
{

    /**
     * @param string $extension
     *
     * @return string|null
     */
    public function getName($extension);

    /**
     * @param string $extension
     *
     * @return bool
     */
    public function isEditable($extension);

    /**
     * @return array
     */
    public function getEditableExtensions();

    /**
     * @param string $action
     *
     * @return string
     */
    public function resolveDirectoryAction($action);

    /**
     * @param string $action
     * @param string $extension
     *
     * @return string
     */
    public function resolveFileAction($action, $extension);
}