import React, { useState } from 'react';
import {
    TextField,
    Box,
    Paper,
    Typography,
    ToggleButton,
    ToggleButtonGroup,
    TextFieldProps,
} from '@mui/material';
import PreviewIcon from '@mui/icons-material/Preview';
import EditIcon from '@mui/icons-material/Edit';
import MarkdownRenderer from './MarkdownRenderer';

interface MarkdownTextFieldProps extends Omit<TextFieldProps, 'multiline' | 'rows'> {
    value: string;
    onChange: (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => void;
    rows?: number;
}

const MarkdownTextField: React.FC<MarkdownTextFieldProps> = ({
    value,
    onChange,
    rows = 3,
    label,
    helperText,
    ...textFieldProps
}) => {
    const [mode, setMode] = useState<'edit' | 'preview'>('edit');

    const handleModeChange = (
        _event: React.MouseEvent<HTMLElement>,
        newMode: 'edit' | 'preview' | null,
    ) => {
        if (newMode !== null) {
            setMode(newMode);
        }
    };

    return (
        <Box>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1 }}>
                <Typography variant="subtitle2" color="text.secondary">
                    {label}
                </Typography>
                <ToggleButtonGroup
                    value={mode}
                    exclusive
                    onChange={handleModeChange}
                    size="small"
                >
                    <ToggleButton value="edit" aria-label="edit">
                        <EditIcon fontSize="small" />
                    </ToggleButton>
                    <ToggleButton value="preview" aria-label="preview">
                        <PreviewIcon fontSize="small" />
                    </ToggleButton>
                </ToggleButtonGroup>
            </Box>

            {mode === 'edit' ? (
                <TextField
                    {...textFieldProps}
                    value={value}
                    onChange={onChange}
                    multiline
                    rows={rows}
                    fullWidth
                    helperText={helperText}
                />
            ) : (
                <Paper 
                    variant="outlined" 
                    sx={{ 
                        p: 2, 
                        minHeight: `${rows * 24 + 32}px`,
                        backgroundColor: 'grey.50'
                    }}
                >
                    {value ? (
                        <MarkdownRenderer content={value} variant="body1" />
                    ) : (
                        <Typography variant="body1" color="text.secondary" fontStyle="italic">
                            No content to preview
                        </Typography>
                    )}
                </Paper>
            )}
        </Box>
    );
};

export default MarkdownTextField;