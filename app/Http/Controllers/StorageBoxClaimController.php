<?php

namespace BB\Http\Controllers;

use Auth;
use BB\Entities\StorageBox;
use Illuminate\Http\Request;

class StorageBoxClaimController extends Controller
{
    public function update(Request $request, StorageBox $storageBox)
    {
        $this->authorize('claim', $storageBox);

        if ($storageBox->isClaimed()) {
            \FlashNotification::error("Storage box {$storageBox->location} has beeen claimed by somebody else.");
            return redirect()->back();
        }

        $storageBox->update([
            'user_id' => Auth::user()->id,
        ]);

        return redirect()->back();
    }

    public function destroy(StorageBox $storageBox)
    {
        $this->authorize('release', $storageBox);

        $storageBox->update([
            'user_id' => 0, // TODO: Be able to null these
        ]);

        return redirect()->back();
    }
}
