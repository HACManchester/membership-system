import React, { useEffect, useState } from 'react';
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
  const [open, setOpen] = useState(false);

  const message = flash?.error || flash?.success || null;
  const severity = flash?.error ? 'error' : 'success';

  // flash is a fresh object on every Inertia response, so this re-opens the
  // snackbar even when the same message is flashed twice in a row
  useEffect(() => {
    if (message) {
      setOpen(true);
    }
  }, [flash, message]);

  if (!message) {
    return null;
  }

  return (
    <Snackbar
      open={open}
      autoHideDuration={6000}
      onClose={(_, reason) => {
        if (reason !== 'clickaway') {
          setOpen(false);
        }
      }}
      anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
    >
      <Alert severity={severity} variant="filled" onClose={() => setOpen(false)}>
        {message}
      </Alert>
    </Snackbar>
  );
};

export default FlashSnackbar;
