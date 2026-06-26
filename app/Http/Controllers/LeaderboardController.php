<?php

namespace BB\Http\Controllers;

use BB\Repo\TrainingRecordRepository;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /** @var TrainingRecordRepository */
    protected $trainingRecordRepository;

    function __construct(TrainingRecordRepository $trainingRecordRepository)
    {
        $this->trainingRecordRepository = $trainingRecordRepository;
    }

    public function index(Request $request)
    {
        $threeMonths = $this->trainingRecordRepository->getLeaderboard(TrainingRecordRepository::LEADERBOARD_THREE_MONTHS);
        $thisYear = $this->trainingRecordRepository->getLeaderboard(TrainingRecordRepository::LEADERBOARD_YEAR);
        $lastYear = $this->trainingRecordRepository->getLeaderboard(TrainingRecordRepository::LEADERBOARD_LAST_YEAR);
        $allTime = $this->trainingRecordRepository->getLeaderboard(TrainingRecordRepository::LEADERBOARD_ALL_TIME);
        
        return view('leaderboard.index', compact('threeMonths', 'thisYear', 'lastYear', 'allTime'));
    }
}
