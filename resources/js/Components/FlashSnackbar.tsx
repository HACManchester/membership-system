import React, { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { Snackbar, Alert } from '@mui/material';

type FlashPageProps = {
  flash?: {
    success: string | null;
    error: string | null;
  };
};

/**
 * Shows the session flash message (`->with('success', ...)` / `->with('error', ...)`)
 * shared by HandleInertiaRequests as a snackbar. The Inertia counterpart of the
 * Blade layout's flash-message partial.
 */
const FlashSnackbar: React.FC = () => {
  const { flash } = usePage<FlashPageProps>().props;
  const [dismissedFlash, setDismissedFlash] = useState<FlashPageProps['flash']>();

  const message = flash?.error || flash?.success || null;
  const severity = flash?.error ? 'error' : 'success';

  // flash is a fresh object on every Inertia response, so the snackbar re-opens
  // even when the same message is flashed twice in a row
  const open = flash !== dismissedFlash;
  const close = () => setDismissedFlash(flash);

  if (!message) {
    return null;
  }

  return (
    <Snackbar
      open={open}
      autoHideDuration={6000}
      onClose={(_, reason) => {
        if (reason !== 'clickaway') {
          close();
        }
      }}
      anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
    >
      <Alert severity={severity} variant="filled" onClose={close}>
        {message}
      </Alert>
    </Snackbar>
  );
};

export default FlashSnackbar;
