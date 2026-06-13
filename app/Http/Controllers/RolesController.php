<?php namespace BB\Http\Controllers;

use BB\Entities\Role;
use BB\Validators\RoleValidator;

class RolesController extends Controller
{
    /**
     * @var \BB\Repo\UserRepository
     */
    private $userRepository;
    /**
     * @var RoleValidator
     */
    private $roleValidator;

    function __construct(\BB\Repo\UserRepository $userRepository, RoleValidator $roleValidator)
    {
        $this->userRepository = $userRepository;
        $this->roleValidator = $roleValidator;
    }


    public function index()
    {
        $roles = Role::with('Users')->get();
        $memberList = $this->userRepository->getAllAsDropdown();
        return \View::make('roles.index')->with('roles', $roles)->with('memberList', $memberList);
    }


    public function create()
    {
        //
    }


    public function store()
    {

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {

    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id)
    {

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function update($id)
    {
        $role = Role::findOrFail($id);

        $formData = \Request::only(['description', 'title', 'email_public', 'email_private', 'slack_channel']);
        $this->roleValidator->validate($formData);

        $role->update($formData);

        return \Redirect::back();
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id)
    {
        
    }


}
