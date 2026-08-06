import React from 'react';
import { Autocomplete, TextField, CircularProgress } from '@mui/material';
import { Member } from '../types/resources';
import { useMemberSearch } from '../hooks/useMemberSearch';

type Props = {
  value: Member[];
  onChange: (members: Member[]) => void;
  searchUrl: string;
  label: string;
  placeholder?: string;
  error?: boolean;
  helperText?: string;
};

/**
 * On-demand member multi-select. Chips seed from `value`; options come from the
 * members/search endpoint (already-selected members are excluded). Shared by the
 * area-coordinator, maintainer, and role-member pickers.
 */
const MemberMultiSelect: React.FC<Props> = ({
  value,
  onChange,
  searchUrl,
  label,
  placeholder = 'Search members…',
  error,
  helperText,
}) => {
  const {
    members: options,
    searching,
    search,
  } = useMemberSearch(searchUrl, { exclude: value.map((m) => m.id) });

  return (
    <Autocomplete
      multiple
      options={options}
      getOptionLabel={(option) => option.name}
      filterOptions={(x) => x}
      isOptionEqualToValue={(option, v) => option.id === v.id}
      loading={searching}
      value={value}
      onChange={(_, newValue) => onChange(newValue)}
      onInputChange={(_, input) => search(input)}
      noOptionsText="Type to search members"
      renderInput={(params) => (
        <TextField
          {...params}
          label={label}
          placeholder={placeholder}
          error={error}
          helperText={helperText}
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
    />
  );
};

export default MemberMultiSelect;
