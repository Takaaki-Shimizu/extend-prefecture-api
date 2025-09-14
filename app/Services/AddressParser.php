<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class AddressParser
{
    /**
     * 都道府県名の直接抽出（正規表現）
     */
    public function extractPrefecture(string $clientAddress): ?string
    {
        $pattern = Cache::rememberForever('prefecture_pattern', function () {
            $prefectureList = config('prefectures.list');
            return implode('|', array_map('preg_quote',
                $prefectureList,
                array_fill(0, count($prefectureList), '/')
            ));
        });

        $regex = '/(' . $pattern . ')/u';

        if (preg_match($regex, $clientAddress, $matches)) {
            return $matches[0];
        }

        return null;
    }
}