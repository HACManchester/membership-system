<?php

namespace BB\Console\Commands;

use Illuminate\Console\Command;

class CheckMembershipStatus extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'bb:check-memberships';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the membership expiry dates and disable or email users';

    /**
     * @var \BB\Process\RecoverMemberships
     */
    private $recoverMemberships;

    /**
     * @var \BB\Process\CheckPaymentWarnings
     */
    private $checkPaymentWarnings;

    /**
     * @var \BB\Process\CheckLeavingUsers
     */
    private $checkLeavingUsers;

    /**
     * @var \BB\Process\CheckSuspendedUsers
     */
    private $checkSuspendedUsers;

    /**
     * Create a new command instance.
     *
     */
    public function __construct(
        \BB\Process\RecoverMemberships $recoverMemberships,
        \BB\Process\CheckPaymentWarnings $checkPaymentWarnings,
        \BB\Process\CheckSuspendedUsers $checkSuspendedUsers,
        \BB\Process\CheckLeavingUsers $checkLeavingUsers
    ) {
        parent::__construct();
        $this->recoverMemberships = $recoverMemberships;
        $this->checkPaymentWarnings = $checkPaymentWarnings;
        $this->checkSuspendedUsers = $checkSuspendedUsers;
        $this->checkLeavingUsers = $checkLeavingUsers;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Checking for memberships to recover (expires sooner than their last payment covers)");
        $this->recoverMemberships->run();

        // Run in descending order, so we can never take a member through all steps in one run (although this should not happen in normal operation)
        $this->info("Checking users with a leaving status");
        $this->checkLeavingUsers->run();

        $this->info("Checking users with suspended status");
        $this->checkSuspendedUsers->run();

        $this->info("Checking users with payment warnings");
        $this->checkPaymentWarnings->run();
    }
}
