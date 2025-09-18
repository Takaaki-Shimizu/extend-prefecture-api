<?php

namespace App\Repositories\Interfaces;

interface AiApiInterface
{
    /**
     * AIを使用して住所から都道府県を抽出する
     *
     * @param string $address 住所文字列
     * @return string|null 都道府県名（見つからない場合はnull）
     * @throws \App\Exceptions\ExternalResourceNotFoundException
     */
    public function extractPrefectureByAi(string $address): ?string;
}