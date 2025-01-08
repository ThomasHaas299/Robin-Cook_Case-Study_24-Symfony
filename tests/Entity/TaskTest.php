<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\Enum\TaskStatus;
use App\Entity\Worker;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTaskInitialization()
    {
        $task = new Task();

        $this->assertNull($task->getId());
        $this->assertEquals(TaskStatus::NEW, $task->getStatus());
        $this->assertInstanceOf(DateTimeImmutable::class, $task->getCreatedAt());
        $this->assertNull($task->getName());
        $this->assertNull($task->getWorker());
    }

    public function testSetter()
    {
        $task = new Task();

        $task->setName('Test Task');
        $this->assertEquals('Test Task', $task->getName());

        $this->assertEquals(TaskStatus::NEW, $task->getStatus());
        $task->setStatus(TaskStatus::IN_PROGRESS);
        $this->assertEquals(TaskStatus::IN_PROGRESS, $task->getStatus());

        $this->assertNull($task->getWorker());
        $worker = new Worker();
        $task->setWorker($worker);
        $this->assertEquals($worker, $task->getWorker());

    }

}