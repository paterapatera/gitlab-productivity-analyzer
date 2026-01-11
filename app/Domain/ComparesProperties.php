<?php

namespace App\Domain;

use ReflectionClass;
use ReflectionProperty;

/**
 * エンティティのプロパティ比較を提供するトレイト
 *
 * このトレイトは、エンティティの `equals()` メソッドの実装を簡素化します。
 * リフレクションを使用してすべてのpublicプロパティを自動的に比較します。
 *
 * 値オブジェクトのプロパティは、その `equals()` メソッドを使用して比較されます。
 * プリミティブ型のプロパティは、厳密等価演算子（===）を使用して比較されます。
 */
trait ComparesProperties
{
    /**
     * 他のエンティティと等価かどうかを判定する
     *
     * @param  self  $other  比較対象のエンティティ
     * @return bool 等価な場合 true、そうでない場合 false
     */
    public function equals(self $other): bool
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $thisValue = $property->getValue($this);
            $otherValue = $property->getValue($other);

            // 値オブジェクトの場合は equals() メソッドを使用
            if (is_object($thisValue) && method_exists($thisValue, 'equals')) {
                // 型チェック: 同じ型でない場合は等価でない
                if (! ($otherValue instanceof $thisValue)) {
                    return false;
                }
                if (! $thisValue->equals($otherValue)) {
                    return false;
                }
            } elseif ($thisValue !== $otherValue) {
                return false;
            }
        }

        return true;
    }
}
