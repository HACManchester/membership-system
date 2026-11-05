import { describe, expect, it, jest } from '@jest/globals';
import { fireEvent, render, screen } from '@testing-library/react';
import NotificationEmailForm, { NotificationEmailFormData } from '../NotificationEmailForm';

const data: NotificationEmailFormData = {
  recipient: 'all',
  subject: 'Open evening',
  message: 'Come along',
  send_to_all: false,
};

const renderForm = (overrides: Partial<NotificationEmailFormData> = {}, errors = {}) => {
  const setData = jest.fn();
  render(
    <NotificationEmailForm
      data={{ ...data, ...overrides }}
      setData={setData}
      errors={errors}
      processing={false}
      onSubmit={() => {}}
    >
      <p>All Members</p>
    </NotificationEmailForm>
  );

  return setData;
};

describe('NotificationEmailForm', () => {
  it('sends a test to the sender until "send to everyone" is ticked', () => {
    renderForm();

    expect(screen.getByRole('button', { name: 'Send a test to yourself' })).toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'Send to everyone' })).not.toBeInTheDocument();
  });

  it('says the email goes to everyone once ticked', () => {
    renderForm({ send_to_all: true });

    expect(screen.getByRole('button', { name: 'Send to everyone' })).toBeInTheDocument();
  });

  it('ticks "send to everyone"', () => {
    const setData = renderForm();

    fireEvent.click(screen.getByRole('checkbox', { name: /send the message to everyone/i }));

    expect(setData).toHaveBeenCalledWith('send_to_all', true);
  });

  it('shows the field errors', () => {
    renderForm({}, { subject: 'The subject field is required.' });

    expect(screen.getByText('The subject field is required.')).toBeInTheDocument();
  });
});
