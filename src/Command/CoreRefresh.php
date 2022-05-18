<?php

namespace Rguj\Laracore\Command;

use Illuminate\Console\Command;

# -----------------------------
use PhpOption\Option;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Env;
# -----------------------------



class CoreRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'core:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes route, config, view, cache, and env. Also runs composer `post-update-cmd`.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //return 0;
        $this->line('');
        $this->line('<fg=yellow>'.$this->description.'</fg=yellow>');
        $this->line('');
        
        $commands = [
            // 'cache:forget spatie.permission.cache',
            // 'clear-compiled',
            // 'optimize',
            'cache:clear',
            'route:clear',
            'route:cache',
            'config:clear',
            'config:cache',
            'view:clear',
            'view:cache',
        ];
        $caller = function(array $commands) {
            foreach($commands as $key=>$val) {
                $this->call($val);
                $this->line('');
            }
        };
        $caller($commands);


        ////////////////////////////

        $str1 = File::get('./.env');
        $arr1 = SELF::parseEnv();
        $arr1['PROCESS_TIME'] = 0.0;
        //echo(json_encode($arr1));die();

        $parse_val = function($val) {
            if($val === null)         return 'null';  
            elseif($val === false)    return 'false';      
            elseif($val === true)     return 'true';
            elseif(is_string($val))   return '"'.$val.'"';
            else                      return $val;
        };

        $str1 = ''; $x=-1; $c=count($arr1);
        foreach($arr1 as $key=>$val) {
            $x++;
            $str1 .= '"'.$key.'"=>'.$parse_val($val).($x===$c-1 ? '' : ',');
        } //echo($str1);die();
        File::replace(config_path('env.php'), '<?php return ['.$str1.'];');
        
        $arr2 = [];
        try {
            $arr2 = require(config_path('env.php'));
            $arr2['PROCESS_TIME'] = 0.0;
        } catch(\Exception $ex) {}

        if($arr1 !== $arr2) {
            $this->line('<fg=red>Enviroment file failed to refresh!</fg=red>');
        } else {
            $this->line('<fg=green>Enviroment file refreshed!</fg=green>');
        }
        $this->line('');
        
        // execute composer post-update-cmd
        $c = 'composer run-script post-update-cmd';
        $this->line('<fg=yellow>'.$c.'</fg=yellow>');
        $o = $r = null;
        exec($c, $o, $r);
        foreach((array)$o as $k=>$v) {
            $this->line($v);
        }
        $this->line('');
        $this->line('<fg=green>ALL DONE !</fg=green>');
        $this->line('');
    }



    public static function parseEnv() {
        $opt = [];
        $default = null;
        $repo = Env::getRepository();
        $repo_arr = obj_reflect(obj_reflect($repo, false)->writer ?? [], false)->loaded ?? [];
        foreach($repo_arr as $key=>$val) {
            $opt[$key] = Option::fromValue($repo->get($key))->map(function ($value) {
                switch (strtolower($value)) {
                    case 'true':
                    case '(true)':
                        return true;
                    case 'false':
                    case '(false)':
                        return false;
                    case 'empty':
                    case '(empty)':
                        return '';
                    case 'null':
                    case '(null)':
                        return;
                }
                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }
                return $value;
            })->getOrCall(function () use ($default) {
                return value($default);
            });
        }
        return $opt;
    }



}
