<?php
/**
 * Created by PhpStorm.
 * User: ermilov
 * Date: 29.03.2019
 * Time: 20:24
 */

namespace App\BitrixClasses\Parser;

use App\BitrixClasses\Lib\BitrixData;
use App\BitrixClasses\Lib\ReportData;
use App\BitrixClasses\Lib\MailData;

class ParseMod
{
    protected $delimiter;

    protected $pathFile;

    protected $data;

    protected $bitrixData;

    protected $accordance;

    public function __construct($pathFile, bool $accordance = false, $delimiter = ';')
    {
        $this->pathFile = $pathFile;
        $this->delimiter = $delimiter;
        $this->accordance = config('parser_accordance', false);
        $this->FormatToArray();
        if ($accordance)  $this->swap($this->accordance);
    }

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }


    public function getContent()
    {
        return $this->data;
    }

    public function getContentWithoutHeaders(){
        return array_slice($this->getContent(), 1);
    }

    public function getHeaders()
    {
        return $this->data[0];
    }

    /**
    * Возвращает объект данных битрикса для запроса
    *
    * @return \App\BitrixClasses\Parser\Lib\BitrixData[]
    */
    public function getBitrixData() {

        $data = array();

        foreach ($this->getContentWithoutHeaders() as $row) {

            $bitrixData = new BitrixData();

            foreach ($row as $key => $value) {

                $res = $bitrixData->add($this->getHeaders()[$key], $value);

                /**
                * Если ошибка при формировании объекта bitrix
                */
               // if ($res !== true) return $res;
            }
            /**
             * Только объекты у которых есть хотя бы одно не пустое свойство
             */

            if (get_object_vars($bitrix = $bitrixData->deleteEmpty())){
                $data[] = $bitrix;
            }
        }
        return $data;
    }

    /**
     * Возвращает объект данных битрикса для запроса
     *
     * @return \App\BitrixClasses\Parser\Lib\ReportData[]
     */
    public function getReportData() {

        $data = array();
        foreach ($this->getContentWithoutHeaders() as $row) {

            $reportData = new ReportData();

            foreach ($row as $key => $value) {

                $res = $reportData->add($this->getHeaders()[$key], $value);

                /**
                 * Если ошибка при формировании объекта bitrix
                 */
                // if ($res !== true) return $res;
            }

            /**
             * Только объекты у которых есть хотя бы одно не пустое свойство
             */

            if (get_object_vars($report = $reportData->deleteEmpty())){
                $data[] = $report;
            }
        }
        return $data;
    }

    /**
     * Возвращает объект данных битрикса для запроса
     *
     * @return \App\BitrixClasses\Parser\Lib\MailData[]
     */
    public function getMailData() {

        $data = array();
        foreach ($this->getContentWithoutHeaders() as $row) {

            $mailData = new MailData();

            foreach ($row as $key => $value) {

                $res = $mailData->add($this->getHeaders()[$key], $value);

                /**
                 * Если ошибка при формировании объекта bitrix
                 */
                // if ($res !== true) return $res;
            }
            /**
             * Только объекты у которых есть хотя бы одно не пустое свойство
             */

            if (get_object_vars($mail = $mailData->deleteEmpty())){
                $data[] = $mail;
            }
        }
        return $data;
    }

    /**
    * Функция чтения csv файла в массив
    *
    * @return void
    */
    private function FormatToArray()
    {

        $csv = file_get_contents($this->pathFile);

        $rows = explode("\r\n", $csv);
        foreach ($rows as $row) {

            $row = mb_convert_encoding($row, 'utf-8', 'cp-1251');

            $data[] = str_getcsv($row, $this->getDelimiter());
        }

        $this->data = $data;
    }
    /**
     * Функция замены соответствий полям из файла parser_accordance
     * @param array $accordance
     *
     * @return void;
     */
    private function swap($accordance) {
        foreach ($accordance as $key => $value) {
            foreach ($this->data[0] as $keyHeader =>$header){
                if ($value == trim($header)){
                    $this->data[0][$keyHeader] = $key;
                }
            }
        }
    }
}
