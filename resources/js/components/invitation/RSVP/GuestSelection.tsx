import { Circle, CircleCheckBig } from 'lucide-react';
import type { Group } from '@/types';
import { palette } from './../theme';

interface GuestSelectionProps {
    group: Group;
    confirmedGuestIds: number[];
    onToggleGuest: (id: number) => void;
    error?: string;
}

export default function GuestSelection({
    group,
    confirmedGuestIds,
    onToggleGuest,
    error,
}: GuestSelectionProps) {
    const singleGuestLabel = (selected: boolean) => {
        return selected ? 'Potvrdjeno' : 'Potvrdi dolazak';
    };

    return (
        <div className="flex flex-col gap-2">
            <label className="text-sm tracking-wider" style={{ color: palette.deep }}>
                Potvrdite dolazak za:
            </label>

            <div className="flex flex-col gap-3">
                {group.guests.map((guest) => {
                    const selected = confirmedGuestIds.includes(guest.id);

                    return (
                        <button
                            key={guest.id}
                            type="button"
                            onClick={() => onToggleGuest(guest.id)}
                            className="relative flex w-full items-center justify-center rounded-lg px-4 py-3.5 text-base font-medium tracking-wide transition-all duration-300 hover:cursor-pointer"
                            style={{
                                backgroundColor: selected ? palette.deep : 'rgba(255, 255, 255, 0.5)',
                                color: selected ? palette.background : palette.dawn,
                                border: '1px solid rgba(67, 58, 102, 0.2)',
                            }}
                        >
                            <span className="absolute left-4 flex items-center">
                                {selected ? (
                                    <CircleCheckBig size={18} style={{ color: palette.background }} />
                                ) : (
                                    <Circle size={18} style={{ color: palette.dawn }} />
                                )}
                            </span>
                            <span className="truncate">
                                {group.has_single_guest ? singleGuestLabel(selected) : guest.full_name}
                            </span>
                        </button>
                    );
                })}
            </div>
            {error && <p className="text-sm text-red-500">{error}</p>}
        </div>
    );
}
