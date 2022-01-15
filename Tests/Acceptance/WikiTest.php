<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WikiTest extends WebTestCase
{
    protected $environment = 'default';

    public function testBrowseRedirect(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $client->followRedirects(false);
        $crawler = $client->request(Request::METHOD_GET, '/browse/');
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertEquals('/browse/index.md', $client->getResponse()->headers->get('location'));
    }

    public function testHistory(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/history');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testExampleAFilenameWithSpaces(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/a%20filename%20with%20spaces.md');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testExampleLinkExample(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/link-example.md');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $link = $crawler->filter('a[href="./table-example.md"]');
        $this->assertCount(1, $link);
        $this->assertNull($link->attr('class'));

        $link = $crawler->filter('a[href="./missing.md"]');
        $this->assertCount(1, $link);
        $this->assertEquals('missing', $link->attr('class'));

        $link = $crawler->filter('a[href="./a%20filename%20with%20spaces.md"]');
        $this->assertCount(1, $link);
        $this->assertNull($link->attr('class'));
    }

    public function testExampleTableExample(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/table-example.md');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testExampleTocExample(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/toc-example.md');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testNonExistingFile(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/not-existing.md');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testNonExistingDirectoryWatcher(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/not-existing/');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testNonExistingDirectoryCommitter(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $client->followRedirects(false);
        $crawler = $client->request(
            Request::METHOD_GET,
            '/browse/examples/not-existing/',
            [],
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/examples/not-existing/index.md',
            $client->getResponse()->headers->get('location')
        );

        $crawler = $client->request(
            Request::METHOD_GET,
            $client->getResponse()->headers->get('location'),
            [],
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/examples/not-existing/index.md?action=edit',
            $client->getResponse()->headers->get('location')
        );
    }

    public function testMoveFile(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $client->setServerParameters(
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/link-example.md?action=move');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('form_submit')->form(
            [
                'form[directory]' => '/',
                'form[name]'      => 'newname.md',
            ]
        );
        $crawler = $client->submit($form);
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/',
            $client->getResponse()->headers->get('location')
        );

        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/link-example.md');
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        /* File does not exist, so we are redirected to edit action */
        $this->assertEquals(
            '/browse/examples/link-example.md?action=edit',
            $client->getResponse()->headers->get('location')
        );

        $crawler = $client->request(Request::METHOD_GET, '/browse/newname.md');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $crawler = $client->request(Request::METHOD_GET, '/browse/newname.md?action=history');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $historyEntries = $crawler->filter('.ddr-gitki-history-entry');
        $this->assertCount(1, $historyEntries);

        $this->assertEquals(
            'John Doe <johndoe@examle.com>',
            $historyEntries->eq(0)->filter('.ddr-gitki-history-committer')->text()
        );
        $this->assertEquals(
            'Moving /examples/link-example.md to /newname.md',
            trim($historyEntries->eq(0)->filter('.ddr-gitki-history-message')->text())
        );
    }

    public function testViewTextFile(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/textfile.txt');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = $crawler->filter('.ddr-gitki-text-content');
        $this->assertCount(1, $content);

        $this->assertEquals('This is a simple text file with some content.', $content->text());
    }

    public function testEditTextFileUnauthenticated(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/textfile.txt?action=edit');
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testEditTextFile(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $client->setServerParameters(
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/textfile.txt?action=edit');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('save')->form(
            [
                'text_edit[content]' => 'This is the changed content'
            ]
        );
        $crawler = $client->submit($form);
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/examples/textfile.txt',
            $client->getResponse()->headers->get('location')
        );

        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/textfile.txt');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = $crawler->filter('.ddr-gitki-text-content');
        $this->assertCount(1, $content);

        $this->assertEquals('This is the changed content', $content->text());

        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/textfile.txt?action=history');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $historyEntries = $crawler->filter('.ddr-gitki-history-entry');
        $this->assertCount(2, $historyEntries);

        $this->assertEquals(
            'John Doe <johndoe@examle.com>',
            $historyEntries->eq(0)->filter('.ddr-gitki-history-committer')->text()
        );
        $this->assertEquals(
            'Editing /examples/textfile.txt',
            trim($historyEntries->eq(0)->filter('.ddr-gitki-history-message')->text())
        );
    }

    public function testRemoveFileUnauthorized(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/textfile.txt?action=remove');
        echo $client->getResponse()->getContent();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testRemoveFile(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $client->setServerParameters(
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/textfile.txt?action=remove');
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /* File not found anymore so redirected to editing */
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/textfile.txt');
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/examples/textfile.txt?action=edit',
            $client->getResponse()->headers->get('location')
        );
    }

    public function testEditMarkdownFileUnauthenticated(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/index.md?action=edit');
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testEditMarkdownFile(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $client->setServerParameters(
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $crawler = $client->request(Request::METHOD_GET, '/browse/index.md?action=edit');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('save')->form(
            [
                'markdown_edit[content]' => 'This is the changed content'
            ]
        );
        $crawler = $client->submit($form);
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/index.md',
            $client->getResponse()->headers->get('location')
        );

        $crawler = $client->request(Request::METHOD_GET, '/browse/index.md');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = $crawler->filter('.markdown-html p');
        $this->assertCount(1, $content);

        $this->assertEquals('This is the changed content', $content->text());

        $crawler = $client->request(Request::METHOD_GET, '/browse/index.md?action=history');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $historyEntries = $crawler->filter('.ddr-gitki-history-entry');
        $this->assertCount(2, $historyEntries);

        $this->assertEquals(
            'John Doe <johndoe@examle.com>',
            $historyEntries->eq(0)->filter('.ddr-gitki-history-committer')->text()
        );
        $this->assertEquals(
            'Editing /index.md',
            trim($historyEntries->eq(0)->filter('.ddr-gitki-history-message')->text())
        );
    }

    public function testListDirectoryAction(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/examples/?action=list');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $subDirectories = $crawler->filter('.ddr-gitki-directory-subdirectories .list-group-item');
        $this->assertCount(1, $subDirectories);

        $files = $crawler->filter('.ddr-gitki-directory-files .list-group-item');
        $this->assertCount(5, $files);
    }

    public function testCreateSubdirectoryActionUnauthenticated(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/browse/?action=subdirectory.create');
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateSubdirectoryAction(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $client->setServerParameters(
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $crawler = $client->request(Request::METHOD_GET, '/browse/?action=subdirectory.create');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('subdirectory_create_submit')->form(
            [
                'subdirectory_create[dirname]' => 'subdir'
            ]
        );
        $crawler = $client->submit($form);
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/subdir/',
            $client->getResponse()->headers->get('location')
        );

        $crawler = $client->request(Request::METHOD_GET, '/browse/?action=list');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $subDirectories = $crawler->filter('.ddr-gitki-directory-subdirectories .list-group-item');
        $this->assertCount(2, $subDirectories);

        $files = $crawler->filter('.ddr-gitki-directory-files .list-group-item');
        $this->assertCount(1, $files);
    }

    public function testCannotEditLockedFile(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $client->setServerParameters(
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ]
        );
        $crawler = $client->request(Request::METHOD_GET, '/browse/index.md?action=edit');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $crawler = $client->request(Request::METHOD_GET, '/browse/index.md?action=holdlock');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->setServerParameters(
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $crawler = $client->request(Request::METHOD_GET, '/browse/index.md?action=edit');
        self::assertResponseStatusCodeSame(Response::HTTP_LOCKED);

        /* Cannot hold lock for different user */
        $crawler = $client->request(Request::METHOD_GET, '/browse/index.md?action=holdlock');
        self::assertResponseStatusCodeSame(Response::HTTP_LOCKED);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironment(): string
    {
        return 'default';
    }
}
