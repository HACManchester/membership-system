<?php namespace BB\Http\Controllers;

use BB\Helpers\StatsHelper;
use BB\Repo\StatRepository;

class StatsController extends Controller
{

    protected $layout = 'layouts.main';

    private $userRepository;
    private $statRepository;

    function __construct(
        \BB\Repo\UserRepository $userRepository, 
        \BB\Repo\StatRepository $statRepository
        )
    {
        $this->userRepository = $userRepository;
        $this->statRepository = $statRepository;
    }

    private function reduceArray($data, $key){
        $toReturn = array_reduce($data, function ($acc, $d) use ($key){
            if ($d['label'] != $key) {
                return $acc;
            }

            return array_merge($acc, [[ "date" => $d['date'], "value" => $d['value'] ]]);
        }, []);

        usort($toReturn, function($a, $b){
            return strtotime($a["date"]) > strtotime($b["date"]);
        });

        return $toReturn;
    }

    public function history(){
        $start = strtotime(date("Y-m-d", strtotime("-7 day")));
        $rawdata = $this->statRepository->getCategoryDates('membercount', $start, date("Y-m-d"));
        
        $memberCount = $this->reduceArray($rawdata, 'members');
        $join = $this->reduceArray($rawdata, 'join');
        $left = $this->reduceArray($rawdata, 'left');

        $graph = [["Date", "Total", "Joined", "Left"]];
        foreach($memberCount as $k => $v) {
                array_push($graph,[
                    $memberCount[$k]['date'],
                    (int)$memberCount[$k]['value'],
                    (int)$join[$k]['value'],
                    (int)$left[$k]['value']
                ]);
        }

        return \View::make('stats.history')
            ->with('historyData', $graph);
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

        $otherIncome = 500;
        $electric = 1000; // roughly as of Feb/Apr 2023. Need to see how this grows/shrinks through the year
        $rent = 2344;
        $otherOutgoings = 500;
        $recommendedPayment = 25;
        // END OF HARDCODED VALUES

        $user = \Auth::user();
        $users = $this->userRepository->getActive();
        $expectedIncome = 0;
        $payingRecommendedOrAbove = 0;

        $paymentMethodsNumbers = [
            'gocardless'            => 0,
            'gocardless-variable'   => 0,
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
