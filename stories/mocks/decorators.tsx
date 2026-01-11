import React from 'react';
import { setProcessing } from './inertia';

/**
 * Inertia の processing 状態を設定するデコレータ
 */
export const withProcessing = (processing: boolean) => {
  return (Story: React.ComponentType) => {
    React.useEffect(() => {
      setProcessing(processing);
      return () => {
        setProcessing(false);
      };
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [processing]);

    return <Story />;
  };
};

/**
 * デフォルトの processing 状態（false）を設定するデコレータ
 */
export const withDefaultProcessing = withProcessing(false);
