<?php
/**
 * Created by Sunny.
 * To change this template use File | Settings | File Templates.
 */

class GenericProperties
{
    const Cron_token = "d7650d6196744075d188bf5653e96a62";
    /**
     * Set Trace
     * @param $data
     * @param bool $die
     */
    public static function _setTrace($data=null,$die=true){
        if(is_array($data) or is_object($data)){
            print "<pre>";
            print_r($data);
            print "</pre>";
        }else{
            print $data;
        }
        print "<hr />";
        if($die){
            exit();
        }
    }

    /**
     * @param $image
     * @param $uid
     * @param bool $force
     */
    public static function setUserImage($image,$uid, $force=false)
    {
        if(Yii::app()->user->getId()==$uid or $force===true)
        {
            Yii::app()->session['user_image'] = $image;
        }
    }

    /**
     * Cache All module level to increase site performance
     * @return array
     */
    public static function getCacheLabels()
    {
        if(!isset(Yii::app()->session['labels'])
            or
            (isset(Yii::app()->session['labels']) and empty(Yii::app()->session['labels']))
        ){
            $labels = FieldLabels::model()->getSiteLabels();

            Yii::app()->session['labels'] = $labels;
        }
        else{
            $labels = Yii::app()->session['labels'];
        }

        return $labels;

    }

    /**
     * @return string
     */
    public static function getUserImage()
    {
        if(!empty(Yii::app()->session['user_image'])):
            return Yii::app()->request->baseUrl."/images/profile-images/".Yii::app()->session['user_image'];
        else:
            return Yii::app()->request->baseUrl."/images/profile-images/profile.jpg";
        endif;
    }

    /**
     * return a nice title for string
     * @param $string
     * @return string
     */
    public static function niceTitle($string){
        if(!is_array($string))
            return ucwords(str_replace("_"," ",$string));
    }

    /**
     * Return List From a key value pair array
     * @param string $key
     * @param array $data
     * @return array
     */
    public static function getListFromKey($key, $data)
    {
        if(!empty($data[0][$key]))
        {
            $result = array();
            foreach($data as $v):
                $result[]=$v[$key];
            endforeach;
            return $result;
        }

    }

    /**
     * @param $text
     * @param null $salt
     * @return string
     */
    public static function encrypt($text,$salt = null)
    {
        if($salt){
            $salt = self::SYSTEM_SALT;
        }
        return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
    }

    /**
     * @param $text
     * @param null $salt
     * @return string
     */
    public static function decrypt($text,$salt = null)
    {
        if($salt){
            $salt = self::SYSTEM_SALT;
        }
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
    }


    /**
     * Curl call in Office Auto Pilot API
     * @param bool $reqType
     * @param bool $data
     * @return bool|mixed
     */

    public static function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * @param $date_time
     * @param bool $age
     * @param bool $isForced
     * @return array
     */
    public static function humanTiming($date_time, $age = false, $isForced = false)
    {
        $regex = '/\d{4}-\d{2}-\d{2}/';
        $diff = time();
        $expire = "Not set";

        if(!preg_match($regex, $date_time)){
            return array($expire, $diff);
        }
        $tokens = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            //60 => 'minute',
            //1 => 'second'
        );
        if(!$age){
            $tokens[60] = 'minute';
        }

        if($age){
            $time_difference = abs(time() - strtotime($date_time));
        }else{
            $addTwoDays = date('Y-m-d H:i:s', strtotime($date_time. ' + 2 days'));
            $time_difference = strtotime($addTwoDays) - time();
            if($time_difference < 1){
                return "Expired";
            }
        }

        $diff = $time_difference;
        $expire = "";

        foreach ($tokens as $unit => $unit_name) {
            if($time_difference < $unit) continue;

            $numberOfUnits = floor($time_difference / $unit);
            $expire .= $numberOfUnits . ' ' . $unit_name.(($numberOfUnits > 1) ? 's ' : ' ');
            $time_difference = floor($time_difference % $unit);
        }
        if(!trim($expire)){
            $expire = "0 hours";
        }
        return array($expire, $diff);
    }

    public static function getDatePicker($model,$str,$showDate=false,$year_control=false)
    {
        if($model->$str !=''){
            $val=date('Y-m-d',strtotime($model->$str));
        }else{
            if($showDate) {
                $val = date('Y-m-d');
            }else{
                $val = '';
            }
        }
        $option=array(
            'showAnim'=>'fold',
            'dateFormat'=>'yy-mm-dd',
        );

        if($year_control){
            $option['changeYear']=true;
            $option['changeMonth']=true;
            $option['yearRange']='-80:+20';
        }
        $controller = new CController('HelpController');
        $controller->widget('zii.widgets.jui.CJuiDatePicker', array(
            'model'=>$model,
            'attribute'=>$str,
            // 'value'=>date('Y-m-d'),
            'options'=>$option,
            'htmlOptions'=>array(
                'style'=>'vertical-align:top',
                "readonly"=>"readonly",
                // "class"=>'simple_field pick_date',
                "class"=>'simple_field form-control pick_date',
                'value'=>$val

            ),
        ));
    }

    public static function GetDropDownByModel($model,$name='name',$condition_string=false,$empty_string=true,$select_value_column='id'){
        /**
         * @var $model XModel
         */




        $criteria = new CDbCriteria();
        $criteria->select = " $select_value_column as id, ".$name;

        if($condition_string){
            $criteria->condition=$condition_string;
        }
        $criteria->order="".$name." ASC";
        $data = Yii::app()->db->commandBuilder->createFindCommand($model->tableName(), $criteria)->queryAll();


        /*
        if($condition_string){
            //Generic::_setTrace($condition_string);
            $data=$model->findAll($condition_string);
        }else{
            $data=$model->findAll();
        }
        //Generic::_setTrace($data);
        */

        $data=CHtml::listData($data,'id',$name);
        if($empty_string){
            $empty=array(''=>'---Select---');
            $data= $empty+$data;
        }

        //asort($data);
        return $data;

    }

    public static function getDateRange( $first, $last, $step = '+1 day', $format = 'Y-m-d' ) {
        $dates = array();
        $current = strtotime( $first );
        $last = strtotime( $last );

        while( $current <= $last ) {
            $index = date( $format, $current );
            $dates[$index] = 0;
            $current = strtotime( $step, $current );
        }

        return $dates;
    }

    public static function getCountryCodeFromPhoneNumber($phone_number = ''){
        $country_codes = Enum::getCountryCodeList();
        $formatted_number = "+" . preg_replace("/[^0-9]/", "", $phone_number);

        $country_code = "";

        foreach($country_codes as $cd => $code){
            $pattern = "/^$code/";
            if(preg_match($pattern, $formatted_number)){
                $country_code = $cd;
                break;
            }
        }
        return $country_code;
    }


    public static function getTravelerProfileLink($model,$link='',$expire=0){
        //return '';
        #$link='http://yourtraveller.com/oapProfile/oPap5ka3Zax1FZE0XMD12haasm3I62uaJk7e0D9AK2M-h71wipSo-t6s_hyilbc81gi6Icllww4X_r2-d1a34w';
        if($link!=''){
            $linkArray=parse_url($link);
            //self::_setTrace($linkArray,false);
            if(isset($linkArray['path'])){
                $encodeData=str_replace("/oapProfile/","",$linkArray['path']);
                if($encodeData!=''){
                    $myCipher=new MyCipher();
                    parse_str($myCipher->decode($encodeData),$decodeData);
                    //self::_setTrace($decodeData,false);
                    if(isset($decodeData['email'])) {
                        $decodeData['expire_time'] = time() + ($expire * 60);
                        $decodeData['agent_id'] = Yii::app()->user->getId();
                        $decodeData['lead_agent_id'] = $model->agent_id;
                        $decodeData['lead_id'] = (isset($model->associated_lead_id) && $model->associated_lead_id)?$model->associated_lead_id:$model->id;
                        $decodeData['commission'] = $model->leadsDetails->commission_level;
                        $decodeData['token'] = sha1(SiteConfig::TokenKey);
                        $decodeData['customer_id'] = $model->leadsDetails->customer_id;
                        //GenericProperties::_setTrace($decodeData);
                        $newEncodeData=$myCipher->encode(http_build_query($decodeData));
                        $link=str_replace($encodeData,$newEncodeData,$link);

                    }
                    //self::_setTrace($link);
                }
            }
        }
        //self::_setTrace(parse_url($link));
        return $link;
    }

    public static function getAgentsByAgencyId($lead_id){

        if($lead_id>0){
            $agencySql="SELECT agency_id FROM ".Leads::model()->tableName()." WHERE id=$lead_id";
            $agencyId=Yii::app()->db->createCommand($agencySql)->queryScalar();
            if($agencyId>0)
            {
                $agentSql="SELECT
                    tbl_user.id,
                    tbl_user.full_name
                    FROM
                    lts_agent
                    INNER JOIN tbl_user ON lts_agent.user_id = tbl_user.id
                    where agency_id=$agencyId";
                $agentIds=(array)Yii::app()->db->createCommand($agentSql)->queryAll();
                return $agentIds;
            }
            return array();
        }
    }

    public static function machineGeneratedPassword(){
        $string = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqesruvwxyz123456789#$^&!@";
        $length_of_string = strlen($string);
        $password_length = rand(7, 15);
        $password = "";
        for($i = 0; $i < $password_length; $i++){
            $random_index = rand(1, $length_of_string - 1);
            $password .= $string[$random_index];
        }
        return $password;
    }

    public static function getDatePickerForGrid($model,$attribute){

        $controller = new CController('HelpController');
        $datePicker=array(
            'name' => $attribute,
            'type' => 'raw',
            'filter'=>$controller->widget('zii.widgets.jui.CJuiDatePicker', array(
                    'model'=>$model,
                    'attribute'=>$attribute,
                    'htmlOptions' => array(
                        'id' => 'datepicker_for_'.$attribute
                    ),
                    'options' => array(
                        'dateFormat' => 'yy-mm-dd'
                    )
                ), true)
        );

        return $datePicker;
    }

    public static function reinstallDatePiker($create_date=''){

        Yii::app()->clientScript->registerScript('re-install-date-picker', "
            function reinstallDatePicker(id, data) {
                $('#datepicker_for_create_date').datepicker(jQuery.extend({showMonthAfterYear:false},jQuery.datepicker.regional['ja'],{'dateFormat':'yy-mm-dd'}));
                $('#datepicker_for_accept_date').datepicker(jQuery.extend({showMonthAfterYear:false},jQuery.datepicker.regional['ja'],{'dateFormat':'yy-mm-dd'}));
                $('#datepicker_for_offered_date').datepicker(jQuery.extend({showMonthAfterYear:false},jQuery.datepicker.regional['ja'],{'dateFormat':'yy-mm-dd'}));
            }
         ");
    }

    public static function uploadFile($fileInstance, $file_name, $file_path){
        $base_path = Yii::app()->basePath."/../uploaded/";
        $file_base = $base_path.$file_path."/";
        //Create path if not exist
        try{
            if (!file_exists($file_base)) {
                mkdir($file_base, 0777, true);
            }
        }catch (Exception $e){}

        $file = $file_base.$file_name;
        if(move_uploaded_file($fileInstance['tmp_name'],$file)) {
            return $file;
        }
        return false;
    }

    public  static  function GetUserIP(){
        if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $real_client_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $real_client_ip = $_SERVER["REMOTE_ADDR"];
        }
        return $real_client_ip;
    }

    public static function getCustomFieldsData($model,$lead_custom_fields,$mail=false)
    {
        $div=array();
        $hash = $model->id.'-'.Yii::app()->user->getId().'-'.SiteConfig::TokenKey;
        $hash = sha1($hash);
        if(isset($lead_custom_fields) && $lead_custom_fields!=''){
            $lead_custom_fields = @json_decode($lead_custom_fields, true);
            $lead_custom_fields = array_filter($lead_custom_fields);

            #if user is an Agent hide IP address
            if(Yii::app()->session['user_role']==3 && isset($lead_custom_fields['User_Ip_Address'])){
                unset($lead_custom_fields['User_Ip_Address']);
            }elseif(Yii::app()->session['user_role']==3 && isset($lead_custom_fields['User_IP'])){
                unset($lead_custom_fields['User_IP']);
            }

            #GenericProperties::_setTrace($lead_custom_fields);
            if(is_array($lead_custom_fields) && count($lead_custom_fields) > 0){

                $key= 'Bride_Name';
                if(isset($lead_custom_fields[$key] )) {
                    $value = $lead_custom_fields[$key];
                    $dp_array= array($key=>$value);
                    $lead_custom_fields= array_merge($dp_array,$lead_custom_fields);
                    //GenericProperties::_setTrace($lead_custom_fields);

                }

                #hide Price_In_Dollar as far currency irrelevancy
                if(isset($lead_custom_fields['Price_In_Dollar'])){
                    unset($lead_custom_fields['Price_In_Dollar']);
                }


                //GenericProperties::_setTrace($lead_custom_fields,false);
                if(isset($lead_custom_fields['feed'])  || isset($lead_custom_fields['Feed'])){
                    $hideKeys = array('feed','Feed', 'Transaction_Session','Site_Transaction_Id','Name','Phone_Number','Response','User_Ip_Address','Direct','Latitude','Longitude');
                      #if user is an Agent & feed is Rentals United
                    //if(Yii::app()->session['user_role']==3 && $lead_custom_fields['feed']==7){
                    foreach($hideKeys as $key) {
                        unset($lead_custom_fields[$key]);
                    }

                        $country_region_place[]=@$lead_custom_fields['Place_Name'];
                        $country_region_place[]=@$lead_custom_fields['Region_Name'];
                        $country_region_place[]=@$lead_custom_fields['Country_Name'];
                        $country_region_place= array_filter($country_region_place);

                        $lead_custom_fields['Property_Location']=implode(', ',$country_region_place);
                        $hideKeysCRP = array('Place_Name','Region_Name', 'Country_Name');
                        foreach($hideKeysCRP as $key) {
                            unset($lead_custom_fields[$key]);
                        }
                    //}
                }
                //GenericProperties::_setTrace($lead_custom_fields);

                foreach ($lead_custom_fields as $key=>$val) {
                    $currency='';
                    if($key=='Price_In_Dollar'){
                        $currency='$ ';
                    }
                    $key = str_replace(array('_','-'),' ',$key);
                    if($mail){
                        $div[]='<p>'.ucfirst($key).':  '.$val.'    </p>';
                    }else{
                        $val=trim($val);
                        if(filter_var($val, FILTER_VALIDATE_URL)){

                            $link=self::ChangeAgentPortalLink($val);
                            $lead_id= $model->id;
                            $url= $link."?LTSLID=$lead_id&LTSAID=".$model->agent_id.'&hash='.$hash;
                            $val= '<a  target="_blank" href="'.$url.'">'.$val.'</a>';
                        }
                        $div[]='<div class="mws-form-row bordered">
                            <label class="mws-form-label">'.ucfirst($key).'</label>
                            <div class="mws-form-item">
                                '.$currency.$val.'
                            </div>
                          </div>';
                    }

                }


            }
        }
       if($mail){
           if(is_array($div)){
               $div=implode('',$div);
           }
       }

        return $div;
    }

    public static function checkCronToken(){

        if(Yii::app()->request->getParam("token") != self::Cron_token){
            throw new CHttpException(403, "Invalid token!");
        }
    }


    /**
     * Only for cron script
     * @param $subject
     * @param $content
     * @param null $to
     */
    public static function  SendStatusMail($subject,$content, $to = null){
        $subject="TAND Schedule task for ".$subject;
        if($to){
            //rakib@leftofthedot.com,
            $to = $to.', cron.leftofthedot@gmail.com, mithu723019@yahoo.com';
        }
        else{
            $to = 'cron.leftofthedot@gmail.com, mithu723019@yahoo.com';
        }


        // send mail each 1 hour interval
        if(date('i') == '00'){
            self::sendMail($content,$subject,$to);
        }


    }

    public static function AgentSelection(){
        $sunday_start='2356';
        $sunday_end='2356';
        $queue_id=4;
        $criteria=new CDbCriteria();
        $criteria->select="tbl_user.id";
        $criteria_join=" INNER JOIN ". User::model()->tableName()." tbl_user ON t.agent_id = tbl_user.id";
        $criteria_join.=" INNER JOIN ".Agency::model()->tableName()." lts_agency ON t.agency_id =   lts_agency.id";
        $criteria_join.=" INNER JOIN ".AgentUnavailable::model()->tableName()." lts_agent_unavailable ON lts_agent_unavailable.agent_id = tbl_user.id";
        $criteria->join=$criteria_join." INNER JOIN ".AgentAvailabilityWeek::model()->tableName()." lts_agent_availability_week ON lts_agent_availability_week.agent_id = tbl_user.id";
        $criteria->condition="tbl_user.active = 1 AND
                              tbl_user.role = 3 AND
                              t.queue_id = :queue_id AND
                              tbl_user.id NOT IN (
                              CASE
                                WHEN lts_agency.escalation_agent_id IS NULL
                                THEN
                                    0
                                ELSE
                                    lts_agency.escalation_agent_id
                                END
                              )";
        $criteria->group="tbl_user.id,lts_agent_availability_week.sunday_start,lts_agent_availability_week.sunday_end";
        $criteria->having="array_to_string(array_agg(date(lts_agent_unavailable.start_date)),',') NOT LIKE '%2015-03-29%'
                           AND lts_agent_availability_week.sunday_start::INTEGER <= :sunday_start::INTEGER
                           AND lts_agent_availability_week.sunday_end::INTEGER  >= :sunday_end::INTEGER";
        $criteria->params=array(':sunday_start'=>$sunday_start,':sunday_end'=>$sunday_end,':queue_id'=>$queue_id);
        $result=Yii::app()->db->commandBuilder->createFindCommand(AssignedQueues::model()->tableName(),$criteria)->queryAll();
    }

    public static function sendMail($content, $subject, $to, $from="TravelClients <noreply@travelclients.com>")
    {
        // die("fgsdgdfgd");
        $body="<!DOCTYPE html>
                <html>
                <head>
                    <title>$subject</title>
                </head>
                <body>
                    $content
                </body>
                </html>";

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        // Additional headers
        #$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
        //$headers .= 'To: '.$to. "\r\n";
        #$headers .= 'From: Birthday Reminder <birthday@example.com>' . "\r\n";
       // $headers .= 'From: '.$from . "\r\n";
        #$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
        #$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";

        $reg='/<([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)>/';
        preg_match($reg, $from, $from_matches);

        // only email checking
        $re = "/[a-z\\d._%+-]+@[a-z\\d.-]+\\.[a-z]{2,4}\\b/";
        preg_match($re, $from, $matches);
        if(isset($from_matches[0])){
            $headers .= 'From: '.$from . "\r\n";
        }else{
            if(isset($matches[0])){
                $headers .= 'From: '.$matches[0] . "\r\n";
            }
        }


        #GenericProperties::_setTrace($headers,false);
        #GenericProperties::_setTrace($body);
        // Mail it

        if(SiteConfig::CheckEnvironment()=='staging'){
            $subject= "(Staging)".$subject;
        }
        if(SiteConfig::CheckEnvironment()=='local'){
            $file=Yii::app()->getBasePath().'/'.time().'.txt';
            file_put_contents($file,$body,FILE_APPEND);
        }

        if(@mail($to, $subject, $body, $headers, '-f '.$matches[0])){
            return true;
        }
    }


    /**
     * get CSV File Delimiter
     * @param $file
     * @param int $checkLines
     * @return mixed
     */
    public static function getCSVFileDelimiter($file, $checkLines = 2){
        $results = array();
        try
        {
            $file = new SplFileObject($file);
            $delimiters = array(
                ",",
                "\t",
                ";",
                "|",
                ":"
            );

            for($i = 0; $i <= $checkLines; $i++){
                $line = $file->fgets();
                foreach ($delimiters as $delimiter){
                    $regExp = '/['.$delimiter.']/';
                    $fields = preg_split($regExp, $line);
                    if(count($fields) > 1){
                        if(!empty($results[$delimiter])){
                            $results[$delimiter]++;
                        } else {
                            $results[$delimiter] = 1;
                        }
                    }
                }
            }
            $results = array_keys($results, max($results));
        }
        catch(Exception $e){
            return false;
        }

        return $results[0];
    }

    public static function ChangeAgentPortalLink($val){
        $link=str_replace(array('renters','rentbyowner','bedroomvillas'),'rentalhomes',$val);
        if($val != $link){
            $arr= explode('/',$link);
            if(is_array($arr)){
                $property_id=end($arr);
                $link="http://www.rentalhomes.com/individual/".$property_id;
                if( SiteConfig::CheckEnvironment()!='live'){
                    $link="http://beta-rebrandtest.rentalhomes.com/individual/".$property_id;
                }
               
            }

        }else{
            if( strstr($val,'beta.')){
                $link=str_replace('beta.','beta-rebrandtest.',$val);
            }
        }

        return $link;
    }

    public static function GetTimezoneBasedDateTime($timezone,$format="Y-m-d"){
        $date = new DateTime('now', new DateTimeZone($timezone));
        //  $date = new DateTime('now', new DateTimeZone('America/Vancouver'));
        return  $date->format($format) ;
    }

    public static function GetOffsetFromTimezone($timezone_name){
        $timezone_list = file_get_contents(Yii::app()->getBasePath().'/timezone_offset.json');
        $timezone_list= json_decode($timezone_list,true);

        //GenericProperties::_setTrace($timezone_list);
        if(isset($timezone_list[$timezone_name])){
            return $timezone_list[$timezone_name];
        }else{
            return '';
        }
    }

    /*
     * Function for finding the location based on the phone code
     * takes the phone code as input and outputs the location
     * by masud_moni
     * */
    public static function GetExactLocationFromPhoneCode($phoneCode){
        $phoneCodeList = Array
        (
            "201" => "New Jersey (Newark, Jersey City)",
            "202" => "Washington, DC (Washington)",
            "203" => "Connecticut (New Haven, Bridgeport)",
            "205" => "Alabama (Birmingham, Tuscaloosa)",
            "206" => "Washington (Seattle, Kirkland)",
            "207" => "Maine (Portland, Hollis Center)",
            "208" => "Idaho (Boise, Nampa)",
            "209" => "California (Fresno, Stockton)",
            "210" => "Texas (San Antonio)",
            "212" => "New York (New York, Bronx)",
            "213" => "California (Los Angeles, Burbank)",
            "214" => "Texas (Dallas, Fort Worth)",
            "215" => "Pennsylvania (Philadelphia, Lansdale)",
            "216" => "Ohio (Cleveland, Beachwood)",
            "217" => "Illinois (Chicago, Champaign)",
            "218" => "Minnesota (Duluth, Wolverton)",
            "219" => "Indiana (Valparaiso, Portage)",
            "224" => "Illinois (Chicago, Elgin)",
            "225" => "Louisiana (Baton Rouge, Geismar)",
            "228" => "Mississippi (Gulfport, Biloxi)",
            "229" => "Georgia (Albany, Valdosta)",
            "231" => "Michigan (Muskegon, Traverse City)",
            "234" => "Ohio (Akron, Canton)",
            "239" => "Florida (Fort Myers, Ft Myers)",
            "240" => "Maryland (Baltimore, Rockville)",
            "248" => "Michigan (Detroit, Troy)",
            "251" => "Alabama (Mobile, Theodore)",
            "252" => "North Carolina (Greenville, Rocky Mount)",
            "253" => "Washington (Seattle, Tacoma)",
            "254" => "Texas (Killeen, Waco)",
            "256" => "Alabama (Huntsville, Gadsden)",
            "260" => "Indiana (Fort Wayne, Columbia City)",
            "262" => "Wisconsin (Milwaukee, Kenosha)",
            "267" => "Pennsylvania (Philadelphia, Norristown)",
            "269" => "Michigan (Kalamazoo, Battle Creek)",
            "270" => "Kentucky (Bowling Green, Owensboro)",
            "272" => "Pennsylvania (Lake Ariel, Beaver Spgs)",
            "276" => "Virginia (Martinsville, Bristol)",
            "281" => "Texas (Houston, Spring)",
            "301" => "Maryland (Baltimore, Rockville)",
            "302" => "Delaware (Wilmington, Bear)",
            "303" => "Colorado (Denver, Aurora)",
            "304" => "West Virginia (Charleston, Falling Waters)",
            "305" => "Florida (Miami, Fort Lauderdale)",
            "307" => "Wyoming (Casper, Cheyenne)",
            "308" => "Nebraska (Grand Island, Kearney)",
            "309" => "Illinois (Peoria, Bloomington)",
            "310" => "California (Los Angeles, Long Beach)",
            "312" => "Illinois (Chicago, Wheeling)",
            "313" => "Michigan (Detroit, Livonia)",
            "314" => "Missouri (Saint Louis, Saint Charles)",
            "315" => "New York (Syracuse, Utica)",
            "316" => "Kansas (Wichita, Rose Hill)",
            "317" => "Indiana (Indianapolis, Greenwood)",
            "318" => "Louisiana (Shreveport, Monroe)",
            "319" => "Iowa (Cedar Rapids, Cedar Falls)",
            "320" => "Minnesota (Saint Cloud, Farwell)",
            "321" => "Florida (Orlando, Kissimmee)",
            "323" => "California (Los Angeles, Long Beach)",
            "325" => "Texas (Abilene, San Angelo)",
            "330" => "Ohio (Akron, Canton)",
            "331" => "Illinois (Lemont, Aurora)",
            "334" => "Alabama (Montgomery, Dothan)",
            "336" => "North Carolina (Greensboro, Winston Salem)",
            "337" => "Louisiana (Lafayette, Lake Charles)",
            "339" => "Massachusetts (Boston, Malden)",
            "340" => "Virgin Islands (Charlotte Amalie, St Croix)",
            "346" => "Texas (Houston, Sugar Land)",
            "347" => "New York (Brooklyn, New York)",
            "351" => "Massachusetts (Danvers, Pepperell)",
            "352" => "Florida (Gainesville, Ocala)",
            "360" => "Washington (Seattle, Vancouver)",
            "361" => "Texas (Corpus Christi, Victoria)",
            "385" => "Utah (Salt Lake City, Ogden)",
            "386" => "Florida (Daytona Beach, Deltona)",
            "401" => "Rhode Island (Providence, East Greenwich)",
            "402" => "Nebraska (Omaha, Lincoln)",
            "404" => "Georgia (Atlanta, Stone Mountain)",
            "405" => "Oklahoma (Oklahoma City, Edmond)",
            "406" => "Montana (Billings, Bozeman)",
            "407" => "Florida (Orlando, Kissimmee)",
            "408" => "California (San Jose, Fremont)",
            "409" => "Texas (Beaumont, Nederland)",
            "410" => "Maryland (Baltimore, Beltsville)",
            "412" => "Pennsylvania (Pittsburgh, Bethel Park)",
            "413" => "Massachusetts (Springfield, Holyoke)",
            "414" => "Wisconsin (Milwaukee, Waukesha)",
            "415" => "California (San Francisco, Oakland)",
            "417" => "Missouri (Springfield, Joplin)",
            "419" => "Ohio (Toledo, Mansfield)",
            "423" => "Tennessee (Chattanooga, Kingsport)",
            "424" => "California (Los Angeles, Long Beach)",
            "425" => "Washington (Seattle, Everett)",
            "430" => "Texas (Longview, Kemp)",
            "432" => "Texas (Midland, Odessa)",
            "434" => "Virginia (Lynchburg, Charlottesville)",
            "435" => "Utah (Park City, St George)",
            "440" => "Ohio (Cleveland, North Royalton)",
            "442" => "California (Victorville, Hesperia)",
            "443" => "Maryland (Baltimore, Pasadena)",
            "458" => "Oregon (Eugene, Bend)",
            "469" => "Texas (Dallas, Grand Prairie)",
            "470" => "Georgia (Atlanta, Marietta)",
            "475" => "Connecticut (New Haven, Bridgeport)",
            "478" => "Georgia (Macon, Warner Robins)",
            "479" => "Arkansas (Springdale, Fayetteville)",
            "480" => "Arizona (Phoenix, Mesa)",
            "484" => "Pennsylvania (Philadelphia, Reading)",
            "501" => "Arkansas (Little Rock, Hot Springs)",
            "502" => "Kentucky (Louisville, Georgetown)",
            "503" => "Oregon (Portland, Beaverton)",
            "504" => "Louisiana (New Orleans, Kenner)",
            "505" => "New Mexico (Albuquerque, Santa Fe)",
            "507" => "Minnesota (Rochester, Mankato)",
            "508" => "Massachusetts (Boston, Cambridge)",
            "509" => "Washington (Spokane, Yakima)",
            "510" => "California (San Jose, San Francisco)",
            "512" => "Texas (Austin, Georgetown)",
            "513" => "Ohio (Cincinnati, Hamilton)",
            "515" => "Iowa (Des Moines, Ames)",
            "516" => "New York (Springfield Gardens, Seaford)",
            "517" => "Michigan (Lansing, Jackson)",
            "518" => "New York (Schenectady, Albany)",
            "520" => "Arizona (Phoenix, Tucson)",
            "530" => "California (Sacramento, Roseville)",
            "531" => "Nebraska (Lincoln)",
            "534" => "Wisconsin (Chippewa Falls)",
            "539" => "Oklahoma (Tulsa)",
            "540" => "Virginia (Fredericksburg, Roanoke)",
            "541" => "Oregon (Eugene, Medford)",
            "551" => "New Jersey (Newark, Jersey City)",
            "559" => "California (Fresno, Tulare)",
            "561" => "Florida (West Palm Beach, Pompano Beach)",
            "562" => "California (Los Angeles, Long Beach)",
            "563" => "Iowa (Davenport, Dubuque)",
            "567" => "Ohio (Toledo, Mansfield)",
            "570" => "Pennsylvania (Wilkes Barre, Williamsport)",
            "571" => "Virginia (Alexandria, Woodbridge)",
            "573" => "Missouri (Columbia, Jefferson City)",
            "574" => "Indiana (South Bend, Elkhart)",
            "575" => "New Mexico (Las Cruces, Clovis)",
            "580" => "Oklahoma (Oklahoma City, Lawton)",
            "585" => "New York (Buffalo, Rochester)",
            "586" => "Michigan (Detroit, Roseville)",
            "601" => "Mississippi (Jackson, Brandon)",
            "602" => "Arizona (Phoenix, Chandler)",
            "603" => "New Hampshire (Manchester, Nashua)",
            "605" => "South Dakota (Sioux Falls, Rapid City)",
            "606" => "Kentucky (Sharpsburg, Salt Lick)",
            "607" => "New York (Freeville, Binghamton)",
            "608" => "Wisconsin (Madison, Fitchburg)",
            "609" => "New Jersey (Camden, Trenton)",
            "610" => "Pennsylvania (Philadelphia, Reading)",
            "612" => "Minnesota (Minneapolis, Saint Paul)",
            "614" => "Ohio (Columbus, Galloway)",
            "615" => "Tennessee (Nashville, Clarksville)",
            "616" => "Michigan (Grand Rapids, Holland)",
            "617" => "Massachusetts (Boston, Malden)",
            "618" => "Illinois (Belleville, East Saint Louis)",
            "619" => "California (San Diego, Chula Vista)",
            "620" => "Kansas (Hutchinson, Garden City)",
            "623" => "Arizona (Phoenix, Chandler)",
            "626" => "California (Los Angeles, Anaheim)",
            "630" => "Illinois (Chicago, Elgin)",
            "631" => "New York (Deer Park, Hicksville)",
            "636" => "Missouri (Saint Louis, O Fallon)",
            "641" => "Iowa (Beaman, Marshalltown)",
            "646" => "New York (Brooklyn, New York)",
            "650" => "California (San Jose, San Francisco)",
            "651" => "Minnesota (Minneapolis, Saint Paul)",
            "657" => "California (Long Beach, Santa Ana)",
            "660" => "Missouri (Otterville, Sedalia)",
            "661" => "California (Bakersfield, Palmdale)",
            "662" => "Mississippi (Southaven, Olive Branch)",
            "667" => "Maryland (Baltimore, Pikesville)",
            "669" => "California (San Jose, Fremont)",
            "670" => "Northern Mariana Islands (Saipan)",
            "671" => "Guam (Agana, Agana Heights)",
            "678" => "Georgia (Atlanta, Marietta)",
            "681" => "West Virginia (Charleston, Falling Waters)",
            "682" => "Texas (Dallas, Fort Worth)",
            "684" => "American Samoa (Pago Pago)",
            "701" => "North Dakota (Fargo, Bismarck)",
            "702" => "Nevada (Las Vegas, Henderson)",
            "703" => "Virginia (Alexandria, Woodbridge)",
            "704" => "North Carolina (Charlotte, Concord)",
            "706" => "Georgia (Augusta, Columbus)",
            "707" => "California (Santa Rosa, Fairfield)",
            "708" => "Illinois (Chicago, Lemont)",
            "712" => "Iowa (Sioux City, Carter Lake)",
            "713" => "Texas (Houston, Spring)",
            "714" => "California (Long Beach, Santa Ana)",
            "715" => "Wisconsin (Green Bay, Elk Mound)",
            "716" => "New York (Buffalo, Sanborn)",
            "717" => "Pennsylvania (Lancaster, York)",
            "718" => "New York (Brooklyn, New York)",
            "719" => "Colorado (Colorado Springs, Pueblo)",
            "720" => "Colorado (Denver, Aurora)",
            "724" => "Pennsylvania (Pittsburgh, Greensburg)",
            "725" => "Nevada (Las Vegas, Mt Charleston)",
            "727" => "Florida (Tampa, Saint Petersburg)",
            "731" => "Tennessee (Jackson, Ripley)",
            "732" => "New Jersey (Newark, New Brunswick)",
            "734" => "Michigan (Detroit, Livonia)",
            "737" => "Texas (Austin, Georgetown)",
            "740" => "Ohio (Columbus, Powell)",
            "747" => "California (Los Angeles, Van Nuys)",
            "754" => "Florida (Fort Lauderdale, Hollywood)",
            "757" => "Virginia (Virginia Beach, Norfolk)",
            "760" => "California (San Diego, Victorville)",
            "762" => "Georgia (Augusta, Columbus)",
            "763" => "Minnesota (Minneapolis, Roseville)",
            "765" => "Indiana (Indianapolis, West Lafayette)",
            "769" => "Mississippi (Jackson, Brandon)",
            "770" => "Georgia (Atlanta, Marietta)",
            "772" => "Florida (Port Saint Lucie, Port St Lucie)",
            "773" => "Illinois (Chicago, Arlington Heights)",
            "774" => "Massachusetts (Boston, New Bedford)",
            "775" => "Nevada (Reno, Sparks)",
            "779" => "Illinois (Rockford, Joliet)",
            "781" => "Massachusetts (Boston, Malden)",
            "785" => "Kansas (Topeka, Lawrence)",
            "786" => "Florida (Miami, Fort Lauderdale)",
            "787" => "Puerto Rico (Bayamon, Rio Piedras)",
            "801" => "Utah (Salt Lake City, Ogden)",
            "802" => "Vermont (Shelburne, Burlington)",
            "803" => "South Carolina (Columbia, Clover)",
            "804" => "Virginia (Richmond, Midlothian)",
            "805" => "California (Bakersfield, Oxnard)",
            "806" => "Texas (Lubbock, Amarillo)",
            "808" => "Hawaii (Honolulu, Waipahu)",
            "810" => "Michigan (Flint, Southfield)",
            "812" => "Indiana (Evansville, Bloomington)",
            "813" => "Florida (Tampa, Clearwater)",
            "814" => "Pennsylvania (Erie, State College)",
            "815" => "Illinois (Chicago, Rockford)",
            "816" => "Missouri (Kansas City, Independence)",
            "817" => "Texas (Dallas, Fort Worth)",
            "818" => "California (Los Angeles, Van Nuys)",
            "828" => "North Carolina (Asheville, Hickory)",
            "830" => "Texas (New Braunfels, Mc Queeney)",
            "831" => "California (Salinas, Santa Cruz)",
            "832" => "Texas (Houston, Spring)",
            "843" => "South Carolina (Charleston, Myrtle Beach)",
            "845" => "New York (Poughkeepsie, Newburgh)",
            "847" => "Illinois (Chicago, Elgin)",
            "848" => "New Jersey (Newark, New Brunswick)",
            "850" => "Florida (Pensacola, Tallahassee)",
            "856" => "New Jersey (Camden, Vineland)",
            "857" => "Massachusetts (Boston, Malden)",
            "858" => "California (San Diego, Poway)",
            "859" => "Kentucky (Lexington, Covington)",
            "860" => "Connecticut (Hartford, New London)",
            "862" => "New Jersey (Newark, Jersey City)",
            "863" => "Florida (Kissimmee, Lakeland)",
            "864" => "South Carolina (Greenville, Greer)",
            "865" => "Tennessee (Knoxville, Maryville)",
            "870" => "Arkansas (Jonesboro, Pine Bluff)",
            "872" => "Illinois (Chicago)",
            "878" => "Pennsylvania (Pittsburgh, Greensburg)",
            "901" => "Tennessee (Memphis, Millington)",
            "903" => "Texas (Tyler, Longview)",
            "904" => "Florida (Jacksonville, Saint Augustine)",
            "906" => "Michigan (Deerton, Marquette)",
            "907" => "Alaska (Anchorage, Fort Richardson)",
            "908" => "New Jersey (Newark, Jersey City)",
            "909" => "California (Riverside, Victorville)",
            "910" => "North Carolina (Fayetteville, Wilmington)",
            "912" => "Georgia (Savannah, Richmond Hill)",
            "913" => "Kansas (Kansas City, Overland Park)",
            "914" => "New York (Manhattan, Yonkers)",
            "915" => "Texas (El Paso, Clint)",
            "916" => "California (Sacramento, Folsom)",
            "917" => "New York (Brooklyn, New York)",
            "918" => "Oklahoma (Tulsa, Broken Arrow)",
            "919" => "North Carolina (Raleigh, Durham)",
            "920" => "Wisconsin (Milwaukee, Green Bay)",
            "925" => "California (Oakland, Fremont)",
            "928" => "Arizona (Phoenix, Tucson)",
            "929" => "New York (Brooklyn, New York)",
            "931" => "Tennessee (Clarksville, Spring Hill)",
            "936" => "Texas (Conroe, Nacogdoches)",
            "937" => "Ohio (Dayton, Springfield)",
            "938" => "Alabama (Goodwater, Red Bay)",
            "939" => "Puerto Rico (Bayamon, Rio Piedras)",
            "940" => "Texas (Denton, Frisco)",
            "941" => "Florida (Sarasota, Bradenton)",
            "947" => "Michigan (Southfield, Clarkston)",
            "949" => "California (Santa Ana, Anaheim)",
            "951" => "California (Riverside, San Bernardino)",
            "952" => "Minnesota (Minneapolis, Saint Paul)",
            "954" => "Florida (Fort Lauderdale, Hollywood)",
            "956" => "Texas (Laredo, Mcallen)",
            "970" => "Colorado (Ft Collins, Greeley)",
            "971" => "Oregon (Portland, Beaverton)",
            "972" => "Texas (Dallas, Fort Worth)",
            "973" => "New Jersey (Newark, Jersey City)",
            "978" => "Massachusetts (Boston, Cambridge)",
            "979" => "Texas (College Station, Bryan)",
            "980" => "North Carolina (Charlotte, Concord)",
            "984" => "North Carolina (Raleigh)",
            "985" => "Louisiana (New Orleans, Slidell)",
            "989" => "Michigan (Saginaw, Bay City)"
        );

        if(array_key_exists($phoneCode,$phoneCodeList)){
            $areaName = $phoneCodeList[$phoneCode];
        }else{
            $areaName = '';
        }
        return $areaName;
    }

    /*
     * Function for finding the location based on the IP Address
     * takes the IP Address as input and outputs the location
     * by masud_moni
     * */

    public static function getAddressByIp($ip)
    {
        $url = "http://ipinfo.io/".$ip."/json";
        $json = @file_get_contents($url);
        $data=json_decode($json);
        
        if(isset($data->country) && $data->country != ''){
            $location = $data->country;
        }else{
            $location = '';
        }

        if(isset($data->city) && $data->city != ''){
            $location = $location . '/' . $data->city;
        }
        elseif(isset($data->region) && $data->region != ''){
            $location = $location . '/' . $data->region;
        }else{
            $location = $location;
        }

        return $location;
    }
    /**
     * @param $path
     */
    public static function createDirectory($path){
        if (!file_exists($path)) {
            try{
                mkdir($path, 0777, true);
            }catch (Exception $ex){
                //print $ex->getMessage();
            }
        }
    }

    public static function  DeleteDirectoryContents($dir){

        $files = scandir($dir); // get all property file names

        foreach($files as $file){
            @unlink($dir."/".$file); // delete file
        }
    }

}