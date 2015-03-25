<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Exception\PageLockedException;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class FileController extends BaseController
{

    public function serveAction(Request $request, $path)
    {
        $this->assertWatcher();

        $filePath = FilePath::parse($path);

        $file = $this->getWikiService()->getFile($filePath);

        $response = new Response();
        $lastModified = new \DateTime();
        $lastModified->setTimestamp($file->getMTime());
        $response->setLastModified($lastModified);
        if ($response->isNotModified($request)) {
            return $response;
        }

        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent($this->getContents($file));

        return $response;
    }

    public function deleteAction($path)
    {
        $this->assertCommitter();

        $filePath = FilePath::parse($path);
        $user = $this->getUser();

        $commitMessage = 'Removing ' . $filePath->toAbsoluteString();
        $this->getWikiService()->deleteFile($user, $filePath, $commitMessage);

        return $this->redirectToRoute(
            'ddr_gitki_directory',
            ['path' => $filePath->getParentPath()->toAbsoluteString()]
        );
    }

    public function holdLockAction($path)
    {
        $this->assertCommitter();

        $filePath = FilePath::parse($path);
        $user = $this->getUser();

        $expiry = $this->getWikiService()->holdLock($user, $filePath);

        return new Response($expiry);
    }

    public function historyAction($path)
    {
        $this->assertWatcher();

        $filePath = FilePath::parse($path);

        $history = $this->getWikiService()->getFileHistory($filePath);

        return $this->render(
            'DdrGitkiBaseBundle:File:history.html.twig',
            [
                'path'    => $filePath,
                'history' => $history
            ]
        );
    }

    public function renameAction(Request $request, $path)
    {
        $this->assertCommitter();

        $filePath = FilePath::parse($path);
        $user = $this->getUser();

        try {
            $this->getWikiService()->createLock($user, $filePath);
        } catch (PageLockedException $e) {
            throw new ConflictHttpException($e->getMessage());
        }

        $form = $this->createFormBuilder()
            ->add('newpath', 'text', ['label' => 'New path', 'required' => true])
            ->add('rename', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $newPath = FilePath::parse($form->get('newpath')->getData());
                $this->getWikiService()->renameFile(
                    $user,
                    $filePath,
                    $newPath,
                    'Renaming ' . $filePath->toAbsoluteString() . ' to ' . $newPath->toAbsoluteString()
                );

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_directory',
                        ['path' => $newPath->getParentPath()->toAbsoluteString()]
                    )
                );
            }
        } else {
            $form->setData(['newpath' => $filePath->toAbsoluteString()]);
        }

        return $this->render(
            'DdrGitkiBaseBundle:File:rename.html.twig',
            ['form' => $form->createView(), 'path' => $filePath]
        );
    }

    /**
     * @param File $file
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getContents(File $file)
    {
        $level = error_reporting(0);
        $content = file_get_contents($file->getPathname());
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }

        return $content;
    }
}
