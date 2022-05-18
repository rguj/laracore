<?php

namespace Rguj\Laracore\Library;



use Illuminate\Support\Str;
use Illuminate\Support\Arr;


use Exception;
use App\Libraries\AppFn;
use App\Libraries\DT;
use App\Libraries\WebClient;
use App\Libraries\CLHF;


use App\Http\Controllers\Student\Profile as StudentProfile;
use App\Http\Controllers\Student\Link as StudentLink;
use App\Http\Controllers\Student\Application as StudentApplication;
use Illuminate\Support\Facades\App;

class FieldPreloads {



    public static function getRegisterStudent() {
        $PL_SIS = FieldPreloads::getSISPersonal();
        $form_preloads = [
            'namex' => $PL_SIS['namex'],
        ];
        return $form_preloads;
    }

    public static function getEmailVerification() {
        $FR_App = FieldRules::getGeneral();
        $DATA = [];

        return $DATA;
    }

    public static function getNamexs(bool $is_valid=true) {
        $where = $is_valid ? ['is_valid'=>1] : [];
        $order_by = ['created_at'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_namexs', $where, $order_by);
        $output = [];
        foreach($data as $key=>$val) {
            $output[] = $val['namex'];
        }
        return $output;
    }

    public static function getReligions(bool $is_valid=true) {
        $where = $is_valid ? ['is_valid'=>1] : [];
        $order_by = ['created_at'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_religions', $where, $order_by);
        $output = [];
        foreach($data as $key=>$val) {
            $output[] = $val['religion'];
        }
        return $output;
    }

    public static function getNationalities(bool $is_valid=true) {
        $where = $is_valid ? ['is_valid'=>1] : [];
        $order_by = ['created_at'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_nationalities', $where, $order_by);
        $output = [];
        foreach($data as $key=>$val) {
            $output[] = $val['nationality'];
        }
        return $output;
    }

    public static function getBirthSexes(bool $is_valid=true) {
        $where = $is_valid ? ['is_valid'=>1] : [];
        $order_by = ['created_at'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_birthsexes', $where, $order_by);
        $output = [];
        foreach($data as $key=>$val) {
            $output[] = $val['birthsex'];
        }
        return $output;
    }

    public static function getCivilStatuses(bool $is_valid=true) {
        $where = $is_valid ? ['is_valid'=>1] : [];
        $order_by = ['created_at'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_civilstatuses', $where, $order_by);
        $output = [];
        foreach($data as $key=>$val) {
            $output[] = $val['civilstatus'];
        }
        return $output;
    }

    public static function getTypesAHSB(bool $is_valid=true) {
        $where = $is_valid ? ['is_valid'=>1] : [];
        $order_by = ['created_at'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_types_ahsb', $where, $order_by);
        $output = [];
        foreach($data as $key=>$val) {
            $output[] = $val['type'];
        }
        return $output;
    }

    public static function getTypesCM(bool $is_valid=true) {
        $where = $is_valid ? ['is_valid'=>1] : [];
        $order_by = ['created_at'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_types_cm', $where, $order_by);
        $output = [];
        foreach($data as $key=>$val) {
            $output[] = $val['type'];
        }
        return $output;
    }

    public static function getTypesPS(bool $is_valid=true) {
        $where = $is_valid ? ['is_valid'=>1] : [];
        $order_by = ['created_at'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_types_ps', $where, $order_by);
        $output = [];
        foreach($data as $key=>$val) {
            $output[] = $val['type'];
        }
        return $output;
    }

    public static function getCountries(bool $is_valid=true) {
        $where = $is_valid ? ['is_valid'=>1] : [];
        $order_by = ['created_at'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_countries', $where, $order_by);
        $output = [];
        foreach($data as $key=>$val) {
            $output[] = $val['country'];
        }
        return $output;
    }





















    public static function getSISPersonal() {
        //$user_id = CLHF::AUTH_UserID();
        $FR_App = FieldRules::getGeneral();
        //$email = SELF::FETCH_UserEmail($user_id);
        $form_preloads = [];
        
        $form_preloads['namex'] = SELF::getNamexs();
        $form_preloads['maiden_namex'] = $form_preloads['namex'];

        // $form_preloads['birthplace_cm_type']   = SELF::getTypesCM();
        // $form_preloads['birthplace_ps_type']   = SELF::getTypesPS();
        
        // $form_preloads['RC_cm_type'] = SELF::getTypesCM();
        // $form_preloads['RE_cm_type'] = $form_preloads['RC_cm_type'];
        // $form_preloads['RH_cm_type'] = $form_preloads['RC_cm_type'];

        $form_preloads['birthsex'] = SELF::getBirthSexes();
        $form_preloads['birthplace_country'] = SELF::getCountries();

        $form_preloads['nationality'] = SELF::getNationalities();
        $form_preloads['religion'] = SELF::getReligions();
        $form_preloads['civilstatus'] = SELF::getCivilStatuses();
        $form_preloads['mobilenumber'] = $FR_App['mobilenumber'];  // ['prefix']
        
        return $form_preloads;
    }

    public static function getSISAddress() {
        //$user_id = CLHF::AUTH_UserID();
        $FR = FieldPreloads::getSISPersonal();
        $form_preloads = [];
        
        $form_preloads['RC_namex'] = SELF::getNamexs();
        $form_preloads['RE_namex'] = $form_preloads['RC_namex'];

        $form_preloads['RC_ahsb_type'] = SELF::getTypesAHSB();
        $form_preloads['RE_ahsb_type'] = $form_preloads['RC_ahsb_type'];
        $form_preloads['RH_ahsb_type'] = $form_preloads['RC_ahsb_type'];
        
        $form_preloads['RC_cm_type'] = SELF::getTypesCM();
        $form_preloads['RE_cm_type'] = $form_preloads['RC_cm_type'];
        $form_preloads['RH_cm_type'] = $form_preloads['RC_cm_type'];
        
        $form_preloads['RC_ps_type'] = SELF::getTypesPS();
        $form_preloads['RE_ps_type'] = $form_preloads['RC_ps_type'];
        $form_preloads['RH_ps_type'] = $form_preloads['RC_ps_type'];

        $form_preloads['RC_country'] = SELF::getCountries();
        $form_preloads['RE_country'] = $form_preloads['RC_country'];
        $form_preloads['RH_country'] = $form_preloads['RC_country'];

        $form_preloads['RC_mobilenumber'] = $FR['mobilenumber'];  // ['prefix']
        $form_preloads['RE_mobilenumber'] = $form_preloads['RC_mobilenumber'];
        
        return $form_preloads;
    }

    public static function getSISFamily() {
        //$user_id = CLHF::AUTH_UserID();
        $FR = FieldPreloads::getSISPersonal();
        $form_preloads = [];
        
        $form_preloads['f_namex'] = SELF::getNamexs();
        $form_preloads['m_namex'] = $form_preloads['f_namex'];
        $form_preloads['b_namex'] = $form_preloads['f_namex'];
        $form_preloads['s_namex'] = $form_preloads['f_namex'];

        $form_preloads['f_birthsex'] = SELF::getBirthSexes();
        $form_preloads['m_birthsex'] = $form_preloads['f_birthsex'];
        $form_preloads['b_birthsex'] = $form_preloads['f_birthsex'];
        $form_preloads['s_birthsex'] = $form_preloads['f_birthsex'];

        $form_preloads['f_mobilenumber'] = $FR['mobilenumber'];  // ['prefix']
        $form_preloads['m_mobilenumber'] = $form_preloads['f_mobilenumber'];
        $form_preloads['b_mobilenumber'] = $form_preloads['f_mobilenumber'];
        $form_preloads['s_mobilenumber'] = $form_preloads['f_mobilenumber'];
        
        return $form_preloads;
    }





    public static function getPostGradProgramSubjects(int $program_id, int $struc_mode=0) {
        $arr = SELF::getPostGradSubjects();
        $gpi = SELF::getGradProgramIDs();
        //if(!in_array($struc_mode, [0, 1, 2, 3]))
        //    throw new Exception('Invalid struc_mode');

        $opt = [];
        $a = [  // keys
            36 => [83,0,9,10,   11,12,84,20,21,  113,120,136,140,144,148,],
            37 => [83,0,1,5,6,   11,12,84,16,17,  114,120,137,141,145,149,],
            38 => [83,0,1,   11,12,84,  115,120,134,138,142,146,],
            39 => [83,0,1,7,8,   11,12,84,18,19,  116,120,121,135,139,143,147,],

            40 => [22,23,24,25,26,86,   55,56,57,58,59,  91,119,],
            41 => [22,23,24,27,28,   55,56,57,60,61,  92,119,129,150,],
            42 => [22,23,24,31,32,33,   55,56,57,64,65,  93,119,124,128,],
            43 => [22,23,24,29,30,   55,56,57,62,63,  94,119,122,123,127,],
            44 => [22,23,24,34,35,36,   55,56,57,66,67,  95,119,],
            45 => [22,23,24,37,38,   55,56,57,68,  96,119,],
            46 => [22,23,24,39,40,41,87,   55,56,57,69,89,  97,119,],
            47 => [22,23,24,42,43,44,   55,56,57,72,73,  98,119,],
            48 => [22,23,24,47,48,   55,56,57,77,  100,119,76,133,],
            49 => [22,23,24,45,46,   55,56,57,74,75,  99,119,133,],
            50 => [22,23,24,49,50,   55,56,57,78,90,  101,119,125,133,],

            51 => [22,23,24,25,26,86,   55,56,57,58,59,  105,119,],
            52 => [22,23,24,27,28,   55,56,57,60,61,  106,119,129,150,],
            53 => [22,23,24,31,32,33,   55,56,57,64,65,  107,119,124,128,],
            54 => [22,23,24,29,30,   55,56,57,62,63,  108,119,122,127,],
            55 => [22,23,24,34,35,36,   55,56,57,66,67,  109,119,],
            56 => [22,23,24,37,38,   55,56,57,68,  110,119,],

            57 => [22,23,24,51,52,   55,56,57,79,80, 102,119,],
            58 => [22,23,24,   55,56,57,  103,119,130,],
            59 => [22,23,24,53,54,88,   55,56,57,81,82,  117,118,126,],
            60 => [22,23,24,53,54,88,   55,56,57,81,82,  104,112,118,126,132,],
            61 => [22,23,24,53,54,88,   55,56,57,81,82,  104,111,118,126,131,],
            62 => [22,23,24,53,54,88,   55,56,57,81,82,  104,111,118,126,130,],
        ];
        
        if(array_key_exists($program_id, $a) !== true) {
            //throw new Exception('Invalid program_id');
            return $opt;
        }

        //$subject_ids = $a[$program_id];

        // include co-baccalaureate subjects
        $co_program_ids = [];
        $subject_ids = [];
        if(in_array($program_id, $gpi['doctoral'])) {
            $co_program_ids = $gpi['doctoral'];
        }
        elseif(in_array($program_id, $gpi['masteral'])) {
            $co_program_ids = $gpi['masteral'];
        }
        foreach($co_program_ids as $key1=>$val1) {
            foreach($a[$val1] as $key2=>$val2) {
                if(!in_array($val2, $subject_ids)) {
                    $subject_ids[] = $val2;
                }
            }
        }
        foreach($subject_ids as $key=>$val) {
            $opt[] = [
                $val,
                $arr[$val][0],
                $arr[$val][1],
                $arr[$val][2],
                $arr[$val][3],
                $arr[$val][4],
                (string)(CLHF::SECURITY_crypt($val, 0, true)[2] ?? ''),
            ];
        }
        //dd($subject_ids);
        return $opt;
    }

    public static function getPostGradSubjects() {

        $arr = [  // [subject_code, subject, time, room_venue, prof]

            // S1 > All Doctoral
            0 => ['FD 601', 'Advanced Methods in Educational Research / Technological Research', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Antonia D. Mendoza'],
            1 => ['FD 603', 'Public Personnel Administration', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Rose A. Arceno'],
            2 => ['TM 602', 'Advanced Human Resource and Development', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Alain Eulogio S. Tesado'],  // 83
            3 => ['DM 607', 'Human Resource Management and Development', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Alain Eulogio S. Tesado'],  // 83
            4 => ['EM 605', 'Human Resource Management and Development', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Alain Eulogio S. Tesado'],  // 83

            // S1 > DPTM
            5 => ['TM 603', 'Third Word Technology and Dev\'t: Critical Issues & Problems', '1:00pm - 6:00pm', 'Virtual Classroom', 'Dr. Rey Cesar V. Olorvida'],
            6 => ['TM 607', 'Management of Information Technology Systems', '1:00pm - 6:00pm', 'Virtual Classroom', 'Dr. Haide D. Selpa'],

            // S1 > DM
            7 => ['DM 601', 'Administrative Policy: Theories, Practices and Processes', '1:00pm - 6:00pm', 'Virtual Classroom', 'Dr. Eutiquio A. Pernis'],
            8 => ['DM 602', 'Human and Public Relations in Management', '1:00pm - 6:00pm', 'Virtual Classroom', 'Dr. Mary Tiezel G. Rufin'],

            // S1 > DPEd EM
            9 => ['Educ 601', 'Ecology of Educational Management', '1:00pm - 6:00pm', 'Virtual Classroom', 'Dr. Antonio E. Reposar'],
            10 => ['Educ 607', 'Management of Curriculum Development', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Virginia S. Beltran'],

            // S2 > All Doctoral
            11 => ['FD 602', 'Advanced / Applied Statistics', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Antonia D. Mendoza'],
            12 => ['FD 604', 'Advanced Theories and Principles of Education / Technology Transfer and Development', '1:00pm - 6:00pm', 'Virtual Classroom', 'Dr. Eduardo G. Codoy'],
            13 => ['TM 601', 'Organization Development', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Catalino L. Centillas'],  // 84
            14 => ['Educ 604', 'Management of Educational Organizations', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Catalino L. Centillas'],  // 84
            15 => ['DM 603', 'Problems and Issues: Organizational Planning and Development', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Catalino L. Centillas'],  // 84

            // S2 > DPTM
            16 => ['TM 604', 'Management of Social Justice and Human Rights', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Allen T. Arpon'],
            17 => ['TM 605', 'Corporate Planning & Project Evaluation', '1:00pm - 6:00pm', 'Virtual Classroom', 'Dr. Rolando C. Entoma'],

            // S2 > DM
            18 => ['DM 605', 'Crisis Management', '', 'Virtual Classroom', 'Dr. Mary Tiezel G. Rufin'],
            19 => ['DM  610', 'Comparative Local Government Administration', '7:00am – 12:00nn', 'Virtual Classroom', 'Dr. Manuel P. Albano'],

            // S2 > DPEd EM
            20 => ['Educ 603', 'Current Issues in Philippine Education', '1:00pm – 6:00pm', 'Virtual Classroom', 'Dr. Catalino L. Centillas'],
            21 => ['Educ 604', 'Management of Educational Organizations ', '7:00am – 12:00nn', 'Virtual Classroom', 'Dr. Sonia A. Pajaron'],


            // ---------------- MASTERAL -------------------

            // S1 > All Masteral
            22 => ['FD 501-A', 'Methods of Educational Research', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Rose A. Arceño'],
            23 => ['FD 502-A', 'Educational Statistics', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Mr. Jose Sherwin O. Seville'],
            24 => ['FD 503-A', 'Philo-Socio-Psycho Foundations of Education', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Ma. Thelma O Diansay'],

            // S1 > MAEd-E
            25 => ['ENG 506', 'Evaluation of Language Learning', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Sonia A. Pajaron'],
            26 => ['ENG 509', 'Sociolinguistics', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Geryl L. Cataraja'],

            // S1 > MAEd-F
            27 => ['Fil 504', 'Paraan at Pamaraan sa Pagtuturo ng Filipino', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Ferdilyn L. Viacrusis'],
            28 => ['Fil 507', 'Pagtatayang Pangwika sa Filipino', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Jennifer A. Gorumba'],

            // S1 > MAEd-M
            29 => ['Math 503', 'Linear Algebra', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Ms. Delia G. Limpangog'],
            30 => ['Math 507*', 'Methods & Approaches in Teaching Math', '1:00pm - 6:00pm', 'Flexible Learning/Virtual classroom', 'Mr. Christian Caben Larisma'],

            // S1 > MAEd-S
            31 => ['Sci 501', 'Chemistry for Teachers', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Camilo A. Tabinas'],
            32 => ['Sci 505', 'Strategies & Evaluation Techniques in Science', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Camilo A. Tabinas'],
            33 => ['Sci 507', 'Science, Technology & Education', '7:00am-12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Susan S. Entoma'],

            // S1 > MAEd-PE
            34 => ['PE 501', 'History & Development of Physical Education', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Henedina M. Cabusas* '],
            35 => ['PE 504', 'Evaluation Techniques in Physical Education', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Ms. Ariza S. Pogoy'],
            36 => ['PE 506', 'Implementation & Supervision of PE Programs', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Nelson N. Enage'],

            //  S1 > MAEd-MENS
            37 => ['Man Sci 501', 'The IMO and Its Work', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Gregorio S. Ochavillo'],
            38 => ['Man Sci 502', ' Economic Geography and World Trade', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Gregorio S. Ochavillo'],

            // S1 > MAEd-EM
            39 => ['EM 508', 'Computer Application in Management', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Janet A. Orioque'],
            40 => ['EEd 508', 'Computer Application in Management', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Janet A. Orioque'],
            41 => ['EM 504', 'Institutional Planning', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Placido F. Bercero*'],

            // S1 > MAEd - EEd
            42 => ['EEd 502', 'Reorientation Thrusts in Teacher Education: Humanism and Nationhood', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Mrs. Carol O. Laurente'],
            43 => ['EEd 503', 'Teaching Methodologies in Elementary Education', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Mrs. Carol O. Laurente'],
            44 => ['EEd 506', 'Psychology of the Filipino Child', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Jenney P. Bacalla'],

            // S1 > MAEd - TE
            45 => ['TE 506', 'Shop Management & Strategies', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Roel I. Sabang'],
            46 => ['TE 508', 'Computer Applications in Tech. Education', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Janet A. Orioque'],

            // S1 > MAEd - HE
            47 => ['HE 502', 'Problems and Issues in Home Economics', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Ma. Marilyn L. Olavides'],
            48 => ['HE 504', 'Teaching Methods & Techniques in H.E.', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Ma. Marilyn L. Olavides'],

            // S1 > MAEd - IE
            49 => ['IE 508', 'Computer Applications in Tech. Education', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Janet A. Orioque'],
            50 => ['IE 504', 'Advanced Teaching Methods and Techniques in Vocational-Industrial Education', '7:00am-12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Eduardo A. Codoy'],

            // S1 > MAEd - GC
            51 => ['GC 503', 'Filipino Values, Culture & Personality', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Virginia S. Beltran'],
            52 => ['GC 506', 'Concepts and Principles of Personality Development', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Mrs. Agnes Julie P. Beltran'],

            // S1 > MM Plan B
            53 => ['MM 514', 'Human Behavior in Organization', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Mr. Amed Cabugoy'],
            54 => ['MM 511', 'Legal Aspects in Management', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Mr. Leo Oswald'],

            // S2 > All Masteral
            55 => ['FD 501-B', 'Methods of Educational Research', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Geryl D. Cataraja'],
            56 => ['FD 502-B', 'Educational Statistics', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Mr. Jose Sherwin O. Seville'],
            57 => ['FD 503-B', 'Philo-Socio-Psycho Foundations of Education', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Ma. Thelma O Diansay'],

            // S2 > MAEd-E
            58 => ['ENG 503', 'Methods & Approaches in Teaching Language', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Diana Rose P. Esmero'],
            59 => ['ENG 504', 'The Teaching of Reading/Literature', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Nathaniel Bryan S. Fiel'],

            // S2 > MAEd-F
            60 => ['Fil 509', 'Mga Barayti at Baryasyon ng Wikang Filipino', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Henry Mar B. Laureno'],
            61 => ['Fil 510', 'Konteporaryong Panitikan', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Jennifer A. Gorumba'],

            // S2 > MAEd-M
            62 => ['Math 506', 'Abstract Algebra*', '7:00am-12:00nn', 'Flexible Learning/Virtual classroom', 'Mr. Christian Caben Laresma'],
            63 => ['Math 511', 'Research in Math Education*', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Mr. Christian Caben Laresma'],

            // S2 > MAEd-S
            64 => ['Sci 507', 'Science, Technology & Education', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Camilo A. Tabinas'],
            65 => ['Sci 508', 'Environmental Management', '7:00am-12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Susan S. Entoma'],

            // S2 > MAEd-PE
            66 => ['PE 507', 'Dance Education & Production', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Nelson N. Enage'],
            67 => ['PE 510', 'Contemporary Problems in Recreation*', '1:00pm - 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Nelson N. Enage'],

            // S2 > MAEd-MENS
            68 => ['Man Sci 505', 'Methods & Techniques in Teaching MENS', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Gregorio S. Ochavillo'],

            // S2 > MAEd-EM
            69 => ['EM 502', 'Laws & Legislation in Education', '1:00pm - 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Allen T. Arpon'],
            70 => ['EM 503', 'Policy Analysis in Dev’t. Education', '7:00am - 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Susan S. Entoma'],
            71 => ['EM 507', 'Management of Educational Mgt.', '7:00am - 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Susan S. Entoma'],

            // S2 > MAEd-EEd
            72 => ['EEd 505', 'Evaluation of Learning', '7:00am - 12:00nn', 'Flexible Learning/Virtual classroom', 'Mrs. Carol O. Laurente'],
            73 => ['EEd 507', 'Practicum: Management of Educational Programs and Projects', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Mrs. Carol O. Laurente'],

            // S2 > MAEd-TE
            74 => ['TE 507', 'Industrial Plant Training', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Roel I. Sabang'],
            75 => ['TE 510', 'Educational Innovations & Technology', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Eduardo C. Codoy'],

            // S2 > MAEd-HE
            76 => ['HE 503', 'Instruction & Supervision in H.E.', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Ma. Marilyn L. Olavides'],
            77 => ['HE 506', 'Evaluation of Learning Outcome in H.E.', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Ma. Marilyn L. Olavides'],

            // S2 > MAEd-IE
            78 => ['IE 503', 'Theories and Principles in Industrial Education', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Ma. Marilyn L. Olavides'],

            // S2 > MA-GC
            79 => ['GC 501', 'Guidance & Counselling', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Miss Justine Marie S. Beltran**'],
            80 => ['GC 511', 'Career Counseling and Development', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Mrs. Agnes Julie P. Beltran'],

            // S2 > MM-PlanB
            81 => ['FD 504*', 'Theories & Practices in Management', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Mr. Leo Roswald M. Tugonon'],
            82 => ['MM 516*', 'Corporate/Government Accounting', '1:00pm – 6:00pm', 'Flexible Learning/Virtual classroom', 'Mrs. Luz P. Paloma**'],


            // new
            
            83 => ['TM 602 / DM 607 / EM 605', 'Advanced Human Resource and Development / Human Resource Management and Development / Human Resource Management and Development', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Alain Eulogio S. Tesado'],  // 2,3,4
            
            84 => ['TM 601 / Educ 604 / DM 603', 'Organizational Development / Management of Educational Organizations / Problems and Issues: Organizational Planning and Development', '7:00am - 12:00nn', 'Virtual Classroom', 'Dr. Catalino L. Centillas'],  // 13, 14, 15
            
            85 => ['EM 508 / EEd 508', 'Computer Application in Management', '7:00am - 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Janet A. Orioque'],

            86 => ['Eng 501', 'Intro. to Linguistic', '7:00am - 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Geryl D. Cataraja'],
            
            87 => ['EM 511', 'Strategic Planning in Education Management', '7:00am - 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Sonia A. Pajaron'],
            
            88 => ['MM 600/600A', 'Thesis/Case Study Writing', '1:00pm - 6:00pm', 'Flexible Learning/Virtual classroom', 'Dr. Rose A. Arceño'],
            
            89 => ['EM 506 / EM 501 / HE 501', 'Curriculum Research & Dev\'t', '7:00am - 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Catalino L. Centillas Jr.'],

            90 => ['IE 503', 'Theories and Principles in Industrial Education', '7:00am – 12:00nn', 'Flexible Learning/Virtual classroom', 'Dr. Rey Cesar V. Olorvida'],

            91 => ['Eng 600', 'Thesis Writing', '', '', ''],
            92 => ['Fil 600', 'Thesis Writing', '', '', ''],
            93 => ['Sci 600', 'Thesis Writing', '', '', ''],
            94 => ['Math 600', 'Thesis Writing', '', '', ''],
            95 => ['PE 600', 'Thesis Writing', '', '', ''],
            96 => ['Man Sci 600', 'Thesis Writing', '', '', ''],
            97 => ['EM 600', 'Thesis Writing', '', '', ''],
            98 => ['EEd 600', 'Thesis Writing', '', '', ''],
            99 => ['TE 600', 'Thesis Writing', '', '', ''],
            100 => ['HE 600', 'Thesis Writing', '', '', ''],
            101 => ['IE 600', 'Thesis Writing', '', '', ''],
            102 => ['GC 600', 'Thesis Writing', '', '', ''],
            103 => ['CD 600', 'Thesis Writing', '', '', ''],
            104 => ['CD 600', 'Project Feasibility Study', '', '', ''],

            105 => ['Eng 600-A', 'Project Study', '', '', ''],
            106 => ['Fil 600-A', 'Project Study', '', '', ''],
            107 => ['Sci 600-A', 'Project Study', '', '', ''],
            108 => ['Math 600-A', 'Project Study', '', '', ''],
            109 => ['PE 600-A', 'Project Study', '', '', ''],
            110 => ['Man Sci 600-A', 'Project Study', '', '', ''],

            111 => ['HRD 600-A', 'Case Study Writing', '', '', ''],
            112 => ['CM 600-A', 'Case Study Writing', '', '', ''],

            113 => ['Educ 700', 'Dissertation Writing', '', '', ''],
            114 => ['TM 700', 'Dissertation Writing', '', '', ''],
            115 => ['CD 700', 'Dissertation Writing', '', '', ''],
            116 => ['DM 700', 'Dissertation Writing', '', '', ''],
            117 => ['MM 600-A', 'Thesis Writing', '', '', ''],

            118 => ['RE MM 700', 'Masteral Residency', '', '', ''],
            119 => ['RE MA 700', 'Masteral Residency', '', '', ''],
            120 => ['RE DOC 800', 'Doctoral Residency', '', '', ''],
            


            121 => ['DM 606', 'Executive Leadership and Supervisory Development', '', '', ''],
            122 => ['Math 501', 'Foundations of Mathematics', '', '', ''],
            123 => ['Math 507', 'Research Seminar and Practicum', '', '', ''],
            124 => ['Sci 502', 'Physics for Teachers', '', '', ''],
            125 => ['IE 502', 'Current Problem and Issues in Industrial Organization', '', '', ''],
            126 => ['MM 513', 'Management of Change', '', '', ''],
            127 => ['Math 505', 'Modern Geometry', '', '', ''],
            128 => ['Sci 506', 'Instructional Materials Development in Science', '', '', ''],
            129 => ['Fil 506', 'Paghahanda ng mga Kagamitang Pampagtuturo sa Filipino', '', '', ''],
            130 => ['MM 521', 'Computer Applications in Management', '', '', ''],
            131 => ['HRD 521', 'Computer Applications in Management', '', '', ''],
            132 => ['CM 521', 'Computer Applications in Management', '', '', ''],
            133 => ['FD 502-B', 'Applied Statistics', '', '', ''],
            

            134 => ['FD 601', 'Advanced Methods of Operations Research', '', '', ''],
            135 => ['FD 601', 'Advanced Methods of Research in Management', '', '', ''],
            136 => ['FD 601', 'Advanced Methods in Educational Research', '', '', ''],
            137 => ['FD 601', 'Advanced Methods Technological Research', '', '', ''],
            138 => ['FD 602', 'Advanced Statistical Methods', '', '', ''],
            139 => ['FD 602', 'Advanced Statistics in Management', '', '', ''],
            140 => ['FD 602', 'Advanced Statistical Methods in Education', '', '', ''],
            141 => ['FD 602', 'Industrial Statistics', '', '', ''],
            142 => ['FD 603', 'Advanced Principles & Theories of Community Development', '', '', ''],
            143 => ['FD 603', 'Public Personnel Administration', '', '', ''],
            144 => ['FD 603', 'Educational Theories and Principles', '', '', ''],
            145 => ['FD 603', 'Advanced Theories and Principles of Technology Transfer and Development', '', '', ''],
            146 => ['FD 604', 'Psycho-Social and Cultural Foundations of Community/Society', '', '', ''],
            147 => ['FD 604', 'Psycho-Social and Cultural Foundations of Public Organization and Mangement', '', '', ''],
            148 => ['FD 604', 'Philosophical and Ethical Foundation of Education', '', '', ''],
            149 => ['FD 604', 'Philosophical and Ethical Foundation in Technological and Industrial Relations', '', '', ''],
            150 => ['Fil 511', 'Sosyolingwistika', '', '', ''],


        ];
        return $arr;        
    }



    public static function getEnrolleeStatuses(int $mode, bool $is_valid=true, int $ays_offset=0, int $new_acedemic_status=0) {
        // mode ? 0-all ? 1-new ? 2-old

        if(in_array($mode, [0,1,2]) !== true)
            throw new exception('Invalid mode');

        $groups = [
            // 6 not included
            1 => [1,5,7,12],  // new
            2 => [3,10],  // continuing, returnee
        ];
        //$groups[0] = array_merge($groups[1], $groups[2]);  // post grad
        
        if(($mode === 0 || array_key_exists($mode, $groups)) !== true)
            throw new exception('Invalid mode');

        $where = [];
        if($is_valid) $where['is_valid'] = 1;
        $order_by = ['id'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_enrolleestatuses', $where, $order_by);
        $output = [];
        foreach($data as $key=>$val) {
            $arr1 = [
                'id' => $val['id'],
                'enrolleestatus' => $val['enrolleestatus'],
                'description' => $val['description'],
                'combined' => $val['enrolleestatus'].(AppFn::STR_IsBlankSpace($val['description']) !== true ? (' - ('.$val['description'].')') : ''),
            ];

            if($mode > 0) {
                if(in_array($val['id'], $groups[$mode])) {
                    $output[] = $arr1;
                }
            } else {
                $output[] = $arr1;
            }
        }
        //dd($output);
        
        $output2 = [];
        //dd($ays_offset);

        //dd($new_acedemic_status);
        //dd($ays_offset);
        foreach($output as $key=>$val) {
            if($mode === 2) {
                if($ays_offset < -1) {
                    if(in_array($val['id'], [10])) {
                        $output2[] = $val;
                    }
                }
                else if($ays_offset === -1) {
                    if(in_array($val['id'], [3])) {
                        $output2[] = $val;
                    }
                }
                else {
                    
                }
            }
            elseif($mode === 1) {
                if(in_array($new_acedemic_status, [6,7,11]) || in_array($val['id'], [7,5,12])) {
                    if($new_acedemic_status === 11 && in_array($val['id'], [7])) {
                        $output2[] = $val;
                        break;
                    }
                    elseif($new_acedemic_status === 7 && in_array($val['id'], [5])) {
                        $output2[] = $val;
                        break;
                    }                    
                    elseif($new_acedemic_status === 6 && in_array($val['id'], [12])) {  // new course - old
                        $output2[] = $val;
                        break;
                    }
                    /*elseif($new_acedemic_status ===  && in_array($val['id'], [14])) {  // new course - new
                        $output2[] = $val;
                        break;
                    }*/
                } else {
                    $output2[] = $val;
                }
            }
            else {
                $output2[] = $val;
            }
        }
        //dd($output2);
        return $output2;
    }
    
    public static function getPrograms(int $mode, bool $is_valid=true, bool $is_official=true, bool $is_offered=true, int $struc_mode=2) {
        // mode ? 0->all ? 1->high_school ? 2->undergrad ? 3->masters ? 4->doctors ? 5->postgrad
        // struc_mode ? 0->[id, program] ? 1->id ? 2->program

        $grad_program_ids = SELF::getGradProgramIDs();
        $groups = [
            // high_school
            1 => [33,34],
            // undergrad
            2 => [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32],
            // masters
            3 => $grad_program_ids['masteral'],
            // doctors
            4 => $grad_program_ids['doctoral'],
        ];
        $groups[5] = array_merge($groups[3], $groups[4]);  // post grad

        if(($mode === 0 || array_key_exists($mode, $groups)) !== true)
            throw new exception('Invalid mode');

        if(!in_array($struc_mode, [0,1,2]))
            throw new exception('Invalid struc_mode');
            
        /*if(!in_array($enrolleestatus_mode, [0, 1, 2]))
            throw new exception('Invalid enrollee status mode');*/

        $where = [];
        if($is_valid) $where['is_valid'] = 1;
        if($is_official) $where['is_official'] = 1;
        if($is_offered) $where['is_offered'] = 1;
        $order_by = ['program'=>'asc','created_at'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_programs', $where, $order_by);
        $output = [];
        foreach($data as $key=>$val) {
            if($mode > 0) {
                if(in_array($val['id'], $groups[$mode])) {
                    //$output[] = $val['program'];
                    $output[] = [$val['id'], $val['program'], $val['code']];
                }
            } else {
                //$output[] = $val['program'];
                $output[] = [$val['id'], $val['program'], $val['code']];
            }
        }

        $opt = $output;
        if($struc_mode === 1)
            $opt = array_column($output, 0);
        elseif($struc_mode === 2)
            $opt = array_column($output, 1);

        return $opt;
    }

    public static function getNSTP(bool $is_valid, int $program_id, string $col) {
        $where = $is_valid ? ['is_valid'=>1] : [];
        $order_by = ['created_at'=>'asc'];
        $data = CLHF::DB_PreloadFetch('pl_nstp', $where, $order_by);

        $only_maritime_nstp_id = [2];
        $maritime_program_ids = [1, 2];
        $is_maritime = in_array($program_id, $maritime_program_ids);
        
        $data2 = [];
        foreach($data as $key=>$val) {
            if($is_maritime) {
                if(in_array($val['id'], $only_maritime_nstp_id)) {
                    $data2[] = $val;
                }
            } else {
                $data2[] = $val;
            }
        }

        if($col !== '')
            $output = array_column($data2, $col);
        else
            $output = $data2;
        return $output;
    }

    public static function getLOACount() {
        return [1,2];
    }





    /*public static function getSASProgramsOverride() {
        // overrides automate values
        $overrides = [  // oes_program => [[match_course, match_major]]
		
			// -----------------------------
            // SINGLES

            'Bachelor of Science in Marine Engineering' => [  // BSMarE
                ['Bachelor of Science in Marine Engineering', '',],
            ],
            'Bachelor of Science in Marine Transportation' => [  // BSMT
                ['Bachelor of Science in Marine Transportation', '',],
            ],
            'Bachelor of Arts in Communication' => [  // BAComm
                ['Bachelor of Arts in Communication', '',],
            ],
            'Bachelor of Science in Hospitality Management' => [  // BAComm
                ['Bachelor of Science in Hospitality Management', '',],
                ['Bachelor of Science in Hotel & Restaurant Management', 'Cruise Ship Management',],
            ],
            'Bachelor of Science in Business Administration' => [  // BSBA
                ['Bachelor of Science in Business Administration', '',],
                ['Bachelor of Science in Business Administration', 'Marketing Management',],
            ],
            'Bachelor of Science in Mechanical Engineering' => [  // BSME
                ['Bachelor of Science in Mechanical Engineering', '',],
            ],
            'Bachelor of Science in Electrical Engineering' => [  // BSEE
                ['Bachelor of Science in Electrical Engineering', '',],
            ],
            'Bachelor of Science in Industrial Engineering' => [  // BSIE
                ['Bachelor of Science in Industrial Engineering', '',],
            ],
            'Bachelor of Science in Information Technology' => [  // BSInfoTech
                ['Bachelor of Science in Information Technology', '',],
            ],
            'Certificate in Teacher Education' => [  // CTE
                ['Certificate in Teacher Education', '',],
            ],
            'Diploma in Professional Education' => [  // DPE
                ['Diploma in Professional Education', '',],
            ],
            'Bachelor of Physical Education' => [  // BPEd
                ['Bachelor of Physical Education', '',],
            ],
            'Bachelor of Elementary Education' => [  // BEEd
                ['Bachelor of Elementary Education', '',],
            ],




            'Doctor of Philosophy in Education - Education Management' => [  // Ph.D. Ed - EM
                ['Doctor of Philosophy in Education Major in Educational Management', '',],
            ],
            'Doctor of Philosophy in Technology Management' => [  // Ph.D. TM
                ['Doctor of Philosophy in Technology Management', '',],
            ],
            'Doctor of Philosophy in Community Development' => [  // Ph.D CD
                //['', '',],
            ],
            'Doctor of Management' => [  // DM
                ['Doctor of Management', '',],
            ],
            'Master of Management (with Thesis)' => [  // MM-PlanA
                ['Master of Management (With Thesis)', '',],
            ],
            'Master of Management (with Case Study)' => [  // MM-PlanB
                // any MM-PlanB will be routed to this MM-PlanB-CM since it is CGS
                ['Master of Management Plan B (With Case Study)', '',],
                ['Master of Management (With Case Study)', '',],
                ['Master of Management (Non-thesis)', '',],
            ],
            'Master of Management (with Case Study) - Cooperative Management' => [  // MM-PlanB-CM
                ['Master of Management Plan B (With Case Study)', 'Cooperative Management',],
            ],
            'Master of Management (with Case Study) - Human Resource Development' => [  // MM-PlanB-HRD
                ['Master of Management Plan B (With Case Study)', 'Human Resource Development',],
                ['Master of Management (W/ Case Study)', 'Human Resource Development',],
            ],
            'Master of Arts in Guidance and Counseling' => [  // MA-GC
                ['Master of Arts in Guidance And Counseling', '',],
                ['Master of Arts in Education Major in Guidance And Counseling (Maed-gc)', '',],
            ],
            'Master of Arts in Education - Technology Education' => [  // MAEd-TE
                ['Master of Arts in Education Major in Technology Education', '',],
            ],








            // -----------------------------
            // WITH MAJORS
            
            'Bachelor of Science in Industrial Technology - Electronics Technology' => [  // BSInT-NT
                ['Bachelor of Science in Industrial Technology (Bsint)', 'Electronics Technology',],
                ['Bachelor of Science in Industrial Technology', 'Electronics',],
            ],
            'Bachelor of Science in Industrial Technology - Electrical Technology' => [  // BSInT-ET
                ['Bachelor of Science in Industrial Technology', 'Electricity',],
                ['Bachelor of Science in Industrial Technology (Bsint)', 'Electrical Technology',],
            ],
            'Bachelor of Science in Industrial Technology - Mechanical Technology' => [  // BSInT-MT
                ['Bachelor of Science in Industrial Technology (Bsint)', 'Mechanical Technology',],
                ['Bachelor of Science in Industrial Technology', 'Machine Shop',],
            ],
            'Bachelor of Science in Industrial Technology - Food and Beverages Preparation and Services' => [  // BSInT-FBPS
                ['Bachelor of Science in Industrial Technology (Bsint)', 'Food & Beverages Preparation & Service Management',],
                ['Bachelor of Science in Industrial Technology', 'Foods',],
            ],            
            'Bachelor of Science in Industrial Technology - Fashion and Apparel Technology' => [  // BSInT-FAT
                ['Bachelor of Science in Industrial Technology (Bsint)', 'Fashion And Apparel Technology',],
            ],            
            'Bachelor of Science in Industrial Technology - Automotive Technology' => [  // BSInT-AT
                ['Bachelor of Science in Industrial Technology (Bsint)', 'Automotive Technology',],
                ['Bachelor of Science in Industrial Technology', 'Automotive',],
            ],            
            'Bachelor of Science in Industrial Technology - Drafting Technology' => [  // BSInT-DT
                ['Bachelor of Science in Industrial Technology (Bsint)', 'Drafting Technology',],
                ['Bachelor of Science in Industrial Technology', 'Drafting',],
            ],            
            'Bachelor of Science in Industrial Technology - Power Plant Technology' => [  // BSInT-PPT
                ['Bachelor of Science in Industrial Technology (Bsint)', 'Power Plant',],
            ],            
            'Bachelor of Science in Industrial Technology - Welding and Fabrication Technology' => [  // BSInT-WFT
                ['Bachelor of Science in Industrial Technology (Bsint)', 'Welding & Fabrication Technology',],
            ],            
            'Bachelor of Science in Industrial Technology - Heating, Ventilation, Air Conditioning and Refrigeration' => [  // BSInT-HVACR
                ['Bachelor of Science in Industrial Technology (Bsint)', 'Heating, Ventilating, Air Conditioning & Refrigeration Tech.',],
                ['Bachelor of Science in Industrial Technology', 'Refrigeration & Air-Conditioning',],
            ],



            'Bachelor of Secondary Education - Filipino' => [  // BSEd-F
                ['Bachelor of Secondary Education', 'Filipino',],
                ['BACHELOR OF SECONDARY EDUCATION', 'Filipino',],
            ],
            'Bachelor of Secondary Education - Mathematics' => [  // BSEd-M
                ['BACHELOR OF SECONDARY EDUCATION', 'Mathematics',],
                ['Bachelor of Secondary Education', 'Mathematics',],
            ],
            'Bachelor of Secondary Education - English' => [  // BSEd-E
                ['BACHELOR OF SECONDARY EDUCATION', 'English',],
                ['Bachelor of Secondary Education', 'English',],
            ],
            'Bachelor of Secondary Education - Social Science' => [  // BSEd-SS
                ['BACHELOR OF SECONDARY EDUCATION', 'Social Studies',],
                ['Bachelor of Secondary Education', 'Social Studies',],
            ],

            
            'Bachelor of Technical Vocational Teacher Education - Garments, Fashion and Design' => [  // BTVTEd-GFD
                ['BACHELOR OF TECHNICAL-VOCATIONAL TEACHER EDUCATION', 'Garments, Fashion and Design',],
                ['Bachelor of Technical-vocational Teacher Education', 'Garments, Fashion And Design',],
            ],
            'Bachelor of Technical Vocational Teacher Education - Foods and Service Management' => [  // BTVTEd-FSM
                ['BACHELOR OF TECHNICAL-VOCATIONAL TEACHER EDUCATION', 'Food and Service Management',],
                ['Bachelor of Technical-vocational Teacher Education', 'Food And Service Management',],
            ], 
            'Bachelor of Technical Vocational Teacher Education - Automotive' => [  // BTVTEd-A
                ['Bachelor of Technical-vocational Teacher Education', 'Automotive Technology',],
            ],            
            'Bachelor of Technical Vocational Teacher Education - Electrical' => [  // BTVTEd-E
                ['Bachelor of Technical-vocational Teacher Education', 'Electrical Technology',],
            ],   
            

            'Bachelor of Technology and Livelihood Education - Industrial Arts' => [  // BTLEd-IA
                ['Bachelor of Technology And Livelihood Education', 'Industrial Arts',],
            ],
            'Bachelor of Technology and Livelihood Education - Home Economics' => [  // BTLEd-HE
                ['Bachelor of Technology And Livelihood Education', 'Home Economics',],
            ],




            'Master of Arts in Education - Filipino (non-thesis)' => [  // MAEd-F*
                ['Master in Education (Non-thesis)', 'Filipino',],
            ],
            'Master of Arts in Education - Science (non-thesis)' => [  // MAEd-S*
                ['Master in Education (Non-thesis)', 'Science',],
            ],
            'Master of Arts in Education - English (non-thesis)' => [  // MAEd-E*
                ['Master in Education (Non-thesis)', 'English',],
            ],
            'Master of Arts in Education - Mathematics (non-thesis)' => [  // MAEd-M*
                ['Master in Education (Non-thesis)', 'Mathematics',],
            ],
            'Master of Arts in Education - Physical Education (non-thesis)' => [  // MAEd-PE*
                ['Master in Education (Non-thesis)', 'Physical Education',],
            ],
            'Master of Arts in Education - Elementary Education (non-thesis)' => [  // MAEd-EEd*
                //['Master in Education (Non-thesis)', 'Science',],
            ],


            
            
            'Master of Arts in Education - English' => [  // MAEd-E
                ['Master of Arts in Education (With-thesis)', 'English',],
            ],
            'Master of Arts in Education - Filipino' => [  // MAEd-F
                ['Master of Arts in Education (With-thesis)', 'Filipino',],
            ],
            'Master of Arts in Education - Science' => [  // MAEd-S
                ['Master of Arts in Education (With-thesis)', 'Science',],
            ],
            'Master of Arts in Education - Mathematics' => [  // MAEd-M
                ['Master of Arts in Education (With-thesis)', 'Mathematics',],
            ],
            'Master of Arts in Education - Physical Education' => [  // MAEd-PE
                ['Master of Arts in Education (With-thesis)', 'Physical Education',],
            ],
            'Master of Arts in Education - Home Economics' => [  // MAEd-HE
                ['Master of Arts in Education Major in Home Economics', '',],
                ['Master in Technology Education', 'Home Economics',],
            ],
            'Master of Arts in Education - Elementary Education' => [  // MAEd-EEd
                ['Master of Arts in Education Major in Elementary Education (Maed-eed)', '',],
            ],
            'Master of Arts in Education - Educational Management' => [  // MAEd-EM
                ['Master of Arts in Education Major in Educational Management (Maed-em)', '',],
            ],
            'Master of Arts in Education - Industrial Education' => [  // MAEd-IE
                ['Master of Arts in Education Major in Industrial Education', '',],
                ['Master in Technology Education', 'Industrial Education',],
            ],
            'Master of Arts in Education - Marine Engineering and Nautical Science' => [  // MAEd-MENS
                ['Master of Arts in Education (With-thesis)', 'Marine Engineering And Nautical Science',],
            ],



        ];
        return $overrides;
    }*/

    public static function getGradProgramIDs() {
        // prepare grad_program_ids
        $grad_program_ids = [  // oes program ids
            'masteral' => [40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62],
            'doctoral' => [36,37,38,39,],
            'all' => [],
        ];
        foreach($grad_program_ids['masteral'] as $key=>$val) {
            if(!in_array($val, $grad_program_ids['all'])) {
                $grad_program_ids['all'][] = $val;
            }
        }
        foreach($grad_program_ids['doctoral'] as $key=>$val) {
            if(!in_array($val, $grad_program_ids['all'])) {
                $grad_program_ids['all'][] = $val;
            }
        }
        return $grad_program_ids;
    }
    

    //public static function getStudApplication(int $enrolleestatus_mode, int $program_mode, array $programs_locked=[]) {
    public static function getStudApplication(array $reference, int $program_id=0) {
        
        $opt = [
            'enrolleestatus' => [],
            'program' => [],
        ];

        $prog_id = 0;

        // check array structure
        $bool1 = (
            (is_bool($reference[0]) && $reference[0] === true)
            || (is_string($reference[1]) && in_array($reference[1], ['automate', 'admission']))
            || (is_string($reference[2]) && !AppFn::STR_IsBlankSpace($reference[2]))
        );
        if($bool1 !== true)
            return $opt;

        $prev_automate_data = $reference[1] === 'automate' ? StudentLink::SCLATM_StudentDataPrevSem($reference[2])[2] ?? [] : [];
        $ays_offset = $prev_automate_data['ays']['offset'] ?? 0;

        $prev_admission_data = $reference[1] === 'admission' ? StudentLink::STDADM_StudentData($reference[2])[2] ?? [] : [];
        $new_academic_status = $prev_admission_data ['applicant']['academic_status'][0] ?? 0;

        // enrollee status
        $enrolleestatus_mode = ($reference[1] === 'admission' ? 1 : 2);
        $enrolleestatuses = SELF::getEnrolleeStatuses($enrolleestatus_mode, true, $ays_offset, $new_academic_status);
        $opt['enrolleestatus'] = array_column($enrolleestatuses, 'combined');

        
        // get program supporting data (CASE-SENSITIVE)
        $programs_ = [];
        if($enrolleestatus_mode === 1) {  // if admission
            $stud_data = StudentLink::STDADM_StudentData($reference[2]);
            $programs_[] = Arr::get($stud_data, '2.applicant.program_endorsed.2', '');
        }
        else if($enrolleestatus_mode === 2) {  // if automate
            $stud_data = StudentLink::SCLATM_StudentDataPrevSem($reference[2]);
            //$programs_[] = Arr::get($stud_data, '2.curriculum.course', '');
            $automate_course_major = [
                Arr::get($stud_data, '2.curriculum.course', ''),
                Arr::get($stud_data, '2.curriculum.major', ''),
            ];
            $is_filled_0 = !AppFn::STR_IsBlankSpace($automate_course_major[0]);
            $is_filled_1 = !AppFn::STR_IsBlankSpace($automate_course_major[1]);

            $d1 = CLHF::DB_select_arr('mysql', '
                SELECT
                    pl_programs.id
                    , pl_programs.program
                    , pl_ctlcp.sas_course
                    , pl_ctlcp.sas_major
                FROM pl_ctlcp
                LEFT JOIN pl_programs ON pl_programs.id = pl_ctlcp.program_id
                WHERE
                    sas_course = ?
                    AND sas_major = ?
            ', [$automate_course_major[0], $automate_course_major[1]])[0] ?? [];
            $p = $d1['program'] ?? '';
            if(!AppFn::STR_IsBlankSpace($p)) $programs_[] = $p;
            /*$overrides = SELF::getSASProgramsOverride();  // overrides automate values
            if($is_filled_0 === true) {  //if($is_filled_0 === true && $is_filled_1 === true) {
                foreach($overrides as $key2=>$val2) {
                    foreach($val2 as $key3=>$val3) {
                        if($val3[0] === $automate_course_major[0] && $val3[1] === $automate_course_major[1]) {
                            $programs_[] = $key2;
                            break;
                        }
                    }
                }
            }*/
            /*elseif($is_filled_0 === true) {
                $programs_[] = $automate_course_major[0];
            }*/
        }        
        //dd($programs_);

        // program
        $program_mode = 0;  // all
        $programs = SELF::getPrograms($program_mode, true, true, true, 0);//dd($programs);
        $programs_locked = [];
        if(!empty($programs_)) {

            $grad_program_ids = SELF::getGradProgramIDs();
            $grad_mode = 0;  // 0->none, 1->master, 2->doctor

            // get matched program
            foreach($programs as $key1=>$val1) {
                //$v1 = Str::of($val1)->lower()->__toString();
                $v1 = Str::of($val1[1])->lower()->__toString();

                foreach($programs_ as $key2=>$val2) {
                    $v2 = Str::of($val2)->lower()->__toString();  
                    
                    if($v1 === $v2) {
                        
                        // blackhole for grad program
                        if(in_array($val1[0], $grad_program_ids['masteral'])) {
                            $programs_locked[] = $val2;
                            $grad_mode = 1;
                            break 2;  // quit double loop
                        }
                        elseif(in_array($val1[0], $grad_program_ids['doctoral'])) {
                            $programs_locked[] = $val2;
                            $grad_mode = 2;
                            break 2;  // quit double loop
                        }
                        else {  // non post-grad
                            if(!in_array($val2, $programs_locked)) {
                                $programs_locked[] = $val2;
                                break;  // only 1
                            }
                        }
                    }
                    
                }
            }

            $arr01 = CLHF::DB_LookUp('pl_programs', ['program'=>$programs_locked[0] ?? ''], true)[0] ?? [];
            $prog_id = (int)($arr01['id'] ?? 0);//dd($prog_id);
            
            if($grad_mode > 0) {
                $get_prog_name = function(int $id) use($programs) {
                    $prog_name = '';
                    foreach($programs as $key=>$val) {
                        if($val[0] === $id) {
                            $prog_name = $val[1];
                            break;
                        }
                    }
                    return $prog_name;
                };
                $programs_locked = [];  // clear first
                if($grad_mode === 1) {  // masteral
                    foreach($grad_program_ids['masteral'] as $key=>$val) {
                        $prog_name = $get_prog_name($val);
                        if(!in_array($prog_name, $programs_locked) && !AppFn::STR_IsBlankSpace($prog_name)) {
                            $programs_locked[] = $prog_name;
                        }
                    }
                }
                elseif($grad_mode === 2) {  // doctoral
                    foreach($grad_program_ids['doctoral'] as $key=>$val) {
                        $prog_name = $get_prog_name($val);
                        if(!in_array($prog_name, $programs_locked) && !AppFn::STR_IsBlankSpace($prog_name)) {
                            $programs_locked[] = $prog_name;
                        }
                    }
                }
            }
        }
        $opt['program'] = $programs_locked;

        // nstp
        $opt['nstp'] = SELF::getNSTP(true, $prog_id, 'component');
        
        $opt['loa_count'] = SELF::getLOACount();

        //dd($opt);
        return $opt;
    }





    
















}


