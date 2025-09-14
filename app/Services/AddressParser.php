<?php

namespace App\Services;

class AddressParser
{
    /**
     * 都道府県名の直接抽出（正規表現）
     */
    public function extractPrefecture(string $clientAddress): ?string
    {
        $prefectureList = config('prefectures.list');

        $prefecturePattern = implode('|', array_map('preg_quote',
            $prefectureList,
            array_fill(0, count($prefectureList), '/')
        ));

        $pattern = '/(' . $prefecturePattern . ')/u';

        if (preg_match($pattern, $clientAddress, $matches)) {
            return $matches[0];
        }

        return null;
    }
}