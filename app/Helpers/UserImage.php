<?php namespace BB\Helpers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UserImage
{

    /** @var \Illuminate\Filesystem\FilesystemAdapter */
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('public');
    }

    public function uploadPhoto($userId, $filePath, $newImage = false)
    {
        $userHash = md5($userId);

        // Resize the photo to 500px
        $this->disk->put(
            sprintf('user-photo/%s%s.png', $userHash, $newImage ? '-new' : ''),
            Image::make($filePath)->fit(500)->stream('png')
        );

        // And make a 200px thumbnail
        $this->disk->put(
            sprintf('user-photo/%s-thumb%s.png', $userHash, $newImage ? '-new' : ''),
            Image::make($filePath)->fit(200)->stream('png')
        );
    }

    /**
     * Delete an old profile image and replace it with a new one.
     * @param $userId
     */
    public function approveNewImage($userId)
    {
        $userHash = md5($userId);

        foreach (['', '-thumb'] as $type) {
            $sourceFilename = sprintf('user-photo/%s%s-new.png', $userHash, $type);
            $targetFilename = sprintf('user-photo/%s%s.png', $userHash, $type);

            if ($this->disk->exists($targetFilename)) {
                $this->disk->delete($targetFilename);
            }
            $this->disk->move($sourceFilename, $targetFilename);
        }
    }

    public static function imageUrl($userId)
    {
        return asset(sprintf('storage/user-photo/%s.png', md5($userId)));
    }    
    
    public static function thumbnailUrl($userId)
    {
        return asset(sprintf('storage/user-photo/%s-thumb.png', md5($userId)));
    }
    
    public static function newThumbnailUrl($userId)
    {
        return asset(sprintf('storage/user-photo/%s-thumb-new.png', md5($userId)));
    }

    public static function gravatar($email)
    {
        return 'https://www.gravatar.com/avatar/' . md5($email) . '?s=200&d=mm';
    }

    public static function anonymous()
    {
        return 'https://members.hacman.org.uk/local/user-photo/d169b05e6c7c9c7933537436d47b4f3d-thumb.png';
    }

} 