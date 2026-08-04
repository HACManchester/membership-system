import React from 'react';
import { TextField, Button, Grid2 } from '@mui/material';

export type RoomFormData = {
  name: string;
  slug: string;
  description: string;
};

type Props = {
  data: RoomFormData;
  setData: (key: keyof RoomFormData, value: RoomFormData[keyof RoomFormData]) => void;
  onSubmit: (e: React.FormEvent) => void;
  processing: boolean;
  errors: Record<string, string>;
  submitLabel?: string;
};

const generateSlug = (text: string) =>
  text
    .toLowerCase()
    .replace(/[^\w\s-]/gi, '')
    .replace(/\s+/g, '-');

const RoomForm = ({ data, setData, onSubmit, processing, errors, submitLabel = 'Save' }: Props) => {
  const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const name = e.target.value;
    setData('name', name);
    // Keep the slug in step with the name until it's been edited by hand.
    if (!data.slug || data.slug === generateSlug(data.name)) {
      setData('slug', generateSlug(name));
    }
  };

  return (
    <form onSubmit={onSubmit}>
      <Grid2 container spacing={3}>
        <Grid2 size={12}>
          <TextField
            label="Name"
            value={data.name}
            onChange={handleNameChange}
            fullWidth
            required
            error={!!errors.name}
            helperText={errors.name || 'e.g. "Woodwork", "The Stage"'}
          />
        </Grid2>

        <Grid2 size={12}>
          <TextField
            label="Slug"
            value={data.slug}
            onChange={(e) => setData('slug', e.target.value)}
            fullWidth
            required
            error={!!errors.slug}
            helperText={errors.slug || 'URL-friendly identifier, auto-generated from the name'}
          />
        </Grid2>

        <Grid2 size={12}>
          <TextField
            label="Description"
            value={data.description}
            onChange={(e) => setData('description', e.target.value)}
            fullWidth
            multiline
            rows={2}
            error={!!errors.description}
            helperText={errors.description || 'Optional. A short note about this room.'}
          />
        </Grid2>

        <Grid2 size={12} sx={{ mt: 2 }}>
          <Button
            type="submit"
            variant="contained"
            color="primary"
            disabled={processing}
            size="large"
          >
            {submitLabel}
          </Button>
        </Grid2>
      </Grid2>
    </form>
  );
};

export default RoomForm;
