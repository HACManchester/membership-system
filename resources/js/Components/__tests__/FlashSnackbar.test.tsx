import { beforeEach, describe, expect, it, jest } from '@jest/globals';
import { act, render, screen, waitFor } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import FlashSnackbar from '../FlashSnackbar';
import type { FlashNotification } from '../../types/inertia';

const mockedUsePage = jest.mocked(usePage);

/** An Inertia response: each brings a new flash object, empty unless a message was flashed. */
function respond(notification?: FlashNotification) {
  mockedUsePage.mockReturnValue({
    props: {},
    flash: notification ? { notification } : {},
  } as unknown as ReturnType<typeof usePage>);
}

describe('FlashSnackbar', () => {
  beforeEach(() => {
    mockedUsePage.mockReset();
  });

  it('shows nothing without a flashed message', () => {
    respond();
    render(<FlashSnackbar />);

    expect(screen.queryByRole('alert')).not.toBeInTheDocument();
  });

  it.each(['success', 'info', 'warning', 'error'] as const)(
    'shows a %s message with that severity',
    (severity) => {
      respond({ message: 'Room saved', severity });
      render(<FlashSnackbar />);

      const alert = screen.getByRole('alert');
      expect(alert).toHaveTextContent('Room saved');
      expect(alert.className).toContain(
        `MuiAlert-color${severity.charAt(0).toUpperCase()}${severity.slice(1)}`
      );
    }
  );

  it('stays dismissed on later responses without a message (Back, partial reloads)', async () => {
    respond({ message: 'Room saved', severity: 'success' });
    const { rerender } = render(<FlashSnackbar />);

    act(() => screen.getByRole('button', { name: /close/i }).click());
    await waitFor(() => expect(screen.queryByRole('alert')).not.toBeInTheDocument());

    respond();
    rerender(<FlashSnackbar />);
    expect(screen.queryByRole('alert')).not.toBeInTheDocument();
  });

  it('keeps showing a message through a partial reload', () => {
    respond({ message: 'Room saved', severity: 'success' });
    const { rerender } = render(<FlashSnackbar />);

    respond();
    rerender(<FlashSnackbar />);
    expect(screen.getByRole('alert')).toHaveTextContent('Room saved');
  });

  it('shows the same message again when it is flashed again', async () => {
    respond({ message: 'Room saved', severity: 'success' });
    const { rerender } = render(<FlashSnackbar />);

    act(() => screen.getByRole('button', { name: /close/i }).click());
    await waitFor(() => expect(screen.queryByRole('alert')).not.toBeInTheDocument());

    respond({ message: 'Room saved', severity: 'success' });
    rerender(<FlashSnackbar />);
    expect(screen.getByRole('alert')).toHaveTextContent('Room saved');
  });
});
