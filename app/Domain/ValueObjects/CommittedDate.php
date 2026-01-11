<?php

namespace App\Domain\ValueObjects;

readonly class CommittedDate
{
    use ComparesValue;

    public function __construct(
        public \DateTime $value
    ) {}

    /**
     * DateTime オブジェクトの比較をオーバーライド
     * タイムスタンプで比較することで、タイムゾーンの違いを考慮する
     *
     * @param  self  $other  比較対象の値オブジェクト
     * @return bool 等価な場合 true、そうでない場合 false
     */
    public function equals(self $other): bool
    {
        return $this->value->getTimestamp() === $other->value->getTimestamp();
    }
}
