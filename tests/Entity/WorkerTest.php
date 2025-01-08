<?php

namespace App\Tests\Entity;

use App\Entity\Worker;
use PHPUnit\Framework\TestCase;

class WorkerTest extends TestCase
{
    public function testWorkerInitialization()
    {
        $worker = new Worker();
        $this->assertNotEmpty($worker->getAccessToken());
        $this->assertFalse($worker->isDisabled());
        $this->assertEquals(['ROLE_WORKER'], $worker->getRoles());

        $reflection = new \ReflectionClass($worker);
        $property = $reflection->getProperty('id');
        $property->setValue($worker, 'test-id');
        $this->assertEquals('test-id', $worker->getUserIdentifier());

    }

}
