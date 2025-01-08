<?php

namespace App\Tests\Entity;

use App\Entity\Enum\TaskStatus;
use App\Entity\Task;
use App\Entity\Worker;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTaskInitialization(): void
    {
        $task = new Task();

        $this->assertNull($task->getId());
        $this->assertEquals(TaskStatus::NEW, $task->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());
        $this->assertNull($task->getName());
        $this->assertNull($task->getWorker());
    }

    public function testSetter(): void
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
