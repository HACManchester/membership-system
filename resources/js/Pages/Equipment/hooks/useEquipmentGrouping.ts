import { useMemo } from 'react';
import { EquipmentListResource } from '../../../types/resources';

export const NOT_ASSIGNED = 'Not assigned to a room';

export type EquipmentGroup = {
  room: string;
  equipment: EquipmentListResource[];
};

/**
 * Groups a flat equipment list by its room, alphabetically, with any
 * unassigned equipment collected into a final bucket.
 */
export const useEquipmentGrouping = (equipment: EquipmentListResource[]): EquipmentGroup[] => {
  return useMemo(() => {
    const groups: Record<string, EquipmentListResource[]> = {};

    for (const item of equipment) {
      const key = item.room_display || NOT_ASSIGNED;
      (groups[key] ||= []).push(item);
    }

    const sortedKeys = Object.keys(groups).sort((a, b) => {
      if (a === NOT_ASSIGNED) return 1;
      if (b === NOT_ASSIGNED) return -1;
      return a.localeCompare(b);
    });

    return sortedKeys.map((room) => ({ room, equipment: groups[room] }));
  }, [equipment]);
};
