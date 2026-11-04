import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { Snackbar, Alert } from '@mui/material';
import type { FlashNotification } from '../types/inertia';

/**
 * Shows the message a controller flashed (`->with('success'|'error', ...)` or the
 * legacy FlashNotification facade), which HandleInertiaRequests sends as Inertia's
 * flash data. The Inertia counterpart of the Blade layout's flash-message partial.
 */
const FlashSnackbar = () => {
  const { flash } = usePage();

  // Inertia sends flash data only on the response that carries the message: it
  // isn't kept in history (Back) or sent with partial reloads. Each response
  // brings a new flash object, so a message flashed twice in a row shows twice;
  // an empty one leaves the current message alone.
  const [seenFlash, setSeenFlash] = useState(flash);
  const [notification, setNotification] = useState<FlashNotification | undefined>(
    flash.notification
  );
  const [open, setOpen] = useState(flash.notification !== undefined);

  if (flash !== seenFlash) {
    setSeenFlash(flash);
    if (flash.notification) {
      setNotification(flash.notification);
      setOpen(true);
    }
  }

  if (!notification) {
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
      <Alert severity={notification.severity} variant="filled" onClose={() => setOpen(false)}>
        {notification.message}
      </Alert>
    </Snackbar>
  );
};

export default FlashSnackbar;
