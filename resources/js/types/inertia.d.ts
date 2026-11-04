import '@inertiajs/core';

/** A one-off message for FlashSnackbar, flashed by HandleInertiaRequests. */
export type FlashNotification = {
  message: string;
  severity: 'success' | 'info' | 'warning' | 'error';
};

declare module '@inertiajs/core' {
  interface InertiaConfig {
    flashDataType: {
      notification?: FlashNotification;
    };
  }
}
