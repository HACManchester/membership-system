<?php namespace BB\Validators;

use BB\Helpers\MembershipPayments;

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
        'secondary_email'       => 'email|unique:users',
        'display_name'          => 'required|unique:users',
        'announce_name'         => 'max:150',
        'online_only'           => 'required|boolean',
        'password'              => 'required|min:8',
        'phone'                 => 'required_if:online_only,0|min:10',
        'address.line_1'        => 'required_if:online_only,0',
        'address.line_2'        => '',
        'address.line_3'        => '',
        'address.line_4'        => '',
        'address.postcode'      => 'required_if:online_only,0|postcode',
        'monthly_subscription'  => 'required_if:online_only,0|integer', // Min will be added in getValidationRules
        'emergency_contact'     => 'required_if:online_only,0',
        'rules_agreed'          => 'accepted',
    
    ];


    //During an update these rules will override the ones above
    protected $updateRules = [
        'email'                => 'required|email|unique:users,email,{id}',
        'secondary_email'      => 'email|unique:users,secondary_email,{id}',
        'password'             => 'min:8',
        'display_name'          => '',
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

        // Set minimum monthly subscription, being careful not to apply to updates
        if (!empty($rules['monthly_subscription'])) {
            $minPrice = intval(MembershipPayments::getMinimumPrice() / 100);
            $rules['monthly_subscription'] .= '|min:' . $minPrice;
        }

        return $rules;
    }
} 
