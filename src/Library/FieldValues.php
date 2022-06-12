<?php

namespace Rguj\Laracore\Library;



use Illuminate\Support\Str;
use Illuminate\Support\Arr;


use Exception;
use Rguj\Laracore\Library\AppFn;
use Rguj\Laracore\Library\CLHF;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\StorageAccess;
use Rguj\Laracore\Library\WebClient;

use App\Http\Controllers\Student\LinkController as StudentLink;

class FieldValues {


    public function __construct() {
        
    }



























    public static function getRegisterStudent() {
        $form_defaults = [
            /*'lname' => '',
            'fname' => '',
            'mname' => '',
            'namex' => '',*/
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ];
        return $form_defaults;
    }

    public static function getEmailVerification() {
        $FR_App = FieldRules::getGeneral();
        $DATA = [];

        $DATA['code'] = '';

        return $DATA;
    }




    public static function getSISUserLink($user_id, bool $with_data=false) {
        /**
         * PROCESS:
         *      exists(IOPS) > exists(ADMISSION) > exists(AUTOMATE)
         */

        //$__Link = new \app\Http\Controllers\Student\Link();
        $ref_types = StudentLink::$reference_types;

        $output = $data = [
            //'is_valid' => false,     // overall evaluation
            'iops' => [
                'exists' => false,
                'user_id' => 0,
                'student_id' => 0,   // (int) table index only
            ],
            'automate' => [
                'id_num' => 0,       // student ID #
                'exists' => false,   // simple lookup
                'is_valid' => false, // comprehensive lookup
                'created_at' => '',  // created_at in IOPS
                'data' => [],
            ],
            'admission' => [
                'id_num' => 0,       // application ID #
                'exists' => false,   // simple lookup
                'is_valid' => false, // comprehensive lookup
                'created_at' => '',  // created_at in IOPS
                'data' => [],
            ],
        ];

        try {
            // CHECK USER LINK
            $arr1 = CLHF::DB_stored_procedure('mysql', 'stud_userlink', [$user_id, 2], true)[0] ?? [];  // INNER JOIN
            if(empty($arr1))
                throw new exception('User link not found');

            $data['iops']['user_id'] = $arr1['user_id'];
            $data['iops']['student_id'] = $arr1['student_id'];
            $data['iops']['exists'] = true;
            $stud_id_num = $arr1['student_id_num'];
            $appl_id_num = $arr1['application_id_num'];

            if(StudentLink::ReferenceIDType($stud_id_num) === $ref_types[0]) {  // automate
                $lookup = CLHF::DB_select_arr('pit_sa', 'SELECT * FROM dbo.USER_TABLE WHERE dbo.USER_TABLE.ID_NUMBER = ?', [$stud_id_num]);
                if(!empty($lookup)) {
                    $lookup2 = StudentLink::SCLATM_StudentDataPrevSem($stud_id_num);
                    $data['automate']['exists'] = true;
                    $data['automate']['id_num'] = $stud_id_num;
                    $data['automate']['created_at'] = DT::STR_TryParse($arr1['created_at_stud'] ?? '');
                    $data['automate']['is_valid'] = !empty($lookup2);
                    $data['automate']['data'] = $with_data ? $lookup2 : $data['automate']['data'];
                }
            }
            
            if(StudentLink::ReferenceIDType($appl_id_num) === $ref_types[1]) {  // admission
                $lookup = CLHF::DB_select_arr('admission', 'SELECT * FROM appl_admission WHERE appl_admission.application_no = ?', [$appl_id_num]);
                if(!empty($lookup)) {
                    $lookup2 = StudentLink::STDADM_StudentData($appl_id_num);
                    $data['admission']['exists'] = true;
                    $data['admission']['id_num'] = $appl_id_num;
                    $data['admission']['created_at'] = DT::STR_TryParse($arr1['created_at_appl'] ?? '');
                    $data['admission']['is_valid'] = !empty($lookup2);
                    $data['admission']['data'] = $with_data ? $lookup2 : $data['admission']['data'];
                }
            }
            //$data['is_valid'] = ($data['iops']['exists']);
            $output = $data;

        } catch(\Exception $ex) {
            //dd($ex->getMessage());
        }
        return $output;
    }

    public static function getSISPersonal($student_id) {
        $output = [];
        try {
            // check student
            if(StudentLink::StudentExists($student_id, 'id') !== true)
                throw new Exception('Student not found');
            
            $arr1 = CLHF::DB_stored_procedure('mysql', 'stud_pers_deta', [$student_id], true)[0];
            //dd($arr1);
            $output = [
                'name' => [
                    'last' => [$arr1['lname_id'], $arr1['lname']],
                    'first' => [$arr1['fname_id'], $arr1['fname']],
                    'middle' => [$arr1['mname_id'], $arr1['mname']],
                    'extension' => [$arr1['namex_id'], $arr1['namex']],
                ],
                'maiden_name' => [
                    'last' => [$arr1['maiden_lname_id'], $arr1['maiden_lname']],
                    'first' => [$arr1['maiden_fname_id'], $arr1['maiden_fname']],
                    'middle' => [$arr1['maiden_mname_id'], $arr1['maiden_mname']],
                    'extension' => [$arr1['maiden_namex_id'], $arr1['maiden_namex']],
                ],
                'birth' => [
                    'sex' => [$arr1['birthsex_id'], $arr1['birthsex']],
                    'date' => [$arr1['birthdate_id'], $arr1['birthdate']],

                    'place' => [
                        'country' => [$arr1['birthplace_country_id'], $arr1['birthplace_country']],
                        //'ps_type' => [$arr1['birthplace_ps_type_id'], $arr1['birthplace_ps_type']],
                        'ps' => [$arr1['birthplace_ps_id'], $arr1['birthplace_ps']],
                        //'cm_type' => [$arr1['birthplace_cm_type_id'], $arr1['birthplace_cm_type']],
                        'cm' => [$arr1['birthplace_cm_id'], $arr1['birthplace_cm']],
                    ],

                    'nationality' => [$arr1['nationality_id'], $arr1['nationality']],
                ],
                'religion' => [$arr1['religion_id'], $arr1['religion']],
                'civilstatus' => [$arr1['civilstatus_id'], $arr1['civilstatus']],
                'mobilenumber' => [$arr1['mobilenumber_id'], $arr1['mobilenumber']],
                'email' => [$arr1['email_id'], $arr1['email']],//$ud1['email'] ?? '',
            ];
        } catch(\Exception $ex) {
            //dd($ex->getMessage());
        }
        //dd($output);
        return $output;
    }

    public static function getSISPersonal2($student_id) {
        $UD1 = CLHF::AUTH_UserData($student_id);
        $UD2 = SELF::getSISPersonal($student_id);  // User Data
        $UD3 = SELF::getSISDisabilities($student_id, 2);  // strings

        //dd($UD2);
        $FormDefaults = [
            'lname'          => $UD2['name']['last'][1] ?? '',
            'fname'          => $UD2['name']['first'][1] ?? '',
            'mname'          => $UD2['name']['middle'][1] ?? '',
            'namex'          => $UD2['name']['extension'][1] ?? '',
            
            'maiden_lname'   => $UD2['maiden_name']['last'][1] ?? '',
            'maiden_fname'   => $UD2['maiden_name']['first'][1] ?? '',
            'maiden_mname'   => $UD2['maiden_name']['middle'][1] ?? '',
            'maiden_namex'   => $UD2['maiden_name']['extension'][1] ?? '',

            'birthsex'       => $UD2['birth']['sex'][1] ?? '',
            'birthdate'      => $UD2['birth']['date'][1] ?? '',
            //'birthdate'      => '2020-01-12 12:01:50.000111',

            'birthplace_country'   => $UD2['birth']['place']['country'][1] ?? '',
            //'birthplace_ps_type'   => $UD2['birth']['place']['ps_type'][1] ?? '',
            'birthplace_ps'        => $UD2['birth']['place']['ps'][1] ?? '',
            //'birthplace_cm_type'   => $UD2['birth']['place']['cm_type'][1] ?? '',
            'birthplace_cm'        => $UD2['birth']['place']['cm'][1] ?? '',

            'nationality'    => $UD2['birth']['nationality'][1] ?? '',
            'religion'       => $UD2['religion'][1] ?? '',
            'civilstatus'    => $UD2['civilstatus'][1] ?? '',
            //'email'          => $UD1['email'] ?? '',  // email read only
            'email'          => $UD2['email'][1] ?? '',  // email read only
            'mobilenumber'   => $UD2['mobilenumber'][1] ?? '',
            'disabilities'   => $UD3,
        ];

        return $FormDefaults;
    }

    public static function getSISDisabilities($student_id, int $mode=0) {
        /*
            @param $string_only ? array_column(1) : array()
        */
        $modes = [0, 1, 2];
        if(!in_array($mode, $modes))
            throw new exception('$mode is invalid');
        $output = [];
        try {
            $arr1 = CLHF::DB_stored_procedure('mysql', 'stud_pers_disa', [$student_id], true);
            if($mode === 1)
                $output = array_column($arr1, 'disability_id');
            else if($mode === 2)
                $output = array_column($arr1, 'disability');
            else
                $output = $arr1;
        } catch(\Exception $ex) {}
        return $output;
    }

    public static function getSISAddrCurr($student_id) {
        $output = [];
        try {            
            // check student
            if(StudentLink::StudentExists($student_id, 'id') !== true)
                throw new Exception('Student not found');

            $output = CLHF::DB_stored_procedure('mysql', 'stud_addr_curr', [$student_id], true)[0];
        } catch (\Exception $ex) {}//dd($student_id);
        return $output;
    }

    public static function getSISAddrEmgn($student_id) {
        $output = [];
        try {
            // check student
            if(StudentLink::StudentExists($student_id, 'id') !== true)
                throw new Exception('Student not found');

            $output = CLHF::DB_stored_procedure('mysql', 'stud_addr_emgn', [$student_id], true)[0];
        } catch (\Exception $ex) {}
        return $output;
    }

    public static function getSISAddrHome($student_id) {                
        $output = [];
        try {
            // check student
            if(StudentLink::StudentExists($student_id, 'id') !== true)
                throw new Exception('Student not found');

            $output = CLHF::DB_stored_procedure('mysql', 'stud_addr_home', [$student_id], true)[0];
        } catch (\Exception $ex) {}
        return $output;
    }

    public static function getSISAddress($student_id) {
        return [
            'curr' => SELF::getSISAddrCurr($student_id), 
            'emgn' => SELF::getSISAddrEmgn($student_id), 
            'home' => SELF::getSISAddrHome($student_id), 
        ];
    }

    public static function getSISAddress2($student_id) {
        $data = SELF::getSISAddress($student_id);
        $curr = $data['curr'];
        $emgn = $data['emgn'];
        $home = $data['home'];

        $FormDefaults = [
            // curr
            'RC_lname'        => $curr['lname'],
            'RC_fname'        => $curr['fname'],
            'RC_mname'        => $curr['mname'],
            'RC_namex'        => $curr['namex'],            
            'RC_relation'     => $curr['relation'],
            'RC_mobilenumber' => $curr['mobilenumber'],
            'RC_email'        => $curr['email'],
            'RC_country'      => $curr['country'],
            'RC_ps'           => $curr['ps'],
            'RC_cm'           => $curr['cm'],
            'RC_place'        => $curr['place'],
            'RC_zipcode'      => $curr['zipcode'],
            
            // emgn
            'RE_lname'        => $emgn['lname'],
            'RE_fname'        => $emgn['fname'],
            'RE_mname'        => $emgn['mname'],
            'RE_namex'        => $emgn['namex'],            
            'RE_relation'     => $emgn['relation'],
            'RE_mobilenumber' => $emgn['mobilenumber'],
            'RE_email'        => $emgn['email'],
            'RE_country'      => $emgn['country'],
            'RE_ps'           => $emgn['ps'],
            'RE_cm'           => $emgn['cm'],
            'RE_place'        => $emgn['place'],
            'RE_zipcode'      => $emgn['zipcode'],
            
            // home
            'RH_country'      => $home['country'],
            'RH_ps'           => $home['ps'],
            'RH_cm'           => $home['cm'],
            'RH_place'        => $home['place'],
            'RH_zipcode'      => $home['zipcode'],
        ];

        return $FormDefaults;
    }

















    public static function getSISFamilyPare($student_id) {
        $output = [];
        try {
            // check student
            if(StudentLink::StudentExists($student_id, 'id') !== true)
                throw new Exception('Student not found');

            $output = CLHF::DB_stored_procedure('mysql', 'stud_fami_pare', [$student_id], true)[0];
        } catch (\Exception $ex) {}
        return $output;
    }

    public static function getSISFamilySibl($student_id, bool $with_self=false, bool $to_client_tz=false) {
        // this function entwines with the client's tz

        $output = [];
        try {
            // check student
            if(StudentLink::StudentExists($student_id, 'id') !== true)
                throw new Exception('Student not found');
            
            $arr0 = SELF::getSISPersonal($student_id);
            $personal = [
                'lname' => '',//$arr0['name']['last'][1] ?? '',
                'fname' => '(ME)',//$arr0['name']['first'][1] ?? '',
                'mname' => '',//$arr0['name']['middle'][1] ?? '',
                'namex' => '',//$arr0['name']['extension'][1] ?? '',
                'birthsex' => $arr0['birth']['sex'][1] ?? '',
                'birthdate' => $arr0['birth']['date'][1] ?? '',
                'coursedegree' => '',
                'occupation' => '',
                'is_me' => 1,
            ];

            $arr1 = CLHF::DB_stored_procedure('mysql', 'stud_fami_sibl', [$student_id], true);
            $arr2 = $with_self ? array_merge($arr1, [$personal]) : $arr1;
            
            // add is_me for non-existent
            foreach($arr2 as $key=>$val) {
                if(array_key_exists('is_me', $val) !== true)
                    $arr2[$key]['is_me'] = 0;
            }
            $arr3 = $arr2;

            // interpret birthdate to client's tz
            foreach($arr3 as $key=>$val) {           
                if($to_client_tz === true) {
                    $arr3[$key]['birthdate'] = DT::STR_TryParseX($arr3[$key]['birthdate'], '', ['UTC', WebClient::getTimeZone()]);
                } else {
                    $arr3[$key]['birthdate'] = DT::STR_TryParseUTC($arr3[$key]['birthdate'], '', 'UTC');
                }
            }

            $arr4 = $arr3;

            // arrange birthdate
            array_multisort(array_column($arr4, 'birthdate'), SORT_ASC, $arr4);

            $output = $arr4;

        } catch (\Exception $ex) {
            //dd($ex);
            //AppFn::JED($ex->getMessage());
        }
        return $output;
    }

    public static function getSISFamilySibl2($student_id, bool $with_self=false, bool $to_client_tz=false) {
        // removes id columns
        $arr1 = SELF::getSISFamilySibl($student_id, $with_self, $to_client_tz);
        $arr2 = [];
        foreach($arr1 as $key=>$val) {
            foreach($val as $key2=>$val2) {
                if(Str::of($key2)->endsWith('_id') !== true) {
                    $arr2[$key][$key2] = $val2;
                }
            }
        }
        return $arr2;
    }

    /*public static function getSISFamilySpou($student_id) {
        $output = [];
        try {
            // check student
            if(StudentLink::StudentExists($student_id, 'id') !== true)
                throw new Exception('Student not found');

            $output = CLHF::DB_stored_procedure('mysql', '????', [$student_id], true)[0];
        } catch (\Exception $ex) {}
        return $output;
    }*/

    public static function getSISFamily($student_id) {
        return [
            'pare' => SELF::getSISFamilyPare($student_id), 
            'sibl' => SELF::getSISFamilySibl2($student_id), 
            //'spou' => SELF::getSISFamilySpou($student_id), 
        ];
    }

    public static function getSISFamily2($student_id) {
        $data = SELF::getSISFamily($student_id);
        $pare = $data['pare'];
        //$sibl = $data['sibl'];
        //$spou = $data['spou'];

        $FormDefaults = [
            // father
            'f_lname'        => $pare['f_lname'],
            'f_fname'        => $pare['f_fname'],
            'f_mname'        => $pare['f_mname'],
            'f_namex'        => $pare['f_namex'],
            //'f_birthdate'    => $pare['f_birthdate'],
            'f_occupation'   => $pare['f_occupation'],
            'f_mobilenumber' => $pare['f_mobilenumber'],
            
            // mother
            'm_lname'        => $pare['m_lname'],
            'm_fname'        => $pare['m_fname'],
            'm_mname'        => $pare['m_mname'],
            'm_namex'        => $pare['m_namex'],
            //'m_birthdate'    => $pare['m_birthdate'],
            'm_occupation'   => $pare['m_occupation'],
            'm_mobilenumber' => $pare['m_mobilenumber'],

            /*// siblings
            's_lname'        => $pare['s_lname'],
            's_fname'        => $pare['s_fname'],
            's_mname'        => $pare['s_mname'],
            's_namex'        => $pare['s_namex'],
            's_birthsex'     => $pare['s_birthsex'],
            's_birthdate'    => $pare['s_birthdate'],
            's_coursedegree' => $pare['s_coursedegree'],
            //'s_company'      => $pare['s_company'],
            's_occupation'   => $pare['s_occupation'],
            //'s_mobilenumber' => $pare['s_mobilenumber'],*/

            // spouse

        ];

        return $FormDefaults;
    }














    public static function getSIS($student_id) {
        //$FR_App = AppFn::FieldRules();
        $userlink = SELF::getSISUserLink($student_id);//dd($userlink);
        $personal = SELF::getSISPersonal2($student_id);
        $address = SELF::getSISAddress2($student_id);
        $family = SELF::getSISFamily2($student_id);
        $output = [
            'userlink'      => $userlink,
            'personal'      => $personal,
            'address'       => $address,
            'family'        => $family,
        ];
        return $output;
    }


    public static function getSISMirror() {
        return [
            "pers" => [
                "deta" => [
                    "lname_id" => 0,
                    "fname_id" => 0,
                    "mname_id" => 0,
                    "namex_id" => 0,
                    "maiden_lname_id" => 0,
                    "maiden_fname_id" => 0,
                    "maiden_mname_id" => 0,
                    "maiden_namex_id" => 0,
                    "birthsex_id" => 0,
                    "birthdate_id" => 0,
                    "birthplace_country_id" => 0,
                    "birthplace_ps_id" => 0,
                    "birthplace_cm_id" => 0,
                    "nationality_id" => 0,
                    "religion_id" => 0,
                    "civilstatus_id" => 0,
                    "mobilenumber_id" => 0,
                    //"disabilities" => [],
                ],
                "disa" => [],
                "phot" => [
                    'updated_at' => '',
                ],
                "esig" => [
                    'updated_at' => '',
                ],
            ],
            "addr" => [
                "curr" => [
                    "lname_id" => 0,
                    "fname_id" => 0,
                    "mname_id" => 0,
                    "namex_id" => 0,
                    "relation_id" => 0,
                    "mobilenumber_id" => 0,
                    "email_id" => 0,
                    "country_id" => 0,
                    "ps_id" => 0,
                    "cm_id" => 0,
                    "place_id" => 0,
                    "zipcode_id" => 0,
                ],
                "emgn" => [
                    "lname_id" => 0,
                    "fname_id" => 0,
                    "mname_id" => 0,
                    "namex_id" => 0,
                    "relation_id" => 0,
                    "mobilenumber_id" => 0,
                    "email_id" => 0,
                    "country_id" => 0,
                    "ps_id" => 0,
                    "cm_id" => 0,
                    "place_id" => 0,
                    "zipcode_id" => 0,
                ],
                "home" => [
                    "country_id" => 0,
                    "ps_id" => 0,
                    "cm_id" => 0,
                    "place_id" => 0,
                    "zipcode_id" => 0,
                ],
            ],
            "fami" => [
                "pare" => [
                    "f_lname_id" => 0,
                    "f_fname_id" => 0,
                    "f_mname_id" => 0,
                    "f_namex_id" => 0,
                    "f_occupation_id" => 0,
                    "f_mobilenumber_id" => 0,
                    "m_lname_id" => 0,
                    "m_fname_id" => 0,
                    "m_mname_id" => 0,
                    "m_namex_id" => 0,
                    "m_occupation_id" => 0,
                    "m_mobilenumber_id" => 0,
                ],
                "sibl" => [
                    "lname_id" => 0,
                    "fname_id" => 0,
                    "mname_id" => 0,
                    "namex_id" => 0,
                    "birthsex_id" => 0,
                    "birthdate_id" => 0,
                    "coursedegree_id" => 0,
                    "occupation_id" => 0,
                ],
            ],
        ];
    }


    public static function getSISMirrorLabel() {
        return [
            "pers" => [
                "deta" => [
                    "lname_id" => 'Last Name',
                    "fname_id" => 'First Name',
                    "mname_id" => 'Middle Name',
                    "namex_id" => 'Name Extension',
                    "maiden_lname_id" => 'Maiden Last Name',
                    "maiden_fname_id" => 'Maiden First Name',
                    "maiden_mname_id" => 'Maiden Middle Name',
                    "maiden_namex_id" => 'Maiden Name Extension',
                    "birthsex_id" => 'Birth Sex',
                    "birthdate_id" => 'Birth Date',
                    "birthplace_country_id" => 'Birth Country',
                    "birthplace_ps_id" => 'Birth Province/State',
                    "birthplace_cm_id" => 'Birth City/Municipality',
                    "nationality_id" => 'Nationality',
                    "religion_id" => 'Religion',
                    "civilstatus_id" => 'Civil Status',
                    "mobilenumber_id" => 'Mobile No.',
                    "email_id" => 'Email Address',
                ],
                "disa" => [],
                "phot" => [
                    'updated_at' => 'Updated At',
                ],
                "esig" => [
                    'updated_at' => 'Updated At',
                ],
            ],
            "addr" => [
                "curr" => [
                    "lname_id" => 'Last Name',
                    "fname_id" => 'First Name',
                    "mname_id" => 'Middle Name',
                    "namex_id" => 'Name Extension',
                    "relation_id" => 'Relation',
                    "mobilenumber_id" => 'Mobile No.',
                    "email_id" => 'Email',
                    "country_id" => 'Country',
                    "ps_id" => 'Province/State',
                    "cm_id" => 'City/Municipality',
                    "place_id" => 'Place Detail',
                    "zipcode_id" => 'Zipcode',
                ],
                "emgn" => [
                    "lname_id" => 'Last Name',
                    "fname_id" => 'First Name',
                    "mname_id" => 'Middle Name',
                    "namex_id" => 'Name Extension',
                    "relation_id" => 'Relation',
                    "mobilenumber_id" => 'Mobile No.',
                    "email_id" => 'Email',
                    "country_id" => 'Country',
                    "ps_id" => 'Province/State',
                    "cm_id" => 'City/Municipality',
                    "place_id" => 'Place Detail',
                    "zipcode_id" => 'Zipcode',
                ],
                "home" => [
                    "country_id" => 'Country',
                    "ps_id" => 'Province/State',
                    "cm_id" => 'City/Municipality',
                    "place_id" => 'Place Detail',
                    "zipcode_id" => 'Zipcode',
                ],
            ],
            "fami" => [
                "pare" => [
                    "f_lname_id" => 'Father Last Name',
                    "f_fname_id" => 'Father First Name',
                    "f_mname_id" => 'Father First Name',
                    "f_namex_id" => 'Father First Name',
                    "f_occupation_id" => 'Father Occupation',
                    "f_mobilenumber_id" => 'Father Mobile No.',
                    "m_lname_id" => 'Mother Last Name',
                    "m_fname_id" => 'Mother First Name',
                    "m_mname_id" => 'Mother Middle Name',
                    "m_namex_id" => 'Mother Name Extension',
                    "m_occupation_id" => 'Mother Occupation',
                    "m_mobilenumber_id" => 'Mother Mobile No.',
                ],
                "sibl" => [
                    "lname_id" => 'Last Name',
                    "fname_id" => 'First Name',
                    "mname_id" => 'Middle Name',
                    "namex_id" => 'Name Extension',
                    "birthsex_id" => 'Birth Sex',
                    "birthdate_id" => 'Birth Date',
                    "coursedegree_id" => 'Course/Degree',
                    "occupation_id" => 'Occupation',
                ],
            ],
        ];
    }

    


}


