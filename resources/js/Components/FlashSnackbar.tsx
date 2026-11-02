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
  const page = usePage<FlashPageProps>();
  const { flash } = page.props;
  const [dismissedOn, setDismissedOn] = useState<typeof page>();

  const message = flash?.error || flash?.success || null;
  const severity = flash?.error ? 'error' : 'success';

  // Dismissal is tracked per Inertia response, not per flash object: when a visit
  // returns the same component, Inertia keeps the previous object for any prop
  // that hasn't changed. Each response is a fresh page object, so the snackbar
  // re-opens even when the same message is flashed twice in a row.
  const open = page !== dismissedOn;
  const close = () => setDismissedOn(page);

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
