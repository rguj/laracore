<?php

namespace Rguj\Laracore\Rule;

use Exception;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Rguj\Laracore\Library\AppFn;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\WebClient;
use Rguj\Laracore\Library\CLHF;

class GenericRule {

    private bool $isPasswordSet = false;
    private bool $isDateSet = false;
    private bool $isMobileNumberSet = false;
    private bool $isRulesInit = false;
    private bool $isRulesCopied = false;
    private bool $isRulesFinalized = false;

    private array $rulesFounding = [];
    private array $rulesClone = [];
    
    public $rulesArr = [
        'pname' => [  # Person Name
            'min' => 1,
            'max' => 255,
            //'regex' => '/^(?:[\p{L}\p{Mn}\p{Pd}\'\x{2019}]+\s?)+$/u',
            'regex' => '/^(?:[\p{L}\p{Mn}\p{Pd}\'\x{2019}]+[\.]?[\s]?)+$/u',
                # one space, single quote, Unicode letters(diacritics and more)
        ],
        'function' => [  // function name
            'regex' => '/[A-Za-z_]{1}[A-Za-z0-9_]*/u',
        ],
        'auth_with' => [
            'min' => 3,
            'max' => 255,
            'regex' => '/^([A-Za-z0-9]+_[A-Za-z0-9@+\-_~.]+)$/u',
        ],
        'email' => [
            'min' => 3,
            'max' => 255,
            'regex' => '/^([A-Za-z0-9_]+){1}([\.]?[A-Za-z0-9_]+)*([@]){1}(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/u',
        ],
        'password' => [
            'min' => 0,
            'max' => 0,
            'regex' => '',
        ],
        'mobilenumber' => [
            'prefix' => '+63',
            'regex'  => '',
        ],
        'date' => [
            'min'          => 0, 
            'max'          => 0,
            'regex'        => '',  // regex in (d M Y)
            'format_in'    => '',
            'format_out'   => '',
            'format_db'    => '',
            'date_min'     => '',
            'date_max'     => '',
            'converter'    => null,
        ],
        'address' => [
            'min' => 1,
            'max' => 255,
            'regex' => '/^(?!.*([\'\,\-\.\/])\1)([A-Za-z0-9Ññ]+([\'\-\/][A-Za-z0-9Ññ]|[\,\.]|\.\,)?(\ )?)+$/u',
            //'regex' => '/^([a-zA-Z\']+[\ ]*)+$/u',
        ],
        'zipcode' => [
            'min' => 1,
            'max' => 12,
            'regex' => '/^(?!.*([-])\1)([A-Za-z0-9]+([-][A-Za-z0-9])?)*$/u',
        ],
        'relation' => [
            'min' => 1,
            'max' => 50,
            'regex' => "/^(?!.*([',-])\\1)([A-Za-z0-9]+(['-][A-Za-z0-9]|[,.]|\.,)?( )?)+$/u",
        ],
        'email_verification' => [
            'min'     => 1, 
            'max'     => 100, 
            'regex'   => '/^[A-Za-z0-9]{'.(100).'}$/u',
        ],
    ];











    public function __construct()
    {
        foreach($this->rulesArr as $k=>$v) {
            $this->rulesFounding[$k] = $k;
        }
    }

    final private function __mergeRule(string $rule, array $arr)
    {
        if(!array_key_exists($rule, $this->rulesArr))
            throw new Exception('Key doesn\'t exists: '.$rule);
        if(empty($arr))
            throw new Exception('$arr must not be empty');
        $this->rulesArr[$rule] = array_merge($this->rulesArr[$rule], $arr);
    }

    final private function is_adm()  // is app dev mode
    {
        return AppFn::CONFIG_env('APP_DEV_MODE', false, 'boolean');
    }

    final private function __initRules()
    {
        if(!$this->isRulesInit) {
            $match = [  // add closures here
                'password' => fn() => $this->setPassword(),
                'date' => fn() => $this->setDate(),
                'mobilenumber' => fn() => $this->setMobileNumber(),
            ];
            foreach($match as $k=>$v) {
                if(array_key_exists($k, $this->rulesArr)) {
                    $v();
                }
            }
            $this->isRulesInit = true;
        }
    }

    // final public function finalizeRules()
    // {
    //     $this->getRules();  // fire once to finalize copies
    // }
















    final private function setMobileNumber()
    {        
        if(!$this->isMobileNumberSet) {
            $this->__mergeRule('mobilenumber', [
                'regex' => '/^('.preg_quote($this->rulesArr['mobilenumber']['prefix']).'){1}([0-9]){10}$/u',
            ]);
            $this->isMobileNumberSet = true;
        }
    }

    final private function setPassword()
    {
        if(!$this->isPasswordSet) {
            $pw_min_standard = 8;
            $pw_min = $this->is_adm() ? 3 : $pw_min_standard;
            $pw_max = 50;
            $ks = AppFn::STR_GetKeyboardSymbols(true);
            $this->__mergeRule('password', [
                'min' => $pw_min,
                'max' => $pw_max,
                'regex' => '/^([A-Za-z0-9]|['.$ks.']|\ ){'.$pw_min_standard.','.$pw_max.'}$/u',
            ]);
            $this->isPasswordSet = true;
        }
    }

    final private function setDate()
    {
        if(!$this->isDateSet) {
            $this->__mergeRule('date', [
                'min'          => 10, 
                'max'          => 26,
                'regex'        => '/^(0[1-9]|[12][0-9]|3[01])\ ([a-zA-Z]{3})\ ([0-9]{4})$/u',  // regex_in (d M Y)
                'format_in'    => 'd M Y',
                'format_out'   => 'Y-m-d',
                'format_db'    => DT::getStandardDTFormat(),
                'date_min'     => DT::STR_TryParse('1900-01-01 00:00:00.000000', '', [DT::getServerTZ(), 'UTC']),
                'date_max'     => DT::now_str(),
                'converter'    => function(string $str) {
                    return AppFn::STR_regex_eval(
                        $this->rulesArr['date']['regex'], 
                        $str, 
                        function(string $pattern, string $subject, mixed $output) {
                            return DT::STR_TryParseUTC(
                                $subject.' 00:00:00.000000',
                                [$this->rulesArr['date']['format_in'].' H:i:s.u',
                                $this->rulesArr['date']['format_db']],
                                WebClient::getTimeZone(),
                            );
                        }
                    );
                },
            ]);
            $this->isDateSet = true;
        }
    }
















    final private function copyRules()
    {
        $this->__initRules();
        if(!$this->isRulesCopied) {
            $arr = [
                'birthdate'      => 'date',
                'occupation'     => 'relation',
                'coursedegree'   => 'relation',
                'company'        => 'address',

                'lname'          => 'pname',
                'fname'          => 'pname',
                'mname'          => 'pname',
                
                'maiden_lname'   => 'lname',
                'maiden_fname'   => 'fname',
                'maiden_mname'   => 'mname',

                'birthplace_ps'  => 'address',
                'birthplace_cm'  => 'address',

            ];

            foreach($arr as $k=>$v) {
                $this->rulesArr[$k] = $this->rulesArr[$v];
                $this->rulesClone[$k] = $v;
            }

            $this->isRulesCopied = true;
        }
    }













    // final public function getRegex(string $key)
    // {
    //     return $this->getRule($key.'regex');
    // }

    final public function getRule(string $key)
    {
        $this->copyRules();  // invoke copy rules first

        if(!Arr::has($this->rulesArr, $key)) {
            throw new exception('Key not found: '.$key);
        }
        
        // setters to invoke
        // $match = [
        //     'password' => fn() => $this->setPassword(),
        //     'date' => fn() => $this->setDate(),
        //     'mobilenumber' => fn() => $this->setMobileNumber(),
        // ];
        $mainKey = trim(explode('.', $key)[0] ?? '');

        // check main key
        if(empty($mainKey) || !array_key_exists($mainKey, $this->rulesArr))
            return null;

        // invoke setter
        // if(array_key_exists($mainKey, $match))
        //     $match[$mainKey]();        

        return Arr::get($this->rulesArr, $key);
    }

    final public function getRules()
    {
        $this->copyRules();  // invoke copy rules first

        if(!$this->isRulesFinalized) {
            $arr = [];
            foreach($this->rulesArr as $k=>$v) {
                $arr[$k] = $this->getRule($k);
            }
            $this->isRulesFinalized = true;
        }
        return $this->rulesArr;
    }











}