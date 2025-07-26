import React, { useState } from "react";
import {
    Paper,
    Typography,
    Box,
    Button,
    Autocomplete,
    TextField,
} from "@mui/material";
import { router } from "@inertiajs/react";
import { Member } from "../../types/resources";

type Props = {
    memberList: Member[];
    bulkTrainUrl: string;
};

const BulkTrainingForm: React.FC<Props> = ({ memberList, bulkTrainUrl }) => {
    const [selectedMembers, setSelectedMembers] = useState<Member[]>([]);

    const handleBulkTrain = () => {
        if (selectedMembers.length > 0) {
            router.post(bulkTrainUrl, { 
                user_ids: selectedMembers.map(m => m.id) 
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
                    options={memberList}
                    getOptionLabel={(option) => option.name}
                    value={selectedMembers}
                    onChange={(event, newValue) => {
                        setSelectedMembers(newValue);
                    }}
                    renderInput={(params) => (
                        <TextField
                            {...params}
                            variant="outlined"
                            label="Select members to mark as trained"
                            placeholder="Search for members..."
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