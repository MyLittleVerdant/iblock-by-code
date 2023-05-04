<?php

namespace InternalModules;

use Exception;

class IBlock
{
    const CACHE_FOLDER = '/local/cache/';
    const IBLOCK_ID_FILENAME = 'ibList.json';

    /**
     * Запросить из БД список активных инфоблоков
     * timeToRun - время выполнения запроса к бд
     * timestamp - время создания файла кеша
     * list - список инфоблоков "симв. код" => "ID"
     * [
     *   'timeToRun' => $elapsed,
     *   'timestamp' => time(),
     *   'list' => [ "CODE_1" => "ID_1", "CODE_2" => "ID_2", "CODE_3" => "ID_3" ]
     * ]
     * @return array
     */
    public static function getIblockList(): array
    {
        $start = microtime(true);
        \CModule::IncludeModule('iblock');
        $obIb = \CIBlock::GetList(
            [],
            [
                'ACTIVE' => 'Y'
            ],
            ''
        );

        $ibList = [];
        while ($arIblock = $obIb->Fetch()) {
            $ibList[$arIblock["CODE"]] = $arIblock["ID"];
        }
        $elapsed = microtime(true) - $start;

        return [
            'timeToRun' => $elapsed,
            'timestamp' => time(),
            'list' => $ibList
        ];
    }

    /**
     * Получить массив инфоблоков из файла
     * @return array
     */
    protected static function getIblockListFile(): array
    {
        $content = implode(' ', file($_SERVER['DOCUMENT_ROOT'] . self::CACHE_FOLDER . self::IBLOCK_ID_FILENAME));
        if ($content === false) {
            return [];
        }

        $content = json_decode($content, true);

        if (!$content) {
            throw new Exception('Код ИБ не был найден');
        }

        return $content['list'];
    }

    /**
     * Очистить кеш
     * @return void
     */
    public static function clearIblockFile(): void
    {
        unlink($_SERVER['DOCUMENT_ROOT'] . self::CACHE_FOLDER . self::IBLOCK_ID_FILENAME);
    }

    /**
     * Записать список инфоблоков в файл
     * @return array
     */
    protected static function setIblockListFile(): array
    {
        $fp = fopen($_SERVER['DOCUMENT_ROOT'] . self::CACHE_FOLDER . self::IBLOCK_ID_FILENAME, 'w');

        $iblockList = self::getIblockList();

        try {
            fwrite($fp, json_encode($iblockList));
            fclose($fp);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
        return $iblockList;
    }

    /**
     * Получить список инфоблоков CODE => ID
     * Если есть файл, то берется из кеша
     * При очистке кеша ?clear_cache=Y так же чистится кеш инфоблоков
     *
     * @return array
     */
    public static function getIblocks(): array
    {
        global $USER;
        if ($USER->IsAdmin() && $_GET['clear_cache'] === 'Y') {
            self::clearIblockFile();
        }

        $iblockList = self::getIblockListFile();
        if (!empty($iblockList)) {
            return $iblockList;
        }

        return self::setIblockListFile();
    }

    /**
     * Проверяет существование директории и файла с кешем списка ИБ
     */
    public static function checkDir()
    {
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . self::CACHE_FOLDER)) {
            if (!mkdir($_SERVER['DOCUMENT_ROOT'] . self::CACHE_FOLDER, 0777, true)) {
                throw new Exception('Не удалось создать директорию для кеша');
            }
        }

        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . self::CACHE_FOLDER . self::IBLOCK_ID_FILENAME)) {
            try {
                $fp = fopen(self::IBLOCK_ID_FILENAME, 'x');
                fclose($fp);
            } catch (Exception $exception) {
                throw new Exception($exception->getMessage());
            }
        }
    }

    /**
     * Получить ID инфоблока в зависимости от среды
     * @param $iblockCode - код инфоблока
     * @return false|int
     */
    public static function getIblock($iblockCode = null)
    {
        if (empty($iblockCode)) {
            throw new Exception('Символьный код ИБ не определён');
        }
        self::checkDir();
        $ibList = self::getIblocks();
        return $ibList[$iblockCode] ?? false;
    }
}