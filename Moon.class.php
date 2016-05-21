<?php
/*


*/
class Moon
{
    private $phase;
    private $illumination;
    private $age;
    private $distance;
    private $status; // ascending or descending
    
    function __construct($timestamp = null)
    {
        $timestamp = ($timestamp) ? $timestamp : time();
        $moonData = $this->getDataFromAPI($timestamp);
        // $moonData = false;
        if(!$moonData){
            // Parse Data form Formula
            $moonData = $this->getDataFromFormula($timestamp);
            $this->illumination = ceil($moonData[0] * 100);
            $this->age = number_format($moonData[1], 2, '.', '');
            $this->distance = number_format($moonData[2], 2, '.', ',');
        }else{
            // Parse Data form API
            $this->phase = $moonData->Phase;
            $this->illumination = $moonData->Illumination * 100;
            $this->age = number_format($moonData->Age, 2, ',', '');
            $this->distance = number_format($moonData->Distance, 2, '.', ',');
            
        }
    }

    public function getStatus()
    {
        $yesterdayTimestamp = time() - (24 * 60 * 60);
                                    // 24 hours; 60 mins; 60 secs
        $moonData = $this->getDataFromAPI($yesterdayTimestamp);
        if(!$moonData){
            $moonData = $this->getDataFromFormula($yesterdayTimestamp);
            $yesterdayDistance = number_format($moonData[2], 2, '.', ',');
        }else{
            $yesterdayDistance = number_format($moonData->Distance, 2, '.', ',');
        }
        $this->status = (($yesterdayDistance - $this->distance) > 0) ? 'Descending' : 'Ascending';
        return $this->status;
    }

    public function __set($name, $value)
    {
        echo "Setting '$name' to '$value'\n";
        $this->$name = $value;
    }

    public function __get($name)
    {
        if (is_null($this->$name)) {
            return null;
        }
        return $this->$name;

        // $trace = debug_backtrace();
        // trigger_error(
        //     'Undefined property via __get(): ' . $name .
        //     ' in ' . $trace[0]['file'] .
        //     ' on line ' . $trace[0]['line'],
        //     E_USER_NOTICE);
        // return null;
    }

    private function getDataFromFormula($timestamp = null)
    {
        include_once('MoonStatic.class.php');
        $timestamp = ($timestamp) ? $timestamp : time();
        $moonStatic = new moonStatic();
        $stringDate = date("Y,m,d,H,i,s", $timestamp);
        $d = explode(",", $stringDate);
        return $moonStatic->phase($d[0], $d[1], $d[2], $d[3], $d[4], $d[5]);
    }

    private function getDataFromAPI($timestamp = null)
    {
        $timestamp = ($timestamp) ? $timestamp : time();
        $url = 'http://farmsense-prod.apigee.net/v1/moonphases/?d=' . $timestamp;
        $moonData = file_get_contents($url); 
        // var_dump($moonData);
        // die();
        if($moonData){
            $luna  = json_decode($moonData);
            $luna = $luna[0];
            // print_r($luna);
            // $MoonAge = number_format($luna->Age, 0, ',', '');
            // $MoonAge = number_format($luna->Age, 0, ',', '');
            // print_r($luna->Phase);
            return $luna;
        }
        return false;
    }
}


?>