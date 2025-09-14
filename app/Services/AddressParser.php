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

    /**
     * 市区町村+地名の抽出
     */
    public function extractCity(string $clientAddress): ?string
    {
        if (empty($clientAddress)) {
            return null;
        }

        // 1. 郵便番号を除去（〒123-4567 パターン）
        $pattern = '/〒?\s*[0-9０-９]{3}-?[0-9０-９]{4}\s*/u';
        $addressWithoutPostal = preg_replace($pattern, '', $clientAddress);

        // 2. 旧字表記を除去（大字、字、小字）
        $pattern = '/(大字|字|小字)\s*/u';
        $addressWithoutOldChar = preg_replace($pattern, '', $addressWithoutPostal);

        // 3. 丁目以降の住所要素を削除
        $pattern = '/[0-9０-９]+(丁目|番|号|[-－][0-9０-９]+)*\s?.*$/u';
        $deletedAfterBlockAddress = preg_replace($pattern, '', $addressWithoutOldChar);

        // 4. 市区町村郡島+地名の部分を抽出
        $pattern = '/[ぁ-んァ-ン一-龠々]+[市区町村郡島]+[ぁ-んァ-ン一-龠々]+/u';
        if (preg_match($pattern, $deletedAfterBlockAddress, $matches)) {
            return $matches[0];
        }

        return null;
    }
}