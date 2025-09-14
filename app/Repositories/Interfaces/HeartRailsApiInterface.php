<?php

namespace App\Repositories\Interfaces;

use App\Models\Location;

interface HeartRailsApiInterface
{
    /**
     * 住所（市区町村+地名）から位置情報を取得する
     *
     * @param string $extractedCity 市区町村+地名の文字列
     * @return Location|null 位置情報（都道府県含む）
     * @throws \App\Exceptions\ExternalResourceNotFoundException APIエラー時
     */
    public function findByAddress(string $extractedCity): ?Location;
}