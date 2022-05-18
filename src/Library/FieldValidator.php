<?php

namespace Rguj\Laracore\Library;




use Illuminate\Support\Facades\Validator;

use App\Rules\PreloadExists;
use App\Rules\MinMaxRegex;
use App\Rules\DateBetween;

use Exception;
use App\Libraries\AppFn;
use App\Libraries\DT;
use App\Libraries\WebClient;
use App\Libraries\CLHF;


class FieldValidator {

    public function __construct() {
        
    }
































    public function getRegisterStudent(array $inputs) {
        $FR = FieldRules::getRegisterStudent();
        
        $rules = [
            /*'lname' => ['required', 'string', 'min:'.$FR['lname']['min'], 'max:'.$FR['lname']['max'], 'regex:'.$FR['lname']['regex']],
            'fname' => ['required', 'string', 'min:'.$FR['lname']['min'], 'max:'.$FR['lname']['max'], 'regex:'.$FR['lname']['regex']],
            'mname' => ['present', 'string', 'min:'.$FR['lname']['min'], 'max:'.$FR['lname']['max'], 'regex:'.$FR['lname']['regex']],
            'namex' => ['present', 'string', 
                function($attribute, $value, $fail) use($inputs) {
                    $tbl_name = 'pl_namexs';
                    $col_name = 'namex';
                    $is_valid = 1;

                    $el = [$attribute, $value];
                    $db = [$tbl_name, $col_name, $is_valid];
                    $vld = CLHF::DB_PreloadExists($el, $db);
                    if(!$vld[0])
                        return $fail($vld[1]);
                }
            ],*/
            'email' => ['required', 'string', 'email', 'min:'.$FR['email']['min'], 'max:'.$FR['email']['max'], 'regex:'.$FR['email']['regex'], 'unique:users'],
            'password' => ['required', 'string', 'min:'.$FR['password']['min'], 'max:'.$FR['password']['max'], 'regex:'.$FR['password']['regex'], 'confirmed'],
        ];

        // CUSTOM ATTRIBS
        $custom_attributes = [

        ];

        // OVERRIDE ERROR MESSAGES
        $custom_messages = CLHF::VALIDATOR_OverrideErrorMessages($rules);

        $messages = $custom_messages;
        $attributes = $custom_attributes;
        $validator = Validator::make($inputs, $rules, $messages, $attributes);

        //dd($validator->fails());
        return $validator;
    }



















    
    public static function getSISPersonal(array $inputs) {
        // this doesn't view or manipulate `$request`
        // this doesn't validate the User ID

        $FR = FieldRules::getSISPersonal();

        $bool1 = (
            strtolower($inputs['birthsex']) === 'female' &&
            in_array(strtolower($inputs['civilstatus']), ['married', 'divorced', 'widowed'])
        );
        $maiden_lname_required = $bool1 ? 'required' : 'present';
        $maiden_fname_required = $bool1 ? 'required' : 'present';
        
        // DATA
        $data = $inputs;
        // RULES
        $rules = [
            'lname'          => ['required', 'string', new MinMaxRegex($FR['lname'])],
            'fname'          => ['required', 'string', new MinMaxRegex($FR['fname'])],
            'mname'          => ['present',  'string', new MinMaxRegex($FR['mname'])],
            'namex'          => ['present',  'string', new PreloadExists('pl_namexs', 'namex')],

            'birthsex'       => ['required', 'string', new PreloadExists('pl_birthsexes', 'birthsex')],
            'birthdate'      => ['required', 'string', new DateBetween($FR['birthdate'])],

            'birthplace_country'     => ['required', 'string', new PreloadExists('pl_countries', 'country')],
            'birthplace_ps'          => ['required', 'string', new MinMaxRegex($FR['birthplace_ps'])],
            'birthplace_cm'          => ['required', 'string', new MinMaxRegex($FR['birthplace_cm'])],
            
            'maiden_lname'   => [$maiden_lname_required, 'string', new MinMaxRegex($FR['maiden_lname'])],
            'maiden_fname'   => [$maiden_fname_required, 'string', new MinMaxRegex($FR['maiden_fname'])],
            'maiden_mname'   => ['present',  'string', new MinMaxRegex($FR['maiden_mname'])],
            'maiden_namex'   => ['present',  'string', new PreloadExists('pl_namexs', 'namex')],

            'nationality'    => ['required', 'string', new PreloadExists('pl_nationalities', 'nationality')],
            'religion'       => ['required', 'string', new PreloadExists('pl_religions', 'religion')],
            'civilstatus'    => ['required', 'string', new PreloadExists('pl_civilstatuses', 'civilstatus')],
            'mobilenumber'   => ['required', 'string', new MinMaxRegex($FR['mobilenumber'])],
            'email'          => ['required', 'string', new MinMaxRegex($FR['email'])],
            'disabilities'   => ['present',  'array',  'max:'.$FR['disabilities']['max']],
            'disabilities.*' => ['required', 'string', 'distinct', new MinMaxRegex($FR['disability'])],
        ];


        // CUSTOM ATTRIBS
        $custom_attributes = [];

        // OVERRIDE ERROR MESSAGES
        $custom_messages = CLHF::VALIDATOR_OverrideErrorMessages($rules);

        // FINAL PROCESS
        $messages = $custom_messages;
        $attributes = $custom_attributes;
        $validator = Validator::make($data, $rules, $messages, $attributes);

        return $validator;
    }

    /*public static function getSISPersonal_Phot($inputs) {
        // this doesn't view or manipulate `$request`
        // this doesn't validate the User ID

        //$FR = FieldRules::getSISPersonal();
        
        // DATA
        $data = $inputs;

        // RULES
        $rules = [
            'image' => ['required', 'file', 'mimes:jpeg,jpg', 'min:5', 'max:1000'],
        ];

        // CUSTOM ATTRIBS
        $custom_attributes = [];

        // OVERRIDE ERROR MESSAGES
        $custom_messages = CLHF::VALIDATOR_OverrideErrorMessages($rules);

        // FINAL PROCESS
        $messages = $custom_messages;
        $attributes = $custom_attributes;
        $validator = Validator::make($data, $rules, $messages, $attributes);

        return $validator;
    }*/



















    public static function getSISAddressCurr(array $inputs) {
        $FR = FieldRules::getSISAddrCurr();

        // DATA
        $data = $inputs;

        // RULES
        $rules = [
            // CURRENT
            'RC_lname'        => ['required', 'string', new MinMaxRegex($FR['RC_lname'])],
            'RC_fname'        => ['required', 'string', new MinMaxRegex($FR['RC_fname'])],
            'RC_mname'        => ['present',  'string', new MinMaxRegex($FR['RC_mname'])],
            'RC_namex'        => ['present',  'string', new PreloadExists('pl_namexs', 'namex')],
            'RC_relation'     => ['required', 'string', new MinMaxRegex($FR['RC_relation'])],
            'RC_mobilenumber' => ['required', 'string', new MinMaxRegex($FR['RC_mobilenumber'])],
            'RC_email'        => ['required', 'string', new MinMaxRegex($FR['RC_email'])],

            'RC_country'      => ['required', 'string', new PreloadExists('pl_countries', 'country')],
            'RC_ps'           => ['required', 'string', new MinMaxRegex($FR['RC_ps'])],
            'RC_cm'           => ['required', 'string', new MinMaxRegex($FR['RC_cm'])],
            'RC_place'        => ['required', 'string', new MinMaxRegex($FR['RC_place'])],

            'RC_zipcode'      => ['required', 'string', new MinMaxRegex($FR['RC_zipcode'])],
        ];

        // CUSTOM ATTRIBS
        $custom_attributes = [];

        // OVERRIDE ERROR MESSAGES
        $custom_messages = CLHF::VALIDATOR_OverrideErrorMessages($rules);

        // FINAL PROCESS
        $messages = $custom_messages;
        $attributes = $custom_attributes;
        $validator = Validator::make($data, $rules, $messages, $attributes);

        return $validator;
    }

    public static function getSISAddressEmgn(array $inputs) {
        $FR = FieldRules::getSISAddrEmgn();

        // DATA
        $data = $inputs;

        // RULES
        $rules = [
            // EMERGENCY
            'RE_lname'        => ['required', 'string', new MinMaxRegex($FR['RE_lname'])],
            'RE_fname'        => ['required', 'string', new MinMaxRegex($FR['RE_fname'])],
            'RE_mname'        => ['present',  'string', new MinMaxRegex($FR['RE_mname'])],
            'RE_namex'        => ['present',  'string', new PreloadExists('pl_namexs', 'namex')],
            'RE_relation'     => ['required', 'string', new MinMaxRegex($FR['RE_relation'])],
            'RE_mobilenumber' => ['required', 'string', new MinMaxRegex($FR['RE_mobilenumber'])],
            'RE_email'        => ['required', 'string', new MinMaxRegex($FR['RE_email'])],

            'RE_country'      => ['required', 'string', new PreloadExists('pl_countries', 'country')],
            'RE_ps'           => ['required', 'string', new MinMaxRegex($FR['RE_ps'])],
            'RE_cm'           => ['required', 'string', new MinMaxRegex($FR['RE_cm'])],
            'RE_place'        => ['required', 'string', new MinMaxRegex($FR['RE_place'])],

            'RE_zipcode'      => ['required', 'string', new MinMaxRegex($FR['RE_zipcode'])],
        ];

        // CUSTOM ATTRIBS
        $custom_attributes = [];

        // OVERRIDE ERROR MESSAGES
        $custom_messages = CLHF::VALIDATOR_OverrideErrorMessages($rules);

        // FINAL PROCESS
        $messages = $custom_messages;
        $attributes = $custom_attributes;
        $validator = Validator::make($data, $rules, $messages, $attributes);

        return $validator;
    }

    public static function getSISAddressHome(array $inputs) {
        $FR = FieldRules::getSISAddrHome();

        // DATA
        $data = $inputs;

        // RULES
        $rules = [
            // HOME
            'RH_country'      => ['required', 'string', new PreloadExists('pl_countries', 'country')],
            'RH_ps'           => ['required', 'string', new MinMaxRegex($FR['RH_ps'])],
            'RH_cm'           => ['required', 'string', new MinMaxRegex($FR['RH_cm'])],
            'RH_place'        => ['required', 'string', new MinMaxRegex($FR['RH_place'])],

            'RH_zipcode'      => ['required', 'string', new MinMaxRegex($FR['RH_zipcode'])],
        ];

        // CUSTOM ATTRIBS
        $custom_attributes = [];

        // OVERRIDE ERROR MESSAGES
        $custom_messages = CLHF::VALIDATOR_OverrideErrorMessages($rules);

        // FINAL PROCESS
        $messages = $custom_messages;
        $attributes = $custom_attributes;
        $validator = Validator::make($data, $rules, $messages, $attributes);

        return $validator;
    }

    

    













    public static function getSISFamilyPare(array $inputs) {
        $FR = FieldRules::getSISFamilyPare();

        // DATA
        $data = $inputs;

        $father_condition = ($inputs['f_none'] ?? '') === 'none' ? 'present' : 'required';

        // RULES
        $rules = [
            // FATHER
            'f_lname'         => [$father_condition, 'string', new MinMaxRegex($FR['f_lname'])],
            'f_fname'         => [$father_condition, 'string', new MinMaxRegex($FR['f_fname'])],
            'f_mname'         => ['present',  'string', new MinMaxRegex($FR['f_mname'])],
            'f_namex'         => ['present',  'string', new PreloadExists('pl_namexs', 'namex')],
            'f_occupation'    => [$father_condition, 'string', new MinMaxRegex($FR['f_occupation'])],
            //'f_mobilenumber'  => [$father_condition, 'string', new MinMaxRegex($FR['f_mobilenumber'])],
            'f_mobilenumber'  => ['present', 'string', new MinMaxRegex($FR['f_mobilenumber'])],

            // MOTHER
            'm_lname'         => ['required', 'string', new MinMaxRegex($FR['m_lname'])],
            'm_fname'         => ['required', 'string', new MinMaxRegex($FR['m_fname'])],
            'm_mname'         => ['present',  'string', new MinMaxRegex($FR['m_mname'])],
            'm_namex'         => ['present',  'string', new PreloadExists('pl_namexs', 'namex')],
            'm_occupation'    => ['required', 'string', new MinMaxRegex($FR['m_occupation'])],
            'm_mobilenumber'  => ['present', 'string', new MinMaxRegex($FR['m_mobilenumber'])],
        ];

        // CUSTOM ATTRIBS
        $custom_attributes = [];

        // OVERRIDE ERROR MESSAGES
        $custom_messages = CLHF::VALIDATOR_OverrideErrorMessages($rules);

        // FINAL PROCESS
        $messages = $custom_messages;
        $attributes = $custom_attributes;
        $validator = Validator::make($data, $rules, $messages, $attributes);

        return $validator;
    }

    public static function getSISFamilySibl(array $inputs) {
        $FR = FieldRules::getSISFamilySibl();

        // DATA
        $data = $inputs;

        // RULES
        $rules = [  // all wildcards
            'b_lname'         => ['required', 'string', new MinMaxRegex($FR['b_lname'])],
            'b_fname'         => ['required', 'string', new MinMaxRegex($FR['b_fname'])],
            'b_mname'         => ['present',  'string', new MinMaxRegex($FR['b_mname'])],
            'b_namex'         => ['present',  'string', new PreloadExists('pl_namexs', 'namex')],
            'b_birthsex'      => ['required', 'string', new PreloadExists('pl_birthsexes', 'birthsex')],
            'b_birthdate'     => ['required', 'string', new DateBetween($FR['b_birthdate'])],
            'b_coursedegree'  => ['required', 'string', new MinMaxRegex($FR['b_coursedegree'])],
            'b_occupation'    => ['required', 'string', new MinMaxRegex($FR['b_occupation'])],
        ];

        // CUSTOM ATTRIBS
        $custom_attributes = [];

        // OVERRIDE ERROR MESSAGES
        $custom_messages = CLHF::VALIDATOR_OverrideErrorMessages($rules);

        // FINAL PROCESS
        $messages = $custom_messages;
        $attributes = $custom_attributes;
        $validator = Validator::make($data, $rules, $messages, $attributes);

        return $validator;
    }

    public static function getSISFamilySpou(array $inputs) {
        $FR = FieldRules::getSISFamilySpou();

        // DATA
        $data = $inputs;

        // RULES
        $rules = [  // all wildcards
            's_lname'         => ['required', 'string', new MinMaxRegex($FR['s_lname'])],
            's_fname'         => ['required', 'string', new MinMaxRegex($FR['s_fname'])],
            's_mname'         => ['present',  'string', new MinMaxRegex($FR['s_mname'])],
            's_namex'         => ['present',  'string', new PreloadExists('pl_namexs', 'namex')],
            's_birthsex'      => ['required', 'string', new MinMaxRegex($FR['s_birthsex'])],
            's_birthdate'     => ['required', 'string', new DateBetween($FR['s_birthdate'])],
            's_occupation'    => ['required', 'string', new DateBetween($FR['s_occupation'])],
            's_mobilenumber'  => ['required', 'string', new DateBetween($FR['s_mobilenumber'])],
        ];

        // CUSTOM ATTRIBS
        $custom_attributes = [];

        // OVERRIDE ERROR MESSAGES
        $custom_messages = CLHF::VALIDATOR_OverrideErrorMessages($rules);

        // FINAL PROCESS
        $messages = $custom_messages;
        $attributes = $custom_attributes;
        $validator = Validator::make($data, $rules, $messages, $attributes);

        return $validator;
    }













}


