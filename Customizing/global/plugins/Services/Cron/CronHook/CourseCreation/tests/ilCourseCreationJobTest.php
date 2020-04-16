<?php

if (!class_exists(\ilCronJob::class)) {
    class ilCronJob
    {
    }
}
if (!class_exists(\ilCronJobResult::class)) {
    require_once(__DIR__ . "/class.ilCronJobResult.php");
}

require_once(__DIR__ . "/../classes/class.ilCourseCreationJob.php");

use CaT\Plugins\CourseCreation\RequestDB;
use CaT\Plugins\CourseCreation\SendMails;
use ILIAS\TMS\CourseCreation\Process;
use ILIAS\TMS\CourseCreation\Request;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

if (!class_exists(Request::class)) {
    require_once(__DIR__ . "/Request.php");
}

if (!class_exists(Process::class)) {
    require_once(__DIR__ . "/Process.php");
}

/**
 * @group needsInstalledILIAS
 */
class ilCourseCreationJobTest extends TestCase
{
    public function setUp() : void
    {
        $this->request_db = $this->createMock(RequestDB::class);
        $this->process = $this->createMock(Process::class);
        $this->send_mails = $this->createMock(SendMails::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tree = $this->createMock(ilTree::class);
        $this->job = $this->getMockBuilder(\ilCourseCreationJob::class)
            ->setMethods(["ping"])
            ->setConstructorArgs([$this->request_db, $this->process, $this->send_mails, $this->logger, $this->tree])
            ->getMock();
    }

    public function test_run()
    {
        $request1 = new Request(23, 1, "session1", 2, [], [], new \DateTime());
        $request2 = new Request(42, 3, "session1", 4, [], [], new \DateTime());

        $this->request_db
            ->expects($this->exactly(3))
            ->method("getNextDueRequest")
            ->will($this->onConsecutiveCalls(
                $request1,
                $request2,
                null
            ));

        $request1_processed = $request1->withTargetRefIdAndFinishedTS(5, new \DateTime());
        $request2_processed = $request2->withTargetRefIdAndFinishedTS(6, new \DateTime());
        $this->process
            ->expects($this->exactly(2))
            ->method("run")
            ->withConsecutive(
                [$request1],
                [$request2]
            )
            ->will($this->onConsecutiveCalls(
                $request1_processed,
                $request2_processed
            ));

        $this->request_db
            ->expects($this->exactly(2))
            ->method("update")
            ->withConsecutive(
                [$request1_processed],
                [$request2_processed]
            );

        $this->send_mails
            ->expects($this->exactly(2))
            ->method("sendSuccessMails")
            ->withConsecutive(
                [$request1_processed],
                [$request2_processed]
            );

        $this->job
            ->expects($this->exactly(2))
            ->method("ping");

        $result = $this->job->run();

        $this->assertInstanceOf(\ilCronJobResult::class, $result);
        $this->assertEquals(\ilCronJobResult::STATUS_OK, $result->getStatus());
    }

    public function test_run_fails()
    {
        $request1 = new Request(23, 1, "session1", 2, [], [], new \DateTime());

        $this->request_db
            ->expects($this->exactly(2))
            ->method("getNextDueRequest")
            ->will($this->onConsecutiveCalls(
                $request1,
                null
            ));

        $exception = new \Exception("EXCEPTION");
        $this->process
            ->expects($this->exactly(1))
            ->method("run")
            ->with($request1)
            ->will($this->throwException($exception));

        $request1_processed = $request1->withFinishedTS(new \DateTime());
        $this->request_db
            ->expects($this->exactly(1))
            ->method("update")
            ->with($request1_processed);

        $this->send_mails
            ->expects($this->exactly(1))
            ->method("sendFailMails")
            ->with($request1_processed, $exception);

        $this->logger
            ->expects($this->once())
            ->method("error");

        $this->job
            ->expects($this->exactly(1))
            ->method("ping");

        $result = $this->job->run();

        $this->assertInstanceOf(\ilCronJobResult::class, $result);
        $this->assertEquals(\ilCronJobResult::STATUS_OK, $result->getStatus());
    }
}
