<?php namespace BB\Helpers;

use BB\Exceptions\UserImageFailedException;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UserImage
{

    public function __construct()
    {

    }

    public function uploadPhoto($userId, $filePath, $newImage = false)
    {
        $tmpFilePath = storage_path('app') . '/' . $userId . '.png';
        $tmpFilePathThumb = storage_path('app') . '/' . $userId . '-thumb.png';


        //Generate the thumbnail and larger image
        Image::make($filePath)->fit(500)->save($tmpFilePath);
        Image::make($filePath)->fit(200)->save($tmpFilePathThumb);

        if ($newImage) {
            $newFilename      = \App::environment() . '/user-photo/' . md5($userId) . '-new.png';
            $newThumbFilename = \App::environment() . '/user-photo/' . md5($userId) . '-thumb-new.png';
        } else {
            $newFilename      = \App::environment() . '/user-photo/' . md5($userId) . '.png';
            $newThumbFilename = \App::environment() . '/user-photo/' . md5($userId) . '-thumb.png';
        }

        Storage::put($newFilename, file_get_contents($tmpFilePath), 'public');
        Storage::put($newThumbFilename, file_get_contents($tmpFilePathThumb), 'public');

        \File::delete($tmpFilePath);
        \File::delete($tmpFilePathThumb);
    }

    /**
     * Delete an old profile image and replace it with a new one.
     * @param $userId
     */
    public function approveNewImage($userId)
    {

        $sourceFilename      = \App::environment() . '/user-photo/' . md5($userId) . '-new.png';
        $sourceThumbFilename = \App::environment() . '/user-photo/' . md5($userId) . '-thumb-new.png';

        $targetFilename      = \App::environment() . '/user-photo/' . md5($userId) . '.png';
        $targetThumbFilename = \App::environment() . '/user-photo/' . md5($userId) . '-thumb.png';


        if (Storage::exists($targetFilename)) {
            Storage::delete($targetFilename);
        }
        if (Storage::exists($targetThumbFilename)) {
            Storage::delete($targetThumbFilename);
        }

        Storage::move($sourceFilename, $targetFilename);
        Storage::move($sourceThumbFilename, $targetThumbFilename);

        if (Storage::exists($sourceFilename)) {
            Storage::delete($sourceFilename);
        }
        if (Storage::exists($sourceThumbFilename)) {
            Storage::delete($sourceThumbFilename);
        }

    }

    public static function imageUrl($userId)
    {
        return 'https://members.hacman.org.uk' . env('S3_BUCKET') . '/' . \App::environment() . '/user-photo/' . md5($userId) . '.png';
    }


    public static function thumbnailUrl($userId)
    {
        return 'https://members.hacman.org.uk' . env('S3_BUCKET') . '/' . \App::environment() . '/user-photo/' . md5($userId) . '-thumb.png';
    }

    public static function newThumbnailUrl($userId)
    {
        return 'https://members.hacman.org.uk' . env('S3_BUCKET') . '/' . \App::environment() . '/user-photo/' . md5($userId) . '-thumb-new.png';
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