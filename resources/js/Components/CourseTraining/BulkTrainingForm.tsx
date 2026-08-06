import React, { useState } from 'react';
import {
  Paper,
  Typography,
  Box,
  Button,
  Autocomplete,
  TextField,
  CircularProgress,
} from '@mui/material';
import { router } from '@inertiajs/react';
import { Member } from '../../types/resources';
import { useMemberSearch } from '../../hooks/useMemberSearch';

type Props = {
  memberSearchUrl: string;
  excludeIds?: number[];
  bulkTrainUrl: string;
};

const BulkTrainingForm: React.FC<Props> = ({ memberSearchUrl, excludeIds = [], bulkTrainUrl }) => {
  const [selectedMembers, setSelectedMembers] = useState<Member[]>([]);
  // Already-trained members are dropped from the on-demand search results.
  const {
    members: options,
    searching,
    search: searchMembers,
  } = useMemberSearch(memberSearchUrl, { exclude: excludeIds });

  const handleBulkTrain = () => {
    if (selectedMembers.length > 0) {
      router.post(bulkTrainUrl, {
        user_ids: selectedMembers.map((m) => m.id),
      });
      setSelectedMembers([]);
    }
  };

  return (
    <Paper sx={{ p: 3 }} variant="outlined">
      <Typography variant="h6" gutterBottom>
        Bulk Add Trained Members
      </Typography>
      <Box display="flex" gap={2} alignItems="flex-end">
        <Autocomplete
          multiple
          options={options}
          getOptionLabel={(option) => option.name}
          filterOptions={(x) => x}
          isOptionEqualToValue={(option, value) => option.id === value.id}
          loading={searching}
          value={selectedMembers}
          onChange={(_, newValue) => {
            setSelectedMembers(newValue);
          }}
          onInputChange={(_, value) => searchMembers(value)}
          noOptionsText="Type to search members"
          renderInput={(params) => (
            <TextField
              {...params}
              variant="outlined"
              label="Select members to mark as trained"
              placeholder="Search for members..."
              InputProps={{
                ...params.InputProps,
                endAdornment: (
                  <>
                    {searching ? <CircularProgress size={18} /> : null}
                    {params.InputProps.endAdornment}
                  </>
                ),
              }}
            />
          )}
          sx={{ flexGrow: 1 }}
        />
        <Button
          variant="contained"
          onClick={handleBulkTrain}
          disabled={selectedMembers.length === 0}
        >
          Mark Selected as Trained
        </Button>
      </Box>
    </Paper>
  );
};

export default BulkTrainingForm;
