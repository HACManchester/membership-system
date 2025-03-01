@php
$size = $size ?? 250;
$class = $class ?? 'profilePhoto';
$imageSrc = \BB\Helpers\UserImage::anonymous();

if ($profileData->profile_photo) {
    if (Auth::guest() && $profileData->profile_photo_private) {
        $imageSrc = \BB\Helpers\UserImage::anonymous();
    } elseif ((!Auth::guest() && !Auth::user()->shouldMemberSeeProtectedPhoto()) && $profileData->profile_photo_private) {
        $imageSrc = \BB\Helpers\UserImage::anonymous();
    } else {
        $imageSrc = \BB\Helpers\UserImage::thumbnailUrl($userHash);
    }
}
@endphp

<img src="{{ $imageSrc }}" width="{{ $size }}" height="{{ $size }}" class="{{ $class }}" loading="lazy" />
