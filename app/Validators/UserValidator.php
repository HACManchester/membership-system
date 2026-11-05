<?php namespace BB\Validators;

use BB\Helpers\MembershipPayments;
use BB\Support\CurrentUser;

class UserValidator extends FormValidator
{

    /**
     * Validation rules
     *
     * @var array
     */
    protected $rules = [
        'given_name'            => 'required',
        'family_name'           => 'required',
        'email'                 => 'required|email|unique:users',
        'secondary_email'       => 'email|unique:users|nullable',
        'display_name'          => 'required|unique:users',
        'announce_name'         => 'nullable|max:150',
        'pronouns'              => '',
        'suppress_real_name'    => 'required|boolean',
        'online_only'           => 'required|boolean',
        'password'              => 'required|min:8',
        'phone'                 => 'required_if:online_only,0|min:10',
        'address.line_1'        => 'required_if:online_only,0',
        'address.line_2'        => '',
        'address.line_3'        => '',
        'address.line_4'        => '',
        'address.postcode'      => 'required_if:online_only,0|postcode|nullable',
        'monthly_subscription'  => 'required_if:online_only,0|numeric|decimal:0,2|min:0|max:5000|nullable', // Pounds, up to the pence migrations' limit; the minimum is added in getValidationRules
        'emergency_contact'     => 'required_if:online_only,0',
        'rules_agreed'          => 'accepted',
    
    ];


    //During an update these rules will override the ones above
    protected $updateRules = [
        'email'                => 'required|email|unique:users,email,{id}',
        'secondary_email'      => 'email|unique:users,secondary_email,{id}',
        'password'             => 'min:8',
        // Only admins may change it; for anyone else the controller drops the field
        'display_name'          => 'sometimes|required|string|unique:users,display_name,{id}',
        'monthly_subscription' => '',
        'rules_agreed'         => '',
    ];


    protected $adminOverride = [
        'password'          => 'min:8',
        'emergency_contact' => '',
        'phone'             => '',
    ];


    protected function getValidationRules(array $replacements = []) {
        $rules = parent::getValidationRules($replacements);


        // Set the minimum monthly subscription (in pounds, as the form takes, from the pence in
        // config), being careful not to apply it to updates. Admins creating a member may go
        // below it, as they can when changing a member's amount.
        if (!empty($rules['monthly_subscription']) && ! CurrentUser::find()?->isAdmin()) {
            $rules['monthly_subscription'] .= '|min:' . MembershipPayments::formatPounds(MembershipPayments::getMinimumPrice());
        }

        return $rules;
    }
} 
