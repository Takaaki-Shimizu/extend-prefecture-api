<?php

namespace App\Services;

class AddressParser
{
    /**
     * @var string|null Cached prefecture pattern
     */
    private static $prefecturePattern = null;

    /**
     * 都道府県名の直接抽出（正規表現）
     */
    public function extractPrefecture(string $clientAddress): ?string
    {
        if (self::$prefecturePattern === null) {
            $prefectureList = config('prefectures.list');
            self::$prefecturePattern = implode('|', array_map('preg_quote',
                $prefectureList,
                array_fill(0, count($prefectureList), '/')
            ));
        }

        $pattern = '/(' . self::$prefecturePattern . ')/u';

        if (preg_match($pattern, $clientAddress, $matches)) {
            return $matches[0];
        }

        return null;
    }
}