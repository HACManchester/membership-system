import React from 'react';
import { TextField, Button, Grid2 } from '@mui/material';
import MemberMultiSelect from './MemberMultiSelect';
import { Member } from '../types/resources';

export type EquipmentAreaFormData = {
  name: string;
  slug: string;
  description: string;
  area_coordinators: Member[];
};

type Props = {
  data: EquipmentAreaFormData;
  setData: <K extends keyof EquipmentAreaFormData>(key: K, value: EquipmentAreaFormData[K]) => void;
  onSubmit: (e: React.FormEvent) => void;
  processing: boolean;
  errors: Record<string, string>;
  searchUrl: string;
  submitLabel?: string;
};

const generateSlug = (text: string) =>
  text
    .toLowerCase()
    .replace(/[^\w\s-]/gi, '')
    .replace(/\s+/g, '-');

const EquipmentAreaForm = ({
  data,
  setData,
  onSubmit,
  processing,
  errors,
  searchUrl,
  submitLabel = 'Save',
}: Props) => {
  const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const name = e.target.value;
    setData('name', name);
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
            helperText={errors.name || 'e.g. "Visual Arts", "3D Printing"'}
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
            helperText={errors.description || 'Optional. What this area covers.'}
          />
        </Grid2>

        <Grid2 size={12}>
          <MemberMultiSelect
            value={data.area_coordinators}
            onChange={(members) => setData('area_coordinators', members)}
            searchUrl={searchUrl}
            label="Area coordinators"
            error={!!errors.area_coordinators}
            helperText={errors.area_coordinators || 'Members who coordinate this area.'}
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

export default EquipmentAreaForm;
