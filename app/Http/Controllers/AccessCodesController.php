<?php namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Entities\AccessCode;
use Response;
use View;

class AccessCodesController extends Controller
{

    /**
     * Show the codes
     *
     * @return Response
     */
    public function show($id)
    {
        $user = User::findWithPermission($id);

        // Only active users may see the current codes
        if ($user->status == 'active') {
            $accessCodes = $this->accessCodes;
            return \View::make('access_codes.index')
                ->with('accessCodes', $accessCodes)
                ->with('user', $user);
        }

        return \Response::make('', 404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        //
    }

    public function getCounter($id)
    {
        $counter = $this->accessCodes->getById($id);
        return \Response::json([
            'counter'   => $counter,
        ], 200);
    }

    public function setCounter($id)
    {
        $data = \Request::only(['counter']);

        $maxCounter = $this->accessCodeRepository->updateCounter($id, $data['counter']);

        return \Response::json([
            'counter'   => $maxCounter,
        ], 200);
    }

}
