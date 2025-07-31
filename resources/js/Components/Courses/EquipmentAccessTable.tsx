import React from "react";
import {
    Card,
    CardContent,
    Typography,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    Paper,
    Avatar,
    Link,
    Box,
    Chip,
} from "@mui/material";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import { EquipmentResource, InductionResource } from "../../types/resources";

type Props = {
    equipment: EquipmentResource[];
    userCourseInduction: InductionResource | null;
    isUserTrained: boolean;
};

const EquipmentAccessTable: React.FC<Props> = ({ 
    equipment, 
    userCourseInduction, 
    isUserTrained 
}) => {
    if (equipment.length === 0) {
        return null;
    }

    return (
        <Card>
            <CardContent>
                <Typography
                    variant="h5"
                    component="h2"
                    gutterBottom
                >
                    Equipment Access
                </Typography>
                <Typography
                    variant="body1"
                    color="text.secondary"
                    sx={{ mb: 3 }}
                >
                    Completing this induction will grant you access
                    to the following equipment:
                </Typography>

                <TableContainer
                    component={Paper}
                    variant="outlined"
                >
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell></TableCell>
                                <TableCell>Name</TableCell>
                                <TableCell>Location</TableCell>
                                <TableCell>PPE Required</TableCell>
                                <TableCell>Status</TableCell>
                                <TableCell align="center">
                                    Dangerous
                                </TableCell>
                                {isUserTrained && (
                                    <TableCell>Access Code</TableCell>
                                )}
                                <TableCell align="center">
                                    Trained
                                </TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {equipment.map((equipmentItem) => (
                                <TableRow key={equipmentItem.id}>
                                    <TableCell>
                                        {equipmentItem.photo_url ? (
                                            <Avatar
                                                src={equipmentItem.photo_url}
                                                alt={equipmentItem.name}
                                                variant="rounded"
                                                sx={{
                                                    width: 60,
                                                    height: 60,
                                                }}
                                            />
                                        ) : (
                                            <Avatar
                                                variant="rounded"
                                                sx={{
                                                    width: 60,
                                                    height: 60,
                                                    bgcolor: "grey.300",
                                                }}
                                            >
                                                🔧
                                            </Avatar>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <Link
                                            href={equipmentItem.urls.show}
                                            underline="hover"
                                        >
                                            <Typography
                                                variant="body2"
                                                fontWeight="medium"
                                            >
                                                {equipmentItem.name}
                                            </Typography>
                                        </Link>
                                    </TableCell>
                                    <TableCell>
                                        <Typography variant="body2">
                                            {equipmentItem.room_display && (
                                                <Box
                                                    component="span"
                                                    sx={{
                                                        fontWeight: "medium",
                                                    }}
                                                >
                                                    {equipmentItem.room_display}
                                                </Box>
                                            )}
                                        </Typography>
                                    </TableCell>
                                    <TableCell>
                                        {equipmentItem.ppe.length > 0 && (
                                            <Box
                                                sx={{
                                                    display: "flex",
                                                    flexWrap: "wrap",
                                                    gap: 0.5,
                                                }}
                                            >
                                                {equipmentItem.ppe.map((item, index) => (
                                                    <Chip
                                                        key={index}
                                                        label={item}
                                                        size="small"
                                                        variant="outlined"
                                                        color="info"
                                                    />
                                                ))}
                                            </Box>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <Box
                                            sx={{
                                                display: "flex",
                                                flexWrap: "wrap",
                                                gap: 0.5,
                                            }}
                                        >
                                            {Boolean(equipmentItem.working) ? (
                                                <Chip
                                                    label="Working"
                                                    size="small"
                                                    color="success"
                                                    variant="filled"
                                                />
                                            ) : (
                                                <Chip
                                                    label="Out of action"
                                                    size="small"
                                                    color="error"
                                                    variant="filled"
                                                />
                                            )}
                                            {Boolean(equipmentItem.permaloan) && (
                                                <Chip
                                                    label="Permaloan"
                                                    size="small"
                                                    color="warning"
                                                    variant="filled"
                                                />
                                            )}
                                            {!equipmentItem.lone_working && (
                                                <Chip
                                                    label="NO LONE WORKING"
                                                    size="small"
                                                    color="error"
                                                    variant="filled"
                                                />
                                            )}
                                        </Box>
                                    </TableCell>
                                    <TableCell align="center">
                                        {equipmentItem.dangerous ? "⚠️" : ""}
                                    </TableCell>
                                    {isUserTrained && (
                                        <TableCell>
                                            {equipmentItem.access_code ? (
                                                <Chip
                                                    label={equipmentItem.access_code}
                                                    size="small"
                                                    color="info"
                                                    variant="filled"
                                                />
                                            ) : (
                                                <Typography variant="body2" color="text.secondary">
                                                    None
                                                </Typography>
                                            )}
                                        </TableCell>
                                    )}
                                    <TableCell align="center">
                                        {isUserTrained && (
                                            <Box
                                                sx={{
                                                    display: "flex",
                                                    flexDirection: "column",
                                                    alignItems: "center",
                                                    gap: 0.5,
                                                }}
                                            >
                                                <CheckCircleIcon color="success" />
                                                <Typography
                                                    variant="caption"
                                                    color="text.secondary"
                                                >
                                                    {new Date(
                                                        userCourseInduction!.trained!
                                                    ).toLocaleDateString()}
                                                </Typography>
                                            </Box>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </TableContainer>
            </CardContent>
        </Card>
    );
};

export default EquipmentAccessTable;