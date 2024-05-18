<?php

use BB\Entities\StorageBox;
use BB\Repo\StorageBoxRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class StorageBoxRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetMemberBox()
    {
        $storageBox = factory(StorageBox::class)->create(['user_id' => 123]);

        $result = (new StorageBoxRepository())->getMemberBox(123);

        $this->assertEquals($storageBox->id, $result->id);
    }

    public function testGetMemberBoxes()
    {
        factory(StorageBox::class)->create();
        $box1 = factory(StorageBox::class)->create(['user_id' => 123]);
        $box2 = factory(StorageBox::class)->create(['user_id' => 123]);

        $result = (new StorageBoxRepository())->getMemberBoxes(123);

        $this->assertCount(2, $result);
        $this->assertEquals($box1->id, $result[0]->id);
        $this->assertEquals($box2->id, $result[1]->id);
    }

    public function testGetAll()
    {
        factory(StorageBox::class)->create();
        $box1 = factory(StorageBox::class)->create(['user_id' => 123]);
        $box2 = factory(StorageBox::class)->create(['user_id' => 123]);

        $result = (new StorageBoxRepository())->getAll(123);

        $this->assertCount(3, $result);
    }

    public function testNumAvailableBoxes()
    {
        factory(StorageBox::class)->create();
        factory(StorageBox::class)->create();
        $box1 = factory(StorageBox::class)->create(['user_id' => 123]);
        $box2 = factory(StorageBox::class)->create(['user_id' => 123]);

        $result = (new StorageBoxRepository())->numAvailableBoxes();

        $this->assertEquals(2, $result);
    }
}
