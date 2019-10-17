<?php
/**
 * Created by PhpStorm.
 * User: ermilov
 * Date: 29.03.2019
 * Time: 21:56
 */

namespace App\BitrixClasses;


use App\BitrixClasses\Parser\ParseMod;
use App\BitrixClasses\Lib\BitrixData;
use App\BitrixClasses\Lib\BitrixRequest;
use File;
use App\Http\Controllers\BitrixController;
use App\Models\Bitrix\BitrixLogs;

class BitrixAPI
{
    /**
     * Bitrix token
     */
    protected  $token = null;

    protected  $bitrixRequest;

    protected $bitrixData;

    protected $parser;

    public function __construct() {
        $this->token = config('bitrix.bitrix_token', false);
        $this->bitrixRequest = new BitrixRequest(null, $this->token);

    }

    public function handler(BitrixData $bitrixData = null, $method, BitrixLogs $bitrixLogs = null){
       /*
        *Включаем метод замены email на id пользователя
        */
        if ($bitrixData){
            if ((isset($bitrixData->TASKDATA))) $bitrixData = $this->changeEmail($bitrixData, clone $bitrixLogs);
        }

        switch ($method){

            case "createTask":
                $res =  $this->taskCreate($bitrixData, $bitrixLogs);
                break;
            case "updateTask":
                $res =  $this->taskUpdate($bitrixData, $bitrixLogs);
                break;
            case "getTask":
                $res = $this->getTask($bitrixData, $bitrixLogs);
                break;
            case "deleteTask":
                $res = $this->taskDelete($bitrixData, $bitrixLogs);
                break;
            case "inviteUsers":
                $res = $this->inviteUser($bitrixData, $bitrixLogs);
                break;
            case "getUsers":
                $res = $this->getUser($bitrixData, $bitrixLogs);
                break;
            case "addComments":
                $res = $this->createComment($bitrixData, $bitrixLogs);
                break;
        }
        return $res;
    }


    public function taskCreate(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null) {

       $response = $this->bitrixRequest->sendRequest('task.item.add', $bitrixData);
       array_key_exists('result', $response) ? $result = $response['result'] : $result = $response;
       if ($bitrixLogs)  $this->logs($bitrixLogs, 'task', 'create', $result);

       return $result;
    }

    public function taskUpdate(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null) {

       $response = $this->bitrixRequest->sendRequest('task.item.update', $bitrixData);
       array_key_exists('result', $response) ? $result = $bitrixData->TASKID : $result = $response;
       if ($bitrixLogs)  $this->logs($bitrixLogs, 'task', 'update', $result);

       return $result;
    }

    public function taskDelete(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null) {

        $response = $this->bitrixRequest->sendRequest('task.item.delete', $bitrixData);
        array_key_exists('result', $response) ? $result = $bitrixData->TASKID : $result = $response;
        if ($bitrixLogs)  $this->logs($bitrixLogs, 'task', 'delete', $result);

        return $result;
    }

    public function getTask(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null) {

      $response = $this->bitrixRequest->sendRequest('task.item.getdata', $bitrixData);
      if (array_key_exists('result', $response)){
          $result = $response['result'];
          if ($bitrixLogs)  $this->logs($bitrixLogs, 'task', 'getdata', $result['ID']);
      }else{
          $result = $response;
          if ($bitrixLogs)  $this->logs($bitrixLogs, 'task', 'getdata', $result);
          }

      return $result;
    }

    public function getUser(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null){

       $bitrixData->ADMIN_MODE='1'; //Битриксойдный костыль
       $response = $this->bitrixRequest->sendRequest('user.get', $bitrixData);
       if (!empty($response['result'])){
           $result = $response['result'][0];
           if ($bitrixLogs)  $this->logs($bitrixLogs, 'user', 'get', $result['ID']);
       }else
       {
          $result = false;
          if ($bitrixLogs)  $this->logs($bitrixLogs, 'user', 'get', ['error_description'=>'Пользователь не найден']);
       }

       return $result;
    }

    public function inviteUser(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null) {

       $response = $this->bitrixRequest->sendRequest('user.add', $bitrixData);
       array_key_exists('result', $response) ? $result = $response['result'] : $result = $response;
       if ($bitrixLogs)  $this->logs($bitrixLogs, 'user', 'add', $result);

       return $result;
    }

    public function getGroup(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null){

        $response = $this->bitrixRequest->sendRequest('sonet_group.get', $bitrixData);
        if (!empty($response['result'])){
            $result = $response['result'][0];
            if ($bitrixLogs)  $this->logs($bitrixLogs, 'group', 'get', $result['ID']);
        }else{
            $result = false;
            if ($bitrixLogs)  $this->logs($bitrixLogs, 'group', 'get', ['error_description'=>' Группа не найдена']);
        }
        return $result;
    }

    public function createComment(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null){

        $this->bitrixRequest->sendRequest('task.commentitem.add', $bitrixData);
    }

    public function getComment(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null){

        $this->bitrixRequest->sendRequest('task.commentitem.get', $bitrixData);
    }

    public function updateComment(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null){

        $this->bitrixRequest->sendRequest('task.commentitem.update', $bitrixData);
    }

    public function deleteComment(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null){

        $this->bitrixRequest->sendRequest('task.commentitem.delete', $bitrixData);
    }


    /**
     * Функция для логирования запросов в API битрикса
     *
     * @param string $type
     * @param string $method
     * @param object $bitrixLogs
     * @param int|string $id
     *
     * @return void
     */
    protected function logs(BitrixLogs $bitrixLogs, $type, $method, $result){

        if (is_array($result)){
            if (array_key_exists('error_description', $result)){
               $bitrixLogs->error = implode( ', ', $result );
               $bitrixLogs->type = $type;
               $bitrixLogs->method = $method;

               return $bitrixLogs->save();
            }
        }
            $bitrixLogs->id = $result;
            $bitrixLogs->type = $type;
            $bitrixLogs->method = $method;
            return $bitrixLogs->save();
    }

    /**
     * Функция заменяет все вхождения email в полях RESPONSIBLE_ID, CREATED_ID, AUDITORS на
     * id пользователся битрикс если такой имеется
     *
     * @param BitrixData $bitrixData
     * @return BitrixData
     */
    public function changeEmail(BitrixData $bitrixData, BitrixLogs $bitrixLogs = null){

        if (array_key_exists('RESPONSIBLE_ID', $bitrixData->TASKDATA)){
            if (preg_match("/@/", $email = $bitrixData->TASKDATA['RESPONSIBLE_ID'])){
                $bitrixData = $this->changeEmailForProperty(trim($email), $bitrixData, 'RESPONSIBLE_ID', $bitrixLogs);
                if ($bitrixData === false) return $email;
            }
            if (preg_match("/[А-я]+ [А-я]+ [А-я]+/", $fio = $bitrixData->TASKDATA['RESPONSIBLE_ID'])){
                $bitrixData = $this->changeFioForProperty(trim($fio), $bitrixData, 'RESPONSIBLE_ID', $bitrixLogs);
                if ($bitrixData === false) return $fio;
            }
        }

        if (array_key_exists('CREATED_BY', $bitrixData->TASKDATA)){
            if (preg_match("/@/", $email = $bitrixData->TASKDATA['CREATED_BY'])){
                $bitrixData = $this->changeEmailForProperty($email, $bitrixData, 'CREATED_BY', $bitrixLogs);
                if ($bitrixData === false) return $email;
            }
            if (preg_match("/[А-я]+ [А-я]+ [А-я]+/", $fio = $bitrixData->TASKDATA['CREATED_BY'])){
                $bitrixData = $this->changeFioForProperty(trim($fio), $bitrixData, 'CREATED_BY', $bitrixLogs);
                if ($bitrixData === false) return $fio;
            }
        }

        if (array_key_exists('AUDITORS', $bitrixData->TASKDATA)){
            foreach ($bitrixData->TASKDATA['AUDITORS'] as $key => $auditor){
                $bitrixData->TASKDATA['AUDITORS'][$key] = trim($auditor);
                if (preg_match("/@/", $auditor)){
                    $result = $this->changeEmailForProperty($auditor, null, null, $bitrixLogs);
                    if ($result === false) return $auditor;
                    $bitrixData->TASKDATA['AUDITORS'][$key] = $result;
                }
                if (preg_match("/[А-я]+ [А-я]+ [А-я]+/", $auditor)){
                    $result = $this->changeFioForProperty($auditor, null, null, $bitrixLogs);
                    if ($result === false) return $auditor;
                    $bitrixData->TASKDATA['AUDITORS'][$key] = $result;
                }
            }
        }

        if (array_key_exists('ACCOMPLICES', $bitrixData->TASKDATA)){
            foreach ($bitrixData->TASKDATA['ACCOMPLICES'] as $key => $accomplice){
                $bitrixData->TASKDATA['ACCOMPLICES'][$key] = trim($accomplice);
                if (preg_match("/@/", $accomplice)){
                    $result = $this->changeEmailForProperty($accomplice, null, null, $bitrixLogs);

                    if ($result === false) return $accomplice;
                    $bitrixData->TASKDATA['ACCOMPLICES'][$key] = $result;
                }
                if (preg_match("/[А-я]+ [А-я]+ [А-я]+/", $accomplice)){
                    $result = $this->changeFioForProperty($accomplice, null, null, $bitrixLogs);
                    if ($result === false) return $accomplice;
                    $bitrixData->TASKDATA['ACCOMPLICES'][$key] = $result;
                }
            }
        }
        return $bitrixData;
    }

    /**
     * Функция принимает на вход email в формате строки, преобразует его в формат BitrixData,
     * и возвращает id  пользователя. При указании параметров BitrixData и property (должны указываться вместе) результат
     * будет автоматически добавлен в указанное свойство переданного объекта BitrixData с условием что $property = элементу
     * массива TASKDATA.
     *
     * @param string $email
     * @param BitrixData $bitrixData
     * @param string $property
     * @return \App\BitrixClasses\Lib\BitrixData|int| bool
     */
    private function changeEmailForProperty($email, BitrixData $bitrixData = null, $property = null, BitrixLogs $bitrixLogs = null){
        $email = trim($email);
        $emailBitrix = new BitrixData();
        $response = $this->getUser($emailBitrix->add('EMAIL', $email)->deleteEmpty(), $bitrixLogs);
        if ($response !== false){
            $idUser = $response['ID'];
            if ($property and $bitrixData) {
                 $bitrixData->TASKDATA[$property] = $idUser;
                 return $bitrixData;
            }
            return $idUser;
        }
        return false;
    }

    private function changeFioForProperty($fio, BitrixData $bitrixData = null, $property = null, BitrixLogs $bitrixLogs = null){
        $fio = trim($fio);
        $fioBitrix = new BitrixData();
        $response = $this->getUser($fioBitrix->add('NAME_SEARCH', $fio)->deleteEmpty(), $bitrixLogs);
        if ($response !== false){
            $idUser = $response['ID'];
            if ($property and $bitrixData) {
                $bitrixData->TASKDATA[$property] = $idUser;
                return $bitrixData;
            }
            return $idUser;
        }
        return false;
    }

    private function logResponse($file, array $data) {

        $contents = implode(";", $data)."\n";

        File::append($file, $contents);
    }


}
