import React from 'react';
import {
  Typography,
  Container,
  Card,
  CardContent,
  Grid2,
  Link,
  Button,
  Stack,
  TextField,
  FormControl,
  FormHelperText,
  InputLabel,
  Select,
  MenuItem,
  IconButton,
  Divider,
} from '@mui/material';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import { useForm } from '@inertiajs/react';

type Item = { name: string };

type Props = {
  rooms: Record<string, string>;
  maintainerGroupOptions: Record<string, string>;
  courseOptions: { id: number; name: string; live: boolean }[];
  canManageGlobally: boolean;
  urls: {
    index: string;
    store: string;
  };
};

const BulkCreate = ({
  rooms,
  maintainerGroupOptions,
  courseOptions,
  canManageGlobally,
  urls,
}: Props) => {
  const { data, setData, post, processing, errors } = useForm({
    room_id: '' as number | '',
    maintainer_group_id: '' as number | '',
    course_id: '' as number | '',
    items: [{ name: '' }] as Item[],
  });

  // useForm types errors to top-level keys; per-row keys ("items.0.name") are dynamic.
  const rowErrors = errors as Record<string, string>;

  const updateItem = (index: number, name: string) => {
    const items = data.items.map((item, i) => (i === index ? { ...item, name } : item));
    setData('items', items);
  };

  const addRow = () => setData('items', [...data.items, { name: '' }]);

  const removeRow = (index: number) =>
    setData(
      'items',
      data.items.filter((_, i) => i !== index)
    );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(urls.store);
  };

  return (
    <>
      <PageTitle title="Bulk add equipment" />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Tools &amp; Equipment
          </Link>{' '}
          / Bulk add
        </Typography>

        <Card>
          <CardContent>
            <form onSubmit={handleSubmit}>
              <Typography variant="h6" component="h2" gutterBottom>
                Shared details
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                These apply to every item created below. You can fine-tune each item afterwards.
              </Typography>

              <Grid2 container spacing={3}>
                <Grid2 size={{ xs: 12, md: 4 }}>
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
                    <FormHelperText>{errors.room_id}</FormHelperText>
                  </FormControl>
                </Grid2>

                <Grid2 size={{ xs: 12, md: 4 }}>
                  <FormControl
                    fullWidth
                    required={!canManageGlobally}
                    error={!!errors.maintainer_group_id}
                  >
                    <InputLabel id="mg-label">Maintainer group</InputLabel>
                    <Select
                      labelId="mg-label"
                      label="Maintainer group"
                      value={data.maintainer_group_id}
                      onChange={(e) =>
                        setData(
                          'maintainer_group_id',
                          e.target.value === '' ? '' : Number(e.target.value)
                        )
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
                    <FormHelperText>{errors.maintainer_group_id}</FormHelperText>
                  </FormControl>
                </Grid2>

                <Grid2 size={{ xs: 12, md: 4 }}>
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
                      {errors.course_id || 'Optional — marks every item as requiring induction'}
                    </FormHelperText>
                  </FormControl>
                </Grid2>
              </Grid2>

              <Divider sx={{ my: 3 }} />

              <Typography variant="h6" component="h2" gutterBottom>
                Items
              </Typography>

              <Stack spacing={2}>
                {data.items.map((item, index) => {
                  const nameError =
                    rowErrors[`items.${index}.name`] || rowErrors[`items.${index}.slug`];
                  return (
                    <Stack key={index} direction="row" spacing={2} alignItems="flex-start">
                      <TextField
                        label="Name"
                        value={item.name}
                        onChange={(e) => updateItem(index, e.target.value)}
                        fullWidth
                        required
                        error={!!nameError}
                        helperText={nameError}
                      />
                      <IconButton
                        aria-label="Remove item"
                        color="error"
                        onClick={() => removeRow(index)}
                        disabled={data.items.length === 1}
                        sx={{ mt: 1 }}
                      >
                        <DeleteIcon />
                      </IconButton>
                    </Stack>
                  );
                })}
              </Stack>

              <Button startIcon={<AddIcon />} onClick={addRow} sx={{ mt: 2 }}>
                Add another item
              </Button>

              <Stack direction="row" sx={{ mt: 4 }}>
                <Button
                  type="submit"
                  variant="contained"
                  color="primary"
                  size="large"
                  disabled={processing}
                >
                  Create {data.items.length} item{data.items.length === 1 ? '' : 's'}
                </Button>
              </Stack>
            </form>
          </CardContent>
        </Card>
      </Container>
    </>
  );
};

BulkCreate.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default BulkCreate;
