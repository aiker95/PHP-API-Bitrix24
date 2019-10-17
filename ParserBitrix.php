<?php
namespace App\BitrixClasses;

class ParserBitrix
{
    /**Функция обработки CSV файла
     * 
     * @param $path
     * @return $data 
     */
    
    public function submit($path){ 
        //$file1=$res->file->getRealPath();
              
        //Имеющийся массив-эталон, по которому должна происходить сортировка(вводные данные)
        $etalon = ["TITLE", "DESCRIPTION", "DEADLINE", "START_DATE_PLAN", "END_DATE_PLAN", "PRIORITY", "ACCOMPLICES",
            'AUDITORS', 'TAGS', 'ALLOW_CHANGE_DEADLINE', 'TASK_CONTROL', 'PARENT_ID', 'DEPENDS_ON', 'GROUP_ID', 'RESPONSIBLE_ID',
            'TIME_ESTIMATE', 'CREATED_BY', 'DECLINE_REASON', 'STATUS', 'DURATION_PLAN', 'DURATION_TYPE', 'MARK', 'ALLOW_TIME_TRACKING',
            'ADD_IN_REPORT', 'SITE_ID', 'MATCH_WORK_TIME'];
        
        //1.Получение содержимого файла csv в строку(с file не работает, т.к. неправильно разделение строк, не по PHP_EOL(\n), explode() тоже не годится)
        //По пути $path
        $csv=file_get_contents($path);
        
        //2.Разделение строки в массив по разделителю перехода строки(End Of Line) 
        $file = str_getcsv($csv, PHP_EOL);
        
        //Вывод результата на экран
        dump($csv);
         
        //3.Получение строки заголовков
        $hdr= str_getcsv(array_shift($file), ";");
         
        //Инициализация пустого массива
        $data=array();
        
        //4.Заполнение массива отсортированными строками с расставлением заголовков(ключей)+сортировкой по эталонному массиву
        foreach ($file as $line){
            //расставление заголовков
            $data[]=array_combine($hdr,  str_getcsv($line, ";"));
            //удаление пустых элементов в массивах в массиве и удаление пустых массивов при помощи array_map 
            //и двойного использования array_filter(поскольку условие empty() и isset() не помогло)
            $data=array_filter(array_map('array_filter', $data));
            //сортировка элементов в каждом массиве по массиву-эталону
            foreach ($data as $arrayElement){
                    $this->sortArrayByArray($arrayElement, $etalon);
                //Создание массива из строки с ключом TAGS по разделителю [*]
                if (array_key_exists('TAGS', $arrayElement)){
                    $arrayElement['TAGS']=array_filter(explode("[*]", $arrayElement['TAGS']));
                }
            }
        }
 
        //5.Вывод результата на экран
        dump($data);
    }
     
    /*Говнокод здесь
     *
     */
    /**Функция сортировки массива $array по массиву-эталону $orderArray
     * 
     * @param array $array
     * @param array $orderArray
     * @return array
     */
    
    public function sortArrayByArray(array $array, array $orderArray) {
        //инициализация пустого массива, куда элементы будут добавляться в зависимости от порядка в массиве-эталоне
        $ordered = array();
        //цикл с сортировкой элементов 
        foreach ($orderArray as $key) {  
            //Проверка наличия содержания ключей в вводном массиве по массиву-эталону
            if (array_key_exists($key, $array)) {     
                    //Если в массиве есть данный ключ, то происходит расстановка по порядку, указанному в массиве-эталоне
                    $ordered[$key] = $array[$key];         
                //Если данного ключа нет, удаление элемента
                unset($array[$key]);
            }
        }
        //Возвращение отсортированного массива, в котором нет элементов со значениями null, '', false
        return array_diff($ordered, array(null, '', false)) 
        //+ $array
        ;
    }   
}