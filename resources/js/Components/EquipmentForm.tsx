import React, { useState } from 'react';
import {
  TextField,
  Button,
  FormControl,
  FormControlLabel,
  FormHelperText,
  Grid2,
  InputLabel,
  Select,
  MenuItem,
  Switch,
  Typography,
  Divider,
  Box,
  Autocomplete,
  Stack,
  Alert,
  CircularProgress,
} from '@mui/material';
import MarkdownTextField from './MarkdownTextField';
import { Member } from '../types/resources';
import { useMemberSearch } from '../hooks/useMemberSearch';

export type EquipmentFormData = {
  name: string;
  slug: string;
  room_id: number | '';
  detail: string;
  maintainer_group_id: number | '';
  description: string;
  working: boolean;
  permaloan: boolean;
  permaloan_user_id: number | '';
  dangerous: boolean;
  lone_working: boolean;
  ppe: string[];
  course_id: number | '';
  requires_induction: boolean;
  accepting_inductions: boolean;
  induction_category: string;
  induction_instructions: string;
  trained_instructions: string;
  trainer_instructions: string;
  manufacturer: string;
  model_number: string;
  help_text: string;
  docs: string;
  access_fee: number;
  usage_cost: number;
  usage_cost_per: string;
  access_code: string;
  admin_notes: string;
};

type Props = {
  data: EquipmentFormData;
  setData: (
    key: keyof EquipmentFormData,
    value: EquipmentFormData[keyof EquipmentFormData]
  ) => void;
  errors: Record<string, string>;
  processing: boolean;
  onSubmit: (e: React.FormEvent) => void;
  submitLabel?: string;
  rooms: Record<string, string>;
  maintainerGroupOptions: Record<string, string>;
  ppeOptions: Record<string, string>;
  memberSearchUrl: string;
  initialPermaloanHolder: Member | null;
  usageCostPerOptions: Record<string, string>;
  courseOptions: { id: number; name: string; live: boolean }[];
  canManageGlobally: boolean;
};

const generateSlug = (text: string) =>
  text
    .toLowerCase()
    .replace(/[^\w\s-]/gi, '')
    .replace(/\s+/g, '-');

const SectionHeading = ({ children }: { children: React.ReactNode }) => (
  <Grid2 size={12} sx={{ mt: 2 }}>
    <Divider />
    <Box sx={{ mt: 2 }}>
      <Typography variant="h6" component="h3" gutterBottom>
        {children}
      </Typography>
    </Box>
  </Grid2>
);

const EquipmentForm = ({
  data,
  setData,
  errors,
  processing,
  onSubmit,
  submitLabel = 'Save',
  rooms,
  maintainerGroupOptions,
  ppeOptions,
  memberSearchUrl,
  initialPermaloanHolder,
  usageCostPerOptions,
  courseOptions,
  canManageGlobally,
}: Props) => {
  const [selectedMember, setSelectedMember] = useState<Member | null>(initialPermaloanHolder);
  const {
    members: memberOptions,
    searching: searchingMembers,
    search: searchMembers,
  } = useMemberSearch(memberSearchUrl);

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
        {/* Core */}
        <Grid2 size={12}>
          <TextField
            label="Name"
            value={data.name}
            onChange={handleNameChange}
            fullWidth
            required
            error={!!errors.name}
            helperText={errors.name}
          />
        </Grid2>

        <Grid2 size={{ xs: 12, md: 6 }}>
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

        <Grid2 size={{ xs: 12, md: 6 }}>
          <FormControl fullWidth required error={!!errors.room_id}>
            <InputLabel id="room-label">Room</InputLabel>
            <Select
              labelId="room-label"
              label="Room"
              value={data.room_id}
              onChange={(e) =>
                setData('room_id', e.target.value === '' ? '' : Number(e.target.value))
              }
            >
              {Object.entries(rooms).map(([id, name]) => (
                <MenuItem key={id} value={Number(id)}>
                  {name}
                </MenuItem>
              ))}
            </Select>
            <FormHelperText>{errors.room_id || 'Which room the equipment lives in'}</FormHelperText>
          </FormControl>
        </Grid2>

        <Grid2 size={{ xs: 12, md: 6 }}>
          <TextField
            label="Location detail"
            value={data.detail}
            onChange={(e) => setData('detail', e.target.value)}
            fullWidth
            error={!!errors.detail}
            helperText={errors.detail || 'Where in the room is it kept?'}
          />
        </Grid2>

        <Grid2 size={12}>
          <FormControl fullWidth required={!canManageGlobally} error={!!errors.maintainer_group_id}>
            <InputLabel id="maintainer-group-label">Maintainer group</InputLabel>
            <Select
              labelId="maintainer-group-label"
              label="Maintainer group"
              value={data.maintainer_group_id}
              onChange={(e) =>
                setData('maintainer_group_id', e.target.value === '' ? '' : Number(e.target.value))
              }
            >
              <MenuItem value="">
                <em>None</em>
              </MenuItem>
              {Object.entries(maintainerGroupOptions).map(([id, name]) => (
                <MenuItem key={id} value={Number(id)}>
                  {name}
                </MenuItem>
              ))}
            </Select>
            <FormHelperText>
              {errors.maintainer_group_id || 'The group responsible for maintaining this equipment'}
            </FormHelperText>
          </FormControl>
        </Grid2>

        <Grid2 size={12}>
          <MarkdownTextField
            label="Description"
            value={data.description}
            onChange={(e) => setData('description', e.target.value)}
            error={!!errors.description}
            helperText={errors.description || 'Supports markdown formatting.'}
            rows={3}
          />
        </Grid2>

        {/* Status & safety */}
        <SectionHeading>Status &amp; safety</SectionHeading>

        <Grid2 size={{ xs: 12, md: 6 }}>
          <FormControlLabel
            control={
              <Switch
                checked={data.working}
                onChange={(e) => setData('working', e.target.checked)}
              />
            }
            label="Working / in service"
          />
        </Grid2>

        <Grid2 size={{ xs: 12, md: 6 }}>
          <FormControlLabel
            control={
              <Switch
                checked={data.lone_working}
                onChange={(e) => setData('lone_working', e.target.checked)}
              />
            }
            label="Lone working allowed"
          />
        </Grid2>

        <Grid2 size={{ xs: 12, md: 6 }}>
          <FormControlLabel
            control={
              <Switch
                checked={data.dangerous}
                onChange={(e) => setData('dangerous', e.target.checked)}
              />
            }
            label="Especially dangerous"
          />
        </Grid2>

        <Grid2 size={{ xs: 12, md: 6 }}>
          <FormControlLabel
            control={
              <Switch
                checked={data.permaloan}
                onChange={(e) => {
                  const enabled = e.target.checked;
                  setData('permaloan', enabled);
                  // Clear the holder when permaloan is switched off so a stale
                  // selection isn't kept (and later submitted).
                  if (!enabled) {
                    setSelectedMember(null);
                    setData('permaloan_user_id', '');
                  }
                }}
              />
            }
            label="On permaloan"
          />
        </Grid2>

        {data.permaloan && (
          <Grid2 size={12}>
            <Autocomplete
              options={memberOptions}
              getOptionLabel={(option) => option.name}
              filterOptions={(x) => x}
              isOptionEqualToValue={(option, value) => option.id === value.id}
              loading={searchingMembers}
              value={selectedMember}
              onChange={(_, option) => {
                setSelectedMember(option);
                setData('permaloan_user_id', option ? option.id : '');
              }}
              onInputChange={(_, value) => searchMembers(value)}
              noOptionsText="Type to search members"
              renderInput={(params) => (
                <TextField
                  {...params}
                  label="Permaloan holder"
                  error={!!errors.permaloan_user_id}
                  helperText={errors.permaloan_user_id}
                  InputProps={{
                    ...params.InputProps,
                    endAdornment: (
                      <>
                        {searchingMembers ? <CircularProgress size={18} /> : null}
                        {params.InputProps.endAdornment}
                      </>
                    ),
                  }}
                />
              )}
            />
          </Grid2>
        )}

        <Grid2 size={12}>
          <FormControl fullWidth error={!!errors.ppe}>
            <InputLabel id="ppe-label">PPE required</InputLabel>
            <Select
              labelId="ppe-label"
              multiple
              label="PPE required"
              value={data.ppe}
              onChange={(e) => setData('ppe', e.target.value as unknown as string[])}
            >
              {Object.entries(ppeOptions).map(([value, label]) => (
                <MenuItem key={value} value={value}>
                  {label}
                </MenuItem>
              ))}
            </Select>
            <FormHelperText>{errors.ppe || 'Personal protective equipment needed'}</FormHelperText>
          </FormControl>
        </Grid2>

        {/* Training & inductions */}
        <SectionHeading>Training &amp; inductions</SectionHeading>

        <Grid2 size={12}>
          <FormControl fullWidth error={!!errors.course_id}>
            <InputLabel id="course-label">Induction course</InputLabel>
            <Select
              labelId="course-label"
              label="Induction course"
              value={data.course_id}
              onChange={(e) =>
                setData('course_id', e.target.value === '' ? '' : Number(e.target.value))
              }
            >
              <MenuItem value="">
                <em>None</em>
              </MenuItem>
              {courseOptions.map((course) => (
                <MenuItem key={course.id} value={course.id}>
                  {course.name}
                  {course.live ? '' : ' (not yet live)'}
                </MenuItem>
              ))}
            </Select>
            <FormHelperText>
              {errors.course_id ||
                'Associate an induction course. This marks the equipment as requiring induction; training is managed on the course page.'}
            </FormHelperText>
          </FormControl>
        </Grid2>

        <Grid2 size={12}>
          <Alert severity="info">
            <strong>Legacy induction</strong> — prefer attaching an induction course above. These
            fields only apply to equipment not yet migrated to a course, and are superseded once a
            live course manages its training.
          </Alert>
        </Grid2>

        <Grid2 size={{ xs: 12, md: 6 }}>
          <FormControlLabel
            control={
              <Switch
                checked={data.requires_induction}
                onChange={(e) => setData('requires_induction', e.target.checked)}
              />
            }
            label="Requires an induction (legacy)"
          />
        </Grid2>

        <Grid2 size={{ xs: 12, md: 6 }}>
          <FormControlLabel
            control={
              <Switch
                checked={data.accepting_inductions}
                onChange={(e) => setData('accepting_inductions', e.target.checked)}
              />
            }
            label="Currently accepting inductions (legacy)"
          />
        </Grid2>

        <Grid2 size={12}>
          <TextField
            label="Induction category (legacy)"
            value={data.induction_category}
            onChange={(e) => setData('induction_category', e.target.value)}
            fullWidth
            error={!!errors.induction_category}
            helperText={
              errors.induction_category || 'Equipment sharing a category shares training records'
            }
          />
        </Grid2>

        <Grid2 size={12}>
          <MarkdownTextField
            label="Induction instructions (legacy)"
            value={data.induction_instructions}
            onChange={(e) => setData('induction_instructions', e.target.value)}
            error={!!errors.induction_instructions}
            helperText={errors.induction_instructions || 'Shown to members requesting induction.'}
            rows={2}
          />
        </Grid2>

        <Grid2 size={12}>
          <MarkdownTextField
            label="Trained instructions (legacy)"
            value={data.trained_instructions}
            onChange={(e) => setData('trained_instructions', e.target.value)}
            error={!!errors.trained_instructions}
            helperText={errors.trained_instructions || 'Shown to members once trained.'}
            rows={2}
          />
        </Grid2>

        <Grid2 size={12}>
          <MarkdownTextField
            label="Trainer instructions (legacy)"
            value={data.trainer_instructions}
            onChange={(e) => setData('trainer_instructions', e.target.value)}
            error={!!errors.trainer_instructions}
            helperText={errors.trainer_instructions || 'Shown to trainers.'}
            rows={2}
          />
        </Grid2>

        {/* Additional details */}
        <SectionHeading>Additional details (optional)</SectionHeading>

        <Grid2 size={{ xs: 12, md: 6 }}>
          <TextField
            label="Manufacturer"
            value={data.manufacturer}
            onChange={(e) => setData('manufacturer', e.target.value)}
            fullWidth
            error={!!errors.manufacturer}
            helperText={errors.manufacturer}
          />
        </Grid2>

        <Grid2 size={{ xs: 12, md: 6 }}>
          <TextField
            label="Model number"
            value={data.model_number}
            onChange={(e) => setData('model_number', e.target.value)}
            fullWidth
            error={!!errors.model_number}
            helperText={errors.model_number}
          />
        </Grid2>

        <Grid2 size={12}>
          <MarkdownTextField
            label="Help text"
            value={data.help_text}
            onChange={(e) => setData('help_text', e.target.value)}
            error={!!errors.help_text}
            helperText={errors.help_text || 'Extra usage guidance. Supports markdown.'}
            rows={2}
          />
        </Grid2>

        <Grid2 size={12}>
          <TextField
            label="Documentation URL"
            value={data.docs}
            onChange={(e) => setData('docs', e.target.value)}
            fullWidth
            type="url"
            error={!!errors.docs}
            helperText={errors.docs || 'Link to a manual or documentation'}
          />
        </Grid2>

        <Grid2 size={{ xs: 12, md: 4 }}>
          <TextField
            label="Access fee (£)"
            type="number"
            value={data.access_fee}
            onChange={(e) => setData('access_fee', Number(e.target.value))}
            fullWidth
            error={!!errors.access_fee}
            helperText={errors.access_fee}
          />
        </Grid2>

        <Grid2 size={{ xs: 12, md: 4 }}>
          <TextField
            label="Usage cost (£)"
            type="number"
            value={data.usage_cost}
            onChange={(e) => setData('usage_cost', Number(e.target.value))}
            fullWidth
            error={!!errors.usage_cost}
            helperText={errors.usage_cost}
          />
        </Grid2>

        <Grid2 size={{ xs: 12, md: 4 }}>
          <FormControl fullWidth error={!!errors.usage_cost_per}>
            <InputLabel id="usage-cost-per-label">Usage cost per</InputLabel>
            <Select
              labelId="usage-cost-per-label"
              label="Usage cost per"
              value={data.usage_cost_per}
              onChange={(e) => setData('usage_cost_per', e.target.value)}
            >
              {Object.entries(usageCostPerOptions).map(([value, label]) => (
                <MenuItem key={value} value={value}>
                  {label}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Grid2>

        <Grid2 size={12}>
          <TextField
            label="Access code"
            value={data.access_code}
            onChange={(e) => setData('access_code', e.target.value)}
            fullWidth
            error={!!errors.access_code}
            helperText={
              errors.access_code || 'Padlock/keypad code, revealed only to trained members'
            }
          />
        </Grid2>

        <Grid2 size={12}>
          <TextField
            label="Admin notes"
            value={data.admin_notes}
            onChange={(e) => setData('admin_notes', e.target.value)}
            fullWidth
            multiline
            rows={3}
            error={!!errors.admin_notes}
            helperText={errors.admin_notes || 'Internal notes — not shown to members.'}
          />
        </Grid2>

        <Grid2 size={12} sx={{ mt: 2 }}>
          <Stack direction="row" spacing={2}>
            <Button
              type="submit"
              variant="contained"
              color="primary"
              disabled={processing}
              size="large"
            >
              {submitLabel}
            </Button>
          </Stack>
        </Grid2>
      </Grid2>
    </form>
  );
};

export default EquipmentForm;
