import React from 'react';
import {
  TextField,
  Button,
  Grid2,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  FormHelperText,
} from '@mui/material';
import MemberMultiSelect from './MemberMultiSelect';
import { Member } from '../types/resources';

export type MaintainerGroupFormData = {
  name: string;
  slug: string;
  description: string;
  equipment_area_id: number | '';
  maintainers: Member[];
};

type AreaOption = { id: number; name: string };

type Props = {
  data: MaintainerGroupFormData;
  setData: <K extends keyof MaintainerGroupFormData>(
    key: K,
    value: MaintainerGroupFormData[K]
  ) => void;
  onSubmit: (e: React.FormEvent) => void;
  processing: boolean;
  errors: Record<string, string>;
  areaOptions: AreaOption[];
  searchUrl: string;
  submitLabel?: string;
};

const generateSlug = (text: string) =>
  text
    .toLowerCase()
    .replace(/[^\w\s-]/gi, '')
    .replace(/\s+/g, '-');

const MaintainerGroupForm = ({
  data,
  setData,
  onSubmit,
  processing,
  errors,
  areaOptions,
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
            helperText={errors.name || 'e.g. "Woodwork", "Sewing Machines"'}
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
          <FormControl fullWidth required error={!!errors.equipment_area_id}>
            <InputLabel id="equipment-area-label">Represented by area</InputLabel>
            <Select
              labelId="equipment-area-label"
              label="Represented by area"
              value={data.equipment_area_id === '' ? '' : String(data.equipment_area_id)}
              onChange={(e) => setData('equipment_area_id', Number(e.target.value))}
            >
              {areaOptions.map((area) => (
                <MenuItem key={area.id} value={String(area.id)}>
                  {area.name}
                </MenuItem>
              ))}
            </Select>
            <FormHelperText>
              {errors.equipment_area_id || 'The equipment area this group looks after.'}
            </FormHelperText>
          </FormControl>
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
            helperText={errors.description || 'Optional. What this group maintains.'}
          />
        </Grid2>

        <Grid2 size={12}>
          <MemberMultiSelect
            value={data.maintainers}
            onChange={(members) => setData('maintainers', members)}
            searchUrl={searchUrl}
            label="Maintainers"
            error={!!errors.maintainers}
            helperText={errors.maintainers || 'Members who maintain this group’s equipment.'}
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

export default MaintainerGroupForm;
