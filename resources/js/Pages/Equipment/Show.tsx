import React, { useState } from 'react';
import {
  Typography,
  Container,
  Card,
  CardContent,
  Grid2,
  Link,
  Button,
  Stack,
  Chip,
  Alert,
  Box,
  Divider,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableRow,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
} from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import MarkdownRenderer from '../../Components/MarkdownRenderer';
import EquipmentTrainingSections, {
  EquipmentTraining,
} from '../../Components/Equipment/EquipmentTrainingSections';
import { router } from '@inertiajs/react';

type Ppe = { key: string; label: string; image: string };

type EquipmentShow = {
  id: number;
  name: string;
  slug: string;
  working: boolean;
  dangerous: boolean;
  lone_working: boolean;
  permaloan: boolean;
  requires_induction: boolean;
  accepting_inductions: boolean;
  room_name: string | null;
  location_detail: string | null;
  maintainer_group: { name: string; url: string } | null;
  manufacturer_model: string | null;
  purchase_date: string | null;
  usage_cost: string | null;
  ppe: Ppe[];
  photos: string[];
  description: string | null;
  help_text: string | null;
  docs: string | null;
  induction_instructions?: string | null;
  trained_instructions?: string | null;
  trainer_instructions?: string | null;
  access_code?: string;
  admin_notes?: string | null;
};

type Props = {
  equipment: EquipmentShow;
  courses: { name: string; live: boolean; url: string }[];
  flags: { useLegacyInduction: boolean; liveCourse: boolean };
  userStatus: { hasRecord: boolean; trained: boolean; isTrainer: boolean };
  canRequestInduction: boolean;
  training: EquipmentTraining | null;
  memberList: Record<string, string>;
  authUserId: number;
  can: { update: boolean; delete: boolean; train: boolean };
  urls: {
    index: string;
    edit: string;
    destroy: string;
    requestInduction: string;
    emailTrainers: string;
    emailTrained: string;
    emailAwaiting: string;
  };
};

const InfoRow = ({ label, children }: { label: string; children: React.ReactNode }) => (
  <TableRow>
    <TableCell
      component="th"
      scope="row"
      sx={{ bgcolor: 'primary.light', fontWeight: 'bold', width: '40%', verticalAlign: 'middle' }}
    >
      {label}
    </TableCell>
    <TableCell>{children}</TableCell>
  </TableRow>
);

const Show = ({
  equipment,
  courses,
  flags,
  userStatus,
  canRequestInduction,
  training,
  memberList,
  authUserId,
  can,
  urls,
}: Props) => {
  const [deleteOpen, setDeleteOpen] = useState(false);
  const [lightbox, setLightbox] = useState<string | null>(null);

  const actionButtons = (
    <Stack direction="row" spacing={1} justifyContent="flex-end">
      {can.update && (
        <Link href={urls.edit} underline="none">
          <Button variant="contained" color="primary">
            Edit
          </Button>
        </Link>
      )}
      {can.delete && (
        <Button variant="outlined" color="error" onClick={() => setDeleteOpen(true)}>
          Delete
        </Button>
      )}
    </Stack>
  );

  const requestInduction = () => router.post(urls.requestInduction, {}, { preserveScroll: true });

  // Induction status blocks — shown below the main equipment info, not above it.
  const inductionBlocks = (
    <>
      {flags.liveCourse && (
        <Alert severity="info">
          <Typography gutterBottom>Training for this equipment is managed by:</Typography>
          <ul style={{ margin: 0, paddingLeft: 18 }}>
            {courses.map((course) => (
              <li key={course.url}>
                <Link href={course.url}>{course.name}</Link>
              </li>
            ))}
          </ul>
          <Typography sx={{ mt: 1 }}>Please visit the induction page to book training.</Typography>
        </Alert>
      )}

      {canRequestInduction && (
        <Alert
          severity="warning"
          action={
            <Button color="inherit" size="small" onClick={requestInduction}>
              Request induction
            </Button>
          }
        >
          An induction is required before you may use this tool.
        </Alert>
      )}

      {flags.useLegacyInduction &&
        !userStatus.hasRecord &&
        !canRequestInduction &&
        !equipment.accepting_inductions && (
          <Alert severity="warning">Inductions are currently paused for {equipment.name}.</Alert>
        )}

      {userStatus.hasRecord && !userStatus.trained && (
        <Alert severity="info">
          <Typography gutterBottom>Training to be completed.</Typography>
          {equipment.induction_instructions ? (
            <MarkdownRenderer content={equipment.induction_instructions} />
          ) : (
            <Typography>To get trained, ask on the forum or on Telegram.</Typography>
          )}
        </Alert>
      )}

      {userStatus.trained && userStatus.isTrainer && equipment.trainer_instructions && (
        <Card variant="outlined">
          <CardContent>
            <Typography variant="subtitle1" component="h3" gutterBottom>
              Trainer instructions
            </Typography>
            <MarkdownRenderer content={equipment.trainer_instructions} />
          </CardContent>
        </Card>
      )}

      {userStatus.trained && equipment.trained_instructions && (
        <Card variant="outlined">
          <CardContent>
            <Typography variant="subtitle1" component="h3" gutterBottom>
              Instructions for use
            </Typography>
            <MarkdownRenderer content={equipment.trained_instructions} />
          </CardContent>
        </Card>
      )}
    </>
  );

  return (
    <>
      <PageTitle title={equipment.name} actionButtons={actionButtons} />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Tools &amp; Equipment
          </Link>{' '}
          / {equipment.name}
        </Typography>

        {/* Status chips */}
        <Stack direction="row" spacing={1} useFlexGap flexWrap="wrap" sx={{ mb: 3 }}>
          {equipment.requires_induction && !userStatus.hasRecord && (
            <Chip label="Training required" color="error" />
          )}
          {equipment.requires_induction && userStatus.trained && (
            <Chip label="You are inducted and can use this equipment" color="success" />
          )}
          {equipment.requires_induction && userStatus.hasRecord && !userStatus.trained && (
            <Chip label="Training to be completed" color="warning" />
          )}
          {userStatus.trained && equipment.access_code && (
            <Chip label={`🔑 Access code: ${equipment.access_code}`} color="info" />
          )}
          {!equipment.working && <Chip label="Out of action" color="error" variant="outlined" />}
          {equipment.dangerous && <Chip label="⚠️ Bloody dangerous" color="error" />}
          {!equipment.lone_working && (
            <Chip label="No lone working" color="error" variant="outlined" />
          )}
        </Stack>

        {/* Main equipment information */}
        <Grid2 container spacing={3}>
          <Grid2 size={{ xs: 12, md: 7 }}>
            <TableContainer component={Card}>
              <Table size="small">
                <TableBody>
                  {(equipment.room_name || equipment.location_detail) && (
                    <InfoRow label="Lives in">
                      {equipment.room_name && <Typography>🏠 {equipment.room_name}</Typography>}
                      {equipment.location_detail && (
                        <Typography variant="body2" color="text.secondary">
                          {equipment.location_detail}
                        </Typography>
                      )}
                    </InfoRow>
                  )}
                  {equipment.maintainer_group && (
                    <InfoRow label="Maintained by">
                      <Link href={equipment.maintainer_group.url}>
                        🛠️ {equipment.maintainer_group.name}
                      </Link>
                    </InfoRow>
                  )}
                  <InfoRow label="Working?">{equipment.working ? '🟢 Yes' : '🔴 No'}</InfoRow>
                  <InfoRow label="Induction required?">
                    {equipment.requires_induction ? '🔴 Yes' : '🟢 No'}
                  </InfoRow>
                  <InfoRow label="Lone working allowed?">
                    {equipment.lone_working ? '🟢 Yes' : '🔴 No lone working'}
                  </InfoRow>
                  {equipment.manufacturer_model && (
                    <InfoRow label="Manufacturer / model">
                      🔧 {equipment.manufacturer_model}
                    </InfoRow>
                  )}
                  {equipment.purchase_date && (
                    <InfoRow label="Purchase date">📅 {equipment.purchase_date}</InfoRow>
                  )}
                  <InfoRow label="Usage cost">
                    💸 {equipment.usage_cost || 'No usage charge'}
                  </InfoRow>
                  <InfoRow label="Permaloan?">{equipment.permaloan ? '🟢 Yes' : '🔴 No'}</InfoRow>
                </TableBody>
              </Table>
            </TableContainer>
          </Grid2>

          <Grid2 size={{ xs: 12, md: 5 }}>
            <Stack spacing={2}>
              {equipment.dangerous && (
                <Box
                  sx={{
                    p: 2,
                    color: 'white',
                    fontWeight: 'bold',
                    textAlign: 'center',
                    border: '3px solid #e00',
                    background:
                      'repeating-linear-gradient(45deg, #f00, #f00 10px, #e00 10px, #e00 20px)',
                  }}
                >
                  This tool is bloody dangerous
                </Box>
              )}

              <Card>
                <CardContent>
                  <Typography variant="h6" component="h3" gutterBottom>
                    Personal protective equipment
                  </Typography>
                  {equipment.ppe.length > 0 ? (
                    <Stack direction="row" spacing={2} useFlexGap flexWrap="wrap">
                      {equipment.ppe.map((item) => (
                        <Stack key={item.key} alignItems="center" sx={{ width: 90 }}>
                          <Box
                            component="img"
                            src={item.image}
                            alt={item.label}
                            sx={{ width: 80, height: 80, objectFit: 'contain' }}
                          />
                          <Typography variant="caption" align="center">
                            {item.label}
                          </Typography>
                        </Stack>
                      ))}
                    </Stack>
                  ) : (
                    <Typography color="text.secondary">
                      No specific PPE is required. You must still be aware of risks and use relevant
                      PPE to mitigate them.
                    </Typography>
                  )}
                </CardContent>
              </Card>

              {equipment.photos.length > 0 && (
                <Card>
                  <CardContent>
                    <Typography variant="h6" component="h3" gutterBottom>
                      Photos
                    </Typography>
                    <Stack direction="row" spacing={1} useFlexGap flexWrap="wrap">
                      {equipment.photos.map((photo) => (
                        <Box
                          key={photo}
                          component="img"
                          src={photo}
                          alt={equipment.name}
                          onClick={() => setLightbox(photo)}
                          sx={{ width: 120, borderRadius: 1, cursor: 'pointer' }}
                        />
                      ))}
                    </Stack>
                  </CardContent>
                </Card>
              )}
            </Stack>
          </Grid2>

          {/* Description / help / docs */}
          <Grid2 size={12}>
            <Card>
              <CardContent>
                <Typography variant="h6" component="h3" gutterBottom>
                  Description
                </Typography>
                <MarkdownRenderer content={equipment.description} />

                {equipment.help_text && (
                  <>
                    <Divider sx={{ my: 2 }} />
                    <Typography variant="h6" component="h3" gutterBottom>
                      {equipment.name} help
                    </Typography>
                    <MarkdownRenderer content={equipment.help_text} />
                  </>
                )}

                {equipment.docs && (
                  <Box sx={{ mt: 2 }}>
                    <Button
                      variant="contained"
                      color="success"
                      href={equipment.docs}
                      target="_blank"
                    >
                      View documentation
                    </Button>
                  </Box>
                )}
              </CardContent>
            </Card>
          </Grid2>

          {equipment.admin_notes && (
            <Grid2 size={12}>
              <Card variant="outlined" sx={{ borderColor: 'warning.main' }}>
                <CardContent>
                  <Typography variant="h6" component="h3" gutterBottom>
                    Admin notes
                  </Typography>
                  <Typography variant="body2" color="text.secondary" gutterBottom>
                    Only visible to members who can edit this equipment.
                  </Typography>
                  <MarkdownRenderer content={equipment.admin_notes} />
                </CardContent>
              </Card>
            </Grid2>
          )}
        </Grid2>

        {/* Induction & training — below the main information, deliberately secondary */}
        <Stack spacing={2} sx={{ mt: 4 }}>
          {inductionBlocks}
        </Stack>

        {training && (
          <EquipmentTrainingSections
            training={training}
            can={{ train: can.train }}
            memberList={memberList}
            authUserId={authUserId}
            urls={{
              requestInduction: urls.requestInduction,
              emailTrainers: urls.emailTrainers,
              emailTrained: urls.emailTrained,
              emailAwaiting: urls.emailAwaiting,
            }}
          />
        )}

        <Alert severity="warning" sx={{ mt: 4 }}>
          If something is wrong or missing on this page, please raise it on the forum or Telegram.
        </Alert>
      </Container>

      {/* Delete confirmation */}
      <Dialog open={deleteOpen} onClose={() => setDeleteOpen(false)}>
        <DialogTitle>Confirm deletion</DialogTitle>
        <DialogContent>
          <DialogContentText>
            Deleting <em>{equipment.name}</em> removes it from the members system entirely. Are you
            sure?
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setDeleteOpen(false)}>Cancel</Button>
          <Button color="error" onClick={() => router.delete(urls.destroy)}>
            Delete
          </Button>
        </DialogActions>
      </Dialog>

      {/* Photo lightbox */}
      <Dialog open={!!lightbox} onClose={() => setLightbox(null)} maxWidth="lg">
        {lightbox && (
          <Box
            component="img"
            src={lightbox}
            alt={equipment.name}
            sx={{ width: '100%', display: 'block' }}
          />
        )}
      </Dialog>
    </>
  );
};

Show.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Show;
