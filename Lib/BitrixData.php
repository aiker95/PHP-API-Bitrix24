<?php
/**
 * Created by PhpStorm.
 * User: ermilov
 * Date: 30.03.2019
 * Time: 18:21
 */

namespace App\BitrixClasses\Lib;

/**
 * Class BitrixData
 * @package App\Lib
 * Класс представляет из себя формат данных в запросе к API битрикса, со всеми его необходимыми свойствами
 *
 *
 */
class BitrixData extends FormData 
{
    
    /**
    * @var Номер задачи
    */
    public   $TASKID = '';
    public   $ID = '';
    public   $EMAIL = '';
    public   $EXTRANET = '';
    public   $SONET_GROUP_ID = '';
    /**
    * @var array Параметры задачи отправляемой в API битрикх
    */
    public  $TASKDATA = [
        'TITLE'=>'',
        'DESCRIPTION'=>'',
        'DEADLINE'=>'',
        'START_DATE_PLAN'=>'',
        'END_DATE_PLAN'=>'',
        'PRIORITY'=>'',
        'ACCOMPLICES'=>'',
        'AUDITORS'=>'',
        'TAGS'=>'',
        'ALLOW_CHANGE_DEADLINE'=>'',
        'TASK_CONTROL'=>'',
        'PARENT_ID'=>'',
        'GROUP_ID'=>'',
        'RESPONSIBLE_ID'=>'',
        'CREATED_BY'=>'',
        'STATUS'=>'',
        'ALLOW_TIME_TRACKING' => '',
        'MATCH_WORK_TIME'=>'',
        'UF_CRM_TASK'=>''
    ];
    
    public $FIELDS = [
        'AUTHOR_ID'=>'',
        'POST_MESSAGE'=>''
    ];
    
    public $FILTER = [
        'NAME'=>'',
        'NAME_SEARCH'=>''
        
    ];
    
    protected function changeTitle($value){
       return $value;
    }
    
    protected function changeEmail($value){
        return trim($value);    
    }
    
    protected function changeTags($value) {
        $value = explode('[*]', $value);
        $value = array_diff($value, array(''));
        
        return $value;
    }
    
    protected function changeAuditors($value){
        $value = explode(',', $value);
        $value = array_diff($value, array(''));
        
        return $value;
    }
    
    protected function changeSonet_group_id($value){
      return  $this->changeAuditors($value);
    }
    
    protected function changeUf_crm_task($value){
        return $this->changeAuditors($value);
    }
    
    protected function changeAccomplices($value){
        return $this->changeAuditors($value);
    }
    
    protected  function changeDeadline($value){
        $value = trim($value);
        $value = str_replace(" ","T",$value);
        
        return $value;
    }
    
    protected  function changeStart_date_plan($value){
        return $this->changeDeadline($value);
    }
    
    protected function changeEnd_date_plan($value){
        return $this->changeDeadline($value);
    }
    
    protected function changeAllow_change_deadline($value){
        return  $value ? $value = 'Y' : $value = 'N';
    }
    
    protected function changeMatch_work_time($value){
        return $this->changeAllow_change_deadline($value);
    }
    
    protected function changeTask_control($value){
        return ! $value ? $value = 'Y' : $value = 'N';
    }
    
    protected function changePriority($value){
      return $value ? $value = 2 : $value = 1; 
    }
    
    protected function changeAllow_time_tracking($value){
        return $this->changeAllow_change_deadline($value);
    }
}