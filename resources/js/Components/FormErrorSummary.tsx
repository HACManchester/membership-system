import { Alert, AlertTitle, List, ListItem, ListItemText } from '@mui/material';

type Props = {
  errors: Record<string, string>;
};

/**
 * A summary of all validation errors, shown at the top of a form so problems on
 * fields that are currently hidden (e.g. conditional sections) are still visible.
 * Individual fields keep showing their own error underneath as well.
 */
const FormErrorSummary = ({ errors }: Props) => {
  const entries = Object.entries(errors).filter(([, message]) => Boolean(message));

  if (entries.length === 0) {
    return null;
  }

  return (
    <Alert severity="error" sx={{ mb: 3 }}>
      <AlertTitle>
        Please fix the following {entries.length === 1 ? 'problem' : `${entries.length} problems`}
      </AlertTitle>
      <List dense disablePadding>
        {entries.map(([field, message]) => (
          <ListItem key={field} disableGutters sx={{ py: 0, display: 'list-item', ml: 2 }}>
            <ListItemText primary={message} />
          </ListItem>
        ))}
      </List>
    </Alert>
  );
};

export default FormErrorSummary;
