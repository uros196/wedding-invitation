import { UserRoundPlus } from 'lucide-react';
import { FloatingLabelInput } from './../FloatingField';
import { palette } from './../theme';

interface PlusOneFieldProps {
    expanded: boolean;
    name: string;
    onToggle: () => void;
    onNameChange: (name: string) => void;
    error?: string;
}

export default function PlusOneField({
    expanded,
    name,
    onToggle,
    onNameChange,
    error,
}: PlusOneFieldProps) {
    return (
        <div className="flex flex-col gap-2">
            <button
                type="button"
                onClick={onToggle}
                className="group mt-6 flex h-15 w-full cursor-pointer items-center justify-between rounded-2xl border border-gray-200 bg-gray-100 px-6 transition-all duration-200 hover:border-gray-300 hover:bg-gray-200"
                style={{ color: palette.celestial }}
            >
                <span className="text-lg font-medium select-none">
                    Imaš pratnju?
                </span>

                <UserRoundPlus
                    size={30}
                    className="text-#0B2F5B-100 group-hover:bg-#9875A6-50 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-none transition-transform duration-500 ease-in-out"
                />
            </button>

            {expanded && (
                <div className="mt-2 flex animate-in flex-col gap-4 overflow-hidden pt-3 duration-500 fade-in slide-in-from-top-2">
                    <FloatingLabelInput
                        id="plus_one_first_name"
                        label="Ime i prezime pratnje"
                        name="plus_one[full_name]"
                        value={name}
                        onChange={(event) => onNameChange(event.target.value)}
                        error={error}
                    />
                </div>
            )}
        </div>
    );
}
