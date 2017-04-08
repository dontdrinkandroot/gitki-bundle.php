<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WikiTest extends FunctionalTest
{
    protected $environment = 'default';

    public function testBrowseRedirect()
    {
        $this->client->followRedirects(false);
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/');
        $this->assertStatusCode(Response::HTTP_FOUND);
        $this->assertEquals('/browse/index.md', $this->client->getResponse()->headers->get('location'));
    }

    public function testHistory()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/history');
        $this->assertStatusCode(Response::HTTP_OK);
    }

    public function testExampleAFilenameWithSpaces()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/a%20filename%20with%20spaces.md');
        $this->assertStatusCode(Response::HTTP_OK);
    }

    public function testExampleLinkExample()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/link-example.md');
        $this->assertStatusCode(Response::HTTP_OK);

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

    public function testExampleTableExample()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/table-example.md');
        $this->assertStatusCode(Response::HTTP_OK);
    }

    public function testExampleTocExample()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/toc-example.md');
        $this->assertStatusCode(Response::HTTP_OK);
    }

    public function testNonExistingFile()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/not-existing.md');
        $this->assertStatusCode(Response::HTTP_NOT_FOUND);
    }

    public function testNonExistingDirectoryWatcher()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/not-existing/');
        $this->assertStatusCode(Response::HTTP_NOT_FOUND);
    }

    public function testNonExistingDirectoryCommitter()
    {
        $this->client->followRedirects(false);
        $crawler = $this->client->request(
            Request::METHOD_GET,
            '/browse/examples/not-existing/',
            [],
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $this->assertStatusCode(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/examples/not-existing/index.md',
            $this->client->getResponse()->headers->get('location')
        );

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->client->getResponse()->headers->get('location'),
            [],
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $this->assertStatusCode(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/examples/not-existing/index.md?action=edit',
            $this->client->getResponse()->headers->get('location')
        );
    }

    public function testMoveFile()
    {
        $this->client->setServerParameters(
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/link-example.md?action=move');
        $this->assertStatusCode(Response::HTTP_OK);

        $form = $crawler->selectButton('form_move')->form(
            [
                'form[directory]' => '/',
                'form[name]'      => 'newname.md',
            ]
        );
        $crawler = $this->client->submit($form);
        $this->assertStatusCode(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/',
            $this->client->getResponse()->headers->get('location')
        );

        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/link-example.md');
        $this->assertStatusCode(Response::HTTP_FOUND);
        /* File does not exist, so we are redirected to edit action */
        $this->assertEquals(
            '/browse/examples/link-example.md?action=edit',
            $this->client->getResponse()->headers->get('location')
        );

        $crawler = $this->client->request(Request::METHOD_GET, '/browse/newname.md');
        $this->assertStatusCode(Response::HTTP_OK);

        $crawler = $this->client->request(Request::METHOD_GET, '/browse/newname.md?action=history');
        $this->assertStatusCode(Response::HTTP_OK);

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

    public function testViewTextFile()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/textfile.txt');
        $this->assertStatusCode(Response::HTTP_OK);

        $content = $crawler->filter('.ddr-gitki-text-content');
        $this->assertCount(1, $content);

        $this->assertEquals('This is a simple text file with some content.', $content->text());
    }

    public function testEditTextFileUnauthenticated()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/textfile.txt?action=edit');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

    public function testEditTextFile()
    {
        $this->client->setServerParameters(
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/textfile.txt?action=edit');
        $this->assertStatusCode(Response::HTTP_OK);

        $form = $crawler->selectButton('form_submit')->form(
            [
                'form[content]' => 'This is the changed content'
            ]
        );
        $crawler = $this->client->submit($form);
        $this->assertStatusCode(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/examples/textfile.txt',
            $this->client->getResponse()->headers->get('location')
        );

        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/textfile.txt');
        $this->assertStatusCode(Response::HTTP_OK);

        $content = $crawler->filter('.ddr-gitki-text-content');
        $this->assertCount(1, $content);

        $this->assertEquals('This is the changed content', $content->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/textfile.txt?action=history');
        $this->assertStatusCode(Response::HTTP_OK);

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

    public function testRemoveFileUnauthorized()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/textfile.txt?action=remove');
        echo $this->client->getResponse()->getContent();
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

    public function testRemoveFile()
    {
        $this->client->setServerParameters(
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/textfile.txt?action=remove');
        $this->assertStatusCode(Response::HTTP_FOUND);

        /* File not found anymore so redirected to editing */
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/examples/textfile.txt');
        $this->assertStatusCode(Response::HTTP_FOUND);
        $this->assertEquals(
            '/browse/examples/textfile.txt?action=edit',
            $this->client->getResponse()->headers->get('location')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironment(): string
    {
        return 'default';
    }
}
