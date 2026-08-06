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
  Box,
  IconButton,
} from '@mui/material';
import DeleteIcon from '@mui/icons-material/Delete';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import EquipmentForm, { EquipmentFormData } from '../../Components/EquipmentForm';
import FormErrorSummary from '../../Components/FormErrorSummary';
import { useForm, router } from '@inertiajs/react';
import { EquipmentFormResource } from '../../types/resources';

type Props = {
  equipment: EquipmentFormResource;
  rooms: Record<string, string>;
  maintainerGroupOptions: Record<string, string>;
  ppeOptions: Record<string, string>;
  memberSearch: string;
  usageCostPerOptions: Record<string, string>;
  courseOptions: { id: number; name: string; live: boolean }[];
  canManageGlobally: boolean;
  urls: {
    index: string;
  };
};

const Edit = ({
  equipment,
  rooms,
  maintainerGroupOptions,
  ppeOptions,
  memberSearch,
  usageCostPerOptions,
  courseOptions,
  canManageGlobally,
  urls,
}: Props) => {
  const { data, setData, put, processing, errors } = useForm<EquipmentFormData>({
    name: equipment.name,
    slug: equipment.slug,
    room_id: equipment.room_id ?? '',
    detail: equipment.detail || '',
    maintainer_group_id: equipment.maintainer_group_id ?? '',
    description: equipment.description || '',
    working: equipment.working,
    permaloan: equipment.permaloan,
    permaloan_user_id: equipment.permaloan_user_id ?? '',
    dangerous: equipment.dangerous,
    lone_working: equipment.lone_working,
    ppe: equipment.ppe,
    course_id: equipment.course_id ?? '',
    requires_induction: equipment.requires_induction,
    accepting_inductions: equipment.accepting_inductions,
    induction_category: equipment.induction_category || '',
    induction_instructions: equipment.induction_instructions || '',
    trained_instructions: equipment.trained_instructions || '',
    trainer_instructions: equipment.trainer_instructions || '',
    manufacturer: equipment.manufacturer || '',
    model_number: equipment.model_number || '',
    help_text: equipment.help_text || '',
    docs: equipment.docs || '',
    access_fee: equipment.access_fee,
    usage_cost: equipment.usage_cost,
    usage_cost_per: equipment.usage_cost_per || 'hour',
    access_code: equipment.access_code || '',
    admin_notes: equipment.admin_notes || '',
  });

  const photoForm = useForm<{ photo: File | null }>({ photo: null });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(equipment.urls.update);
  };

  const handlePhotoUpload = (e: React.FormEvent) => {
    e.preventDefault();
    photoForm.post(equipment.urls.photo_store, {
      forceFormData: true,
      onSuccess: () => photoForm.reset('photo'),
    });
  };

  const deletePhoto = (destroyUrl: string) => {
    if (window.confirm('Delete this photo?')) {
      router.delete(destroyUrl);
    }
  };

  return (
    <>
      <PageTitle title={`Edit ${equipment.name}`} />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Tools &amp; Equipment
          </Link>{' '}
          /{' '}
          <Link href={equipment.urls.show} color="inherit" underline="hover">
            {equipment.name}
          </Link>{' '}
          / Edit
        </Typography>

        <FormErrorSummary errors={errors} />

        <Grid2 container spacing={4}>
          <Grid2 size={12}>
            <Card>
              <CardContent>
                <EquipmentForm
                  data={data}
                  setData={setData}
                  errors={errors}
                  processing={processing}
                  onSubmit={handleSubmit}
                  submitLabel="Update item"
                  rooms={rooms}
                  maintainerGroupOptions={maintainerGroupOptions}
                  ppeOptions={ppeOptions}
                  memberSearchUrl={memberSearch}
                  initialPermaloanHolder={equipment.permaloan_user}
                  usageCostPerOptions={usageCostPerOptions}
                  courseOptions={courseOptions}
                  canManageGlobally={canManageGlobally}
                />
              </CardContent>
            </Card>
          </Grid2>

          <Grid2 size={12}>
            <Card>
              <CardContent>
                <Typography variant="h6" component="h3" gutterBottom>
                  Photos
                </Typography>

                <Grid2 container spacing={2} sx={{ mb: 2 }}>
                  {equipment.photos.map((photo) => (
                    <Grid2 key={photo.index} size={{ xs: 6, sm: 4, md: 3 }}>
                      <Box sx={{ position: 'relative' }}>
                        <Box
                          component="img"
                          src={photo.url}
                          alt={`${equipment.name} photo`}
                          sx={{ width: '100%', borderRadius: 1, display: 'block' }}
                        />
                        <IconButton
                          size="small"
                          color="error"
                          onClick={() => deletePhoto(photo.destroy_url)}
                          sx={{
                            position: 'absolute',
                            top: 4,
                            right: 4,
                            bgcolor: 'background.paper',
                          }}
                        >
                          <DeleteIcon fontSize="small" />
                        </IconButton>
                      </Box>
                    </Grid2>
                  ))}
                  {equipment.photos.length === 0 && (
                    <Grid2 size={12}>
                      <Typography color="text.secondary">No photos yet.</Typography>
                    </Grid2>
                  )}
                </Grid2>

                <form onSubmit={handlePhotoUpload}>
                  <Stack direction="row" spacing={2} alignItems="center">
                    <Button variant="outlined" component="label">
                      Choose image
                      <input
                        type="file"
                        hidden
                        accept="image/jpeg,image/png"
                        onChange={(e) => photoForm.setData('photo', e.target.files?.[0] ?? null)}
                      />
                    </Button>
                    {photoForm.data.photo && (
                      <Typography variant="body2">{photoForm.data.photo.name}</Typography>
                    )}
                    <Button
                      type="submit"
                      variant="contained"
                      disabled={!photoForm.data.photo || photoForm.processing}
                    >
                      Upload
                    </Button>
                  </Stack>
                  {photoForm.errors.photo && (
                    <Typography color="error" variant="body2" sx={{ mt: 1 }}>
                      {photoForm.errors.photo}
                    </Typography>
                  )}
                </form>
              </CardContent>
            </Card>
          </Grid2>
        </Grid2>
      </Container>
    </>
  );
};

Edit.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Edit;
