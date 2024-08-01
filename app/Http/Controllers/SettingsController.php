<?php namespace BB\Http\Controllers;

use BB\Entities\Settings;

class SettingsController extends Controller
{

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function update()
	{
        $input = \Request::only('key', 'value');

        Settings::change($input['key'], $input['value']);

        \FlashNotification::success("Setting updated");
        return redirect()->back()->withInput();
	}

}
