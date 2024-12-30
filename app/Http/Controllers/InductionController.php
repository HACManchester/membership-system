<?php

namespace BB\Http\Controllers;

use BB\Entities\Equipment;
use BB\Entities\Induction;
use BB\Entities\User;
use BB\Events\Inductions\InductionCompletedEvent;
use BB\Events\Inductions\InductionMarkedAsTrainerEvent;
use BB\Events\Inductions\InductionRequestedEvent;
use BB\Http\Requests\StoreInductionRequest;
use BB\Http\Requests\TrainInductionRequest;

class InductionController extends Controller
{
    public function store(Equipment $equipment, StoreInductionRequest $request)
    {
        $userId = $request->input('user_id', \Auth::user()->id);

        $induction = Induction::create([
            'user_id' => $userId,
            'key' => $equipment->induction_category,
        ]);

        \Event::dispatch(new InductionRequestedEvent($induction));

        return \Redirect::route('equipment.show', $equipment);
    }

    public function train(Equipment $equipment, Induction $induction, TrainInductionRequest $request)
    {
        $trainer = User::find($request->input('trainer_user_id'));

        $induction->update([
            'trained' => \Carbon\Carbon::now(),
            'trainer_user_id' => $trainer,
        ]);

        \Event::dispatch(new InductionCompletedEvent($induction));

        return \Redirect::route('equipment.show', $equipment);
    }

    public function untrain(Equipment $equipment, Induction $induction)
    {
        $this->authorize('untrain', $induction);

        $induction->update([
            'trained' => null,
            'trainer_user_id' => 0
        ]);

        return \Redirect::route('equipment.show', $equipment);
    }

    public function promote(Equipment $equipment, Induction $induction)
    {
        $this->authorize('promote', $induction);

        $induction->update([
            'is_trainer' => true
        ]);

        \Event::dispatch(new InductionMarkedAsTrainerEvent($induction));

        return \Redirect::route('equipment.show', $equipment);
    }

    public function demote(Equipment $equipment, Induction $induction)
    {
        $this->authorize('demote', $induction);

        $induction->update([
            'is_trainer' => false
        ]);

        return \Redirect::route('equipment.show', $equipment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Equipment $equipment, Induction $induction)
    {
        $this->authorize('delete', $induction);

        $induction->delete();

        return \Redirect::route('equipment.show', $equipment);
    }
}
