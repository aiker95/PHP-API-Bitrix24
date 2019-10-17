<?php
namespace App\BitrixClasses\Lib;

/**
 * Абстрактный класс позволяющий формировать необходимую структуру данных
 * Для форматирования значений свойств во время добавления их в объект структуры данных необходимо в дочернем классе прописать
 * функцию типа protected с форматом имени change + Имя свойства которое необходимо поменять  с первой заглавной буквой 
 * остальные маленькие пример: Имя свойства TASKID  то функция будет changeTaskid($value)  и с параметром в которое будет переданно 
 * значение для данного свойства.    
 * @author ermilov.du
 *
 */


abstract class FormData {
   
    
    public function __call($name, $arguments){
        
        return false;
    }
    
    /**
     * Функция добавления свойств с проверкой на пустоту
     *
     * @param $property
     * @param $value
     * @return array error|bool
     */
    public function add($property,  $value){
       
        foreach ($this as $propertyName => $valueName){

            if ($property == $propertyName) {
              
                $funcName = 'change'.ucfirst(strtolower($property));
                
                $changeValue = $this->$funcName($value);
                
                $changeValue ? $value = $changeValue : $value;
                
                $this->$property = $value;
                return $this;
            }
            
            if (is_array($this->$propertyName)){
                if (array_key_exists($property, $this->$propertyName) and isset($value)){
                    
                    $funcName = 'change'.ucfirst(strtolower($property));
                    
                    $changeValue = $this->$funcName($value);
                    
                    $changeValue ? $value = $changeValue : $value;
                    
                    $this->$propertyName[$property] = $value;
                    return $this;
                }
            }
        }
        return $this;
    }
    
    
    /**
     * Возвращает объект только с не пустыми свойствами
     *
     * @return $this
     *
     */
    public function deleteEmpty(){
        
        foreach ($this as $propertyName => $valueName){
            
            if (empty($valueName)){
                unset($this->$propertyName);
                continue;
            }
            
            if (is_array($this->$propertyName)){
                foreach ($this->$propertyName as $key => $value){
                    if (empty($value)){
                        unset($this->$propertyName[$key]);
                    }
                }
                if (empty($this->$propertyName))  unset($this->$propertyName);

            }
        }
        return $this;
    }
    
    private function error($property, $value){
        if (empty($property)) return ['error' => 'Поле '.$property.' не поддерживается'];
        if (empty($value)) return ['error' => 'Значение в поля '.$property. ' не может быть пустым'];
    }
} 