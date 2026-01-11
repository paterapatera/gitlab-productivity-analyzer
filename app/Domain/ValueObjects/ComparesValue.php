<?php

namespace App\Domain\ValueObjects;

/**
 * 値オブジェクトの値比較を提供するトレイト
 *
 * このトレイトは、値オブジェクトの `equals()` メソッドの実装を簡素化します。
 * 値オブジェクトは `value` プロパティを持ち、その値を比較することで等価性を判定します。
 */
trait ComparesValue
{
    /**
     * 他の値オブジェクトと等価かどうかを判定する
     *
     * @param  self  $other  比較対象の値オブジェクト
     * @return bool 等価な場合 true、そうでない場合 false
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
