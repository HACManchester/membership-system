import React from 'react';
import {
  Card,
  CardContent,
  Container,
  Grid,
  Link,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Typography,
} from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import Paginator from '../../Components/Paginator';
import { SimplePaginated } from '../../types/pagination';

type BalancePayment = {
  id: number;
  reason: string;
  method: string;
  date: string | null;
  direction: 'debit' | 'credit' | null;
  amount: string | null;
  status: string;
};

type Props = {
  balance: {
    formatted: string;
    negative: boolean;
  };
  payments: SimplePaginated<BalancePayment>;
};

const amountColor = (direction: BalancePayment['direction']) =>
  direction === 'debit' ? 'error.main' : direction === 'credit' ? 'success.main' : undefined;

const Index = ({ balance, payments }: Props) => {
  return (
    <>
      <PageTitle title="Hackspace Manchester Balance" />
      <Container sx={{ py: 4 }}>
        <Grid container spacing={3} sx={{ mb: 4 }}>
          <Grid size={{ xs: 12, md: 3 }}>
            <Card sx={{ height: '100%' }}>
              <CardContent>
                <Typography variant="h6" component="h2" gutterBottom>
                  Your Balance
                </Typography>
                <Typography>Your current balance is:</Typography>
                <Typography
                  sx={{
                    fontSize: '3em',
                    fontWeight: 'bold',
                    color: balance.negative ? 'error.main' : 'success.main',
                  }}
                >
                  {balance.formatted}
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid size={{ xs: 12, md: 9 }}>
            <Card sx={{ height: '100%' }}>
              <CardContent>
                <Typography variant="h6" component="h2" gutterBottom>
                  Balances have been removed from the Hackspace Manchester membership system.
                </Typography>
                <Typography sx={{ mb: 2 }}>
                  Now that we are able to take card payments at the Hackspace we have removed the
                  online balance features. Paying via card is much more cost effective for us, and
                  makes it easier to maintain our digital systems.
                </Typography>
                <Typography>
                  If you need to make a payment but are unable to visit the Hackspace in person,
                  please contact the board on{' '}
                  <Link href="mailto:board@hacman.org.uk">board@hacman.org.uk</Link>.
                </Typography>
              </CardContent>
            </Card>
          </Grid>
        </Grid>

        <Card>
          <CardContent>
            <Typography variant="h6" component="h2" gutterBottom>
              📒 Balance History
            </Typography>
            <TableContainer>
              <Table>
                <TableHead>
                  <TableRow>
                    <TableCell>Reason</TableCell>
                    <TableCell>Method</TableCell>
                    <TableCell>Date</TableCell>
                    <TableCell>Amount</TableCell>
                    <TableCell>Status</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {payments.data.map((payment) => (
                    <TableRow key={payment.id}>
                      <TableCell>{payment.reason}</TableCell>
                      <TableCell>{payment.method}</TableCell>
                      <TableCell>{payment.date}</TableCell>
                      <TableCell sx={{ color: amountColor(payment.direction), fontWeight: 'bold' }}>
                        {payment.amount}
                      </TableCell>
                      <TableCell>{payment.status}</TableCell>
                    </TableRow>
                  ))}
                  {payments.data.length === 0 && (
                    <TableRow>
                      <TableCell colSpan={5} sx={{ color: 'text.secondary' }}>
                        No balance payments yet.
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </TableContainer>
            <Paginator paginator={payments} />
          </CardContent>
        </Card>
      </Container>
    </>
  );
};

Index.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Index;
