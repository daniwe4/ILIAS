<?php

declare(strict_types=1);

namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\HTTP\Services;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class XSendfile
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runInSeparateProcess
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class XSendfileTest extends TestCase
{
    /**
     * @var Services|\PHPUnit\Framework\MockObject\MockObject
     */
    public Services $httpServiceMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->httpServiceMock = $this->getMockBuilder(Services::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
    }

    /**
     * @Test
     */
    public function testSendFileWithXSendHeaderWhichShouldSucceed(): void
    {
        $this->markTestSkipped("This triggers 'The ILIAS\HTTP\Services::request method is deprecated (Please use \$this->wrapper() in most cases.).'. Please fix usage side.");

        $expectedHeader = 'X-Sendfile';
        $filePath = __FILE__;

        $response = $this->getMockBuilder(ResponseInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $response->expects($this->once())
                 ->method('withHeader')
                 ->with($expectedHeader, $filePath)
                 ->willReturnSelf();

        $this->httpServiceMock->expects($this->once())
                              ->method('response')
                              ->willReturn($response);

        $this->httpServiceMock->expects($this->once())
                              ->method('saveResponse')
                              ->with($response);

        $this->httpServiceMock->expects($this->once())
                              ->method('sendResponse');

        $fileDeliveryType = new XSendfile($this->httpServiceMock);
        $fileDeliveryType->deliver($filePath, false);
    }
}
