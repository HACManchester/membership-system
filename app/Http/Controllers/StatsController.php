<?php namespace BB\Http\Controllers;

use BB\Helpers\StatsHelper;
use BB\Repo\StatRepository;

class StatsController extends Controller
{

    protected $layout = 'layouts.main';

    private $userRepository;
    private $activityRepository;
    private $statRepository;

    function __construct(
        \BB\Repo\UserRepository $userRepository, 
        \BB\Repo\ActivityRepository $activityRepository,
        \BB\Repo\StatRepository $statRepository
        )
    {
        $this->userRepository = $userRepository;
        $this->activityRepository = $activityRepository;
        $this->statRepository = $statRepository;
    }

    private function reduceArray($data, $key){
        $arr = array();
        foreach($data as $d){
            array_push($arr, array(
                $d['date'],
                $d['label'],
                $d['value'], 
            ));
        }

        return array_reduce($arr, function ($acc, $d) {
            if ($d['label'] != $key) {
                return $acc;
            }

            return array_merge($acc, [ $d['date'], $d['value'] ]);
        }, []);
    }

    public function history(){
        $start = strtotime(date("Y-m-d", strtotime("-7 day")));
        $rawdata = $this->statRepository->getCategoryDates('membercount', $start, date("Y-m-d"));
        
        $memberCount = $this->reduceArray($rawdata, 'members');
        $join = $this->reduceArray($rawdata, 'join');
        $left = $this->reduceArray($rawdata, 'left');

        $graph = array(
            array("Total", "Joined", "Left"),
            array_merge(null, $memberCount, $join, $left)
        );

        return \View::make('stats.history')
            ->with('historyData', $data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        /**
         * MANUAL HARDCODED VALUES HERE
         */

        $otherIncome = 0;
        $electric = 280;
        $rent = 1940;
        $otherOutgoings = 200;
        $recommendedPayment = 25;
        // END OF HARDCODED VALUES

        $user = \Auth::user();
        $users = $this->userRepository->getActive();
        $expectedIncome = 0;
        $payingRecommendedOrAbove = 0;

        $paymentMethodsNumbers = [
            'gocardless'            => 0,
            'gocardless-variable'   => 0,
            'paypal'                => 0,
            'standing-order'        => 0
        ];
        foreach ($users as $user) {
            $expectedIncome = $expectedIncome + $user->monthly_subscription;
            
            if($user->monthly_subscription >= $recommendedPayment){
                $payingRecommendedOrAbove += 1;
            }

            if (isset($paymentMethodsNumbers[$user->payment_method])) {
                $paymentMethodsNumbers[$user->payment_method]++;
            }
        }

        $paymentMethods = [
            [
                'Payment Method', 'Number'
            ],
            [
                'Direct Debit', $paymentMethodsNumbers['gocardless'] + $paymentMethodsNumbers['gocardless-variable']
            ],
            [
                'PayPal', $paymentMethodsNumbers['paypal']
            ],
            [
                'Standing Order', $paymentMethodsNumbers['standing-order']
            ]
        ];

        //Fetch the users amounts and bucket them
        $averageMonthlyAmount = 0;
        $numPayingUsers = 0;
        $monthlyAmounts = array_fill_keys(range(5, 50, 5), 0);
        foreach ($users as $user) {
            if (isset($monthlyAmounts[(int)StatsHelper::roundToNearest($user->monthly_subscription)])) {
                $monthlyAmounts[(int)StatsHelper::roundToNearest($user->monthly_subscription)]++;
            }
            if ($user->monthly_subscription > 0) {
                $averageMonthlyAmount = $averageMonthlyAmount + $user->monthly_subscription;
                $numPayingUsers++;
            }
        }

        $averageMonthlyAmount = $averageMonthlyAmount / $numPayingUsers;

        //Remove the higher empty amounts
        $i = 50;
        while ($i >= 0) {
            if (isset($monthlyAmounts[$i]) && empty($monthlyAmounts[$i])) {
                unset($monthlyAmounts[$i]);
            } else {
                break;
            }
            $i = $i - 5;
        }

        //Format the data into the chart format
        $monthlyAmountsData = [];
        $monthlyAmountsData[] = ['Amount', 'Number of Members', (object)['role'=> 'annotation']];
        foreach ($monthlyAmounts as $amount => $numUsers) {
            $monthlyAmountsData[] = ['£' . $amount, $numUsers, $numUsers];
        }


        //Number of Users
        $numMembers = count($users);


        //door access logs
        $logEntrys = $this->activityRepository->activeUsersForPeriod(\Carbon\Carbon::now()->subMonth(), \Carbon\Carbon::now());
        $userArray = [];
        foreach ($logEntrys as $entry) {
            $userArray[] = $entry->user_id;
        }
        $numActiveUsers = count(array_unique($userArray));

        $logEntrys = $this->activityRepository->activeUsersForPeriod(\Carbon\Carbon::now()->subMonths(3), \Carbon\Carbon::now());
        $userArray = [];
        foreach ($logEntrys as $entry) {
            $userArray[] = $entry->user_id;
        }
        $numActiveUsersQuarter = count(array_unique($userArray));

        return \View::make('stats.index')
            ->with('user', $user)
            ->with('expectedIncome', $expectedIncome)
            ->with('otherIncome', $otherIncome)
            ->with('rent', $rent)
            ->with('electric', $electric)
            ->with('otherOutgoings', $otherOutgoings)
            ->with('totalIncome', $otherIncome + $expectedIncome)
            ->with('totalOutgoings', $rent + $electric + $otherOutgoings)
            ->with('averageMonthlyAmount', round($averageMonthlyAmount))
            ->with('numMembers', $numMembers)
            ->with('recommendedPayment', $recommendedPayment)
            ->with('payingRecommendedOrAbove', $payingRecommendedOrAbove)
            ->with('numActiveUsers', $numActiveUsers)
            ->with('numActiveUsersQuarter', $numActiveUsersQuarter)
            ->with('paymentMethods', $paymentMethods)
            ->with('monthlyAmountsData', $monthlyAmountsData);
    }

    public function ddSwitch()
    {
        $users = $this->userRepository->getActive();
        $paymentMethodsNumbers = [
            'gocardless'            => 0,
            'gocardless-variable'   => 0,
        ];
        foreach ($users as $user) {
            if (isset($paymentMethodsNumbers[$user->payment_method])) {
                $paymentMethodsNumbers[$user->payment_method]++;
            }
        }
        $paymentMethods = [
            [
                'GoCardless Type', 'Number'
            ],
            [
                'Fixed', $paymentMethodsNumbers['gocardless']
            ],
            [
                'Variable', $paymentMethodsNumbers['gocardless-variable']
            ]
        ];

        return \View::make('stats.dd-switch')
            ->with('paymentMethods', $paymentMethods);
    }

}
