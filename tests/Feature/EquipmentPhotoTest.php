<?php

namespace Tests\Feature;

use BB\Entities\Equipment;
use BB\Entities\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The equipment photo upload runs through Intervention Image, psr7 streams and
 * a Flysystem write — all of which changed in the Laravel 9 upgrade (psr7 2.x,
 * Flysystem 3) — and had no test.
 */
class EquipmentPhotoTest extends TestCase
{
    /** @test */
    public function an_uploaded_photo_is_resized_stored_and_recorded_on_the_equipment()
    {
        Storage::fake('public');
        $admin = factory(User::class)->state('admin')->create();
        $equipment = factory(Equipment::class)->create();

        $this->actingAs($admin)
            ->post(route('equipment.photo.store', $equipment), [
                'photo' => UploadedFile::fake()->image('laser.jpg', 1600, 1200),
            ])
            ->assertRedirect(route('equipment.edit', $equipment));

        $photos = $equipment->fresh()->photos;
        $this->assertCount(1, $photos);

        $path = $equipment->getPhotoBasePath() . $photos[0]['path'];
        Storage::disk('public')->assertExists($path);

        // fit(1000) crops and resizes to a 1000x1000 square
        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame([1000, 1000], [$width, $height]);
    }

    /**
     * Flysystem 3 returns false from a failed write instead of throwing. The
     * configured disks set `throw`, so a failure still surfaces as an exception
     * (which addPhoto() turns into "image failed") rather than recording a
     * photo that was never written.
     *
     * @test
     */
    public function a_failed_write_to_the_configured_public_disk_throws()
    {
        $root = sys_get_temp_dir() . '/flysystem-throw-' . uniqid();
        $disk = Storage::build(array_merge(config('filesystems.disks.public'), ['root' => $root]));

        // A file can't be written over an existing directory, even as root. This
        // is an UnableToWriteFile — the failure `throw` governs (directory
        // creation failures throw regardless).
        mkdir($root . '/photo.png');

        $this->expectException(\League\Flysystem\UnableToWriteFile::class);

        $disk->put('photo.png', 'data');
    }
}
