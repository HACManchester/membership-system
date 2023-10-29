<?php

namespace BB\Http\Controllers;

use BB\Repo\InductionRepository;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /** @var InductionRepository */
    protected $inductionRepository;

    function __construct(InductionRepository $inductionRepository)
    {
        $this->inductionRepository = $inductionRepository;
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $threeMonths = $this->inductionRepository->getLeaderboard(InductionRepository::LEADERBOARD_THREE_MONTHS);
        $thisYear = $this->inductionRepository->getLeaderboard(InductionRepository::LEADERBOARD_YEAR);
        $lastYear = $this->inductionRepository->getLeaderboard(InductionRepository::LEADERBOARD_LAST_YEAR);
        $allTime = $this->inductionRepository->getLeaderboard(InductionRepository::LEADERBOARD_ALL_TIME);
        
        return view('leaderboard.index', compact('threeMonths', 'thisYear', 'lastYear', 'allTime'));
    }
}
