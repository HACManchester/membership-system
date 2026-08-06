import React from 'react';
import { Typography, Container, Card, CardContent, Grid2, Link } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import EquipmentForm, { EquipmentFormData } from '../../Components/EquipmentForm';
import FormErrorSummary from '../../Components/FormErrorSummary';
import { useForm } from '@inertiajs/react';

type Props = {
  rooms: Record<string, string>;
  maintainerGroupOptions: Record<string, string>;
  ppeOptions: Record<string, string>;
  memberSearch: string;
  usageCostPerOptions: Record<string, string>;
  courseOptions: { id: number; name: string; live: boolean }[];
  canManageGlobally: boolean;
  urls: {
    index: string;
    store: string;
  };
};

const defaultData: EquipmentFormData = {
  name: '',
  slug: '',
  room_id: '',
  detail: '',
  maintainer_group_id: '',
  description: '',
  working: true,
  permaloan: false,
  permaloan_user_id: '',
  dangerous: false,
  lone_working: true,
  ppe: [],
  course_id: '',
  requires_induction: false,
  accepting_inductions: false,
  induction_category: '',
  induction_instructions: '',
  trained_instructions: '',
  trainer_instructions: '',
  manufacturer: '',
  model_number: '',
  help_text: '',
  docs: '',
  access_fee: 0,
  usage_cost: 0,
  usage_cost_per: 'hour',
  access_code: '',
  admin_notes: '',
};

const Create = ({
  rooms,
  maintainerGroupOptions,
  ppeOptions,
  memberSearch,
  usageCostPerOptions,
  courseOptions,
  canManageGlobally,
  urls,
}: Props) => {
  const { data, setData, post, processing, errors } = useForm<EquipmentFormData>(defaultData);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(urls.store);
  };

  return (
    <>
      <PageTitle title="Record a new item" />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Tools &amp; Equipment
          </Link>{' '}
          / Record a new item
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
                  submitLabel="Create item"
                  rooms={rooms}
                  maintainerGroupOptions={maintainerGroupOptions}
                  ppeOptions={ppeOptions}
                  memberSearchUrl={memberSearch}
                  initialPermaloanHolder={null}
                  usageCostPerOptions={usageCostPerOptions}
                  courseOptions={courseOptions}
                  canManageGlobally={canManageGlobally}
                />
              </CardContent>
            </Card>
          </Grid2>
        </Grid2>
      </Container>
    </>
  );
};

Create.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Create;
