import { useForm } from '@inertiajs/react';
import { CircleCheck, CircleCheckBig, Heart, Plus } from 'lucide-react';
import { useState } from 'react';

import { confirm } from '@/routes/group';
import type { Group } from '@/types';

import { FloatingLabelInput, FloatingLabelTextarea } from './FloatingField';
import { fonts, palette } from './theme';

interface RSVPFormProps {
    group: Group;
    /** Formatted RSVP deadline shown in the intro copy. */
    rsvpDeadline: string;
}

interface RSVPFormData {
    confirmed_guest_ids: number[];
    message: string;
    plus_one: {
        first_name: string;
        last_name: string;
    };
}

/**
 * RSVP form. Guests toggle their attendance, may add a plus one (when the
 * group allows it) and can leave a message for the couple. The data is posted
 * to the `group.confirm` route via Inertia.
 */
export default function RSVPForm({ group, rsvpDeadline }: RSVPFormProps) {
    const [plusOneExpanded, setPlusOneExpanded] = useState(false);

    const { data, setData, post, processing, errors, wasSuccessful, transform } = useForm<RSVPFormData>({
        confirmed_guest_ids: group.guests.filter((guest) => guest.is_accepted).map((guest) => guest.id),
        message: '',
        plus_one: { first_name: '', last_name: '' },
    });

    const hasPlusOneData = Boolean(data.plus_one.first_name || data.plus_one.last_name);
    const includePlusOne = group.has_plus_one && plusOneExpanded && hasPlusOneData;

    transform((formData) => ({
        confirmed_guest_ids: formData.confirmed_guest_ids,
        message: formData.message.trim() || null,
        ...(includePlusOne ? { plus_one: formData.plus_one } : {}),
    }));

    const toggleGuest = (id: number) => {
        setData(
            'confirmed_guest_ids',
            data.confirmed_guest_ids.includes(id)
                ? data.confirmed_guest_ids.filter((guestId) => guestId !== id)
                : [...data.confirmed_guest_ids, id],
        );
    };

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        post(confirm.url(group.uuid), { preserveScroll: true });
    };

    if (wasSuccessful) {
        return (
            <div
                className="relative z-20 flex w-full flex-col items-center gap-4 px-0 py-12 text-center sm:px-6"
                style={{ backgroundColor: palette.background, fontFamily: fonts.serif }}
            >
                <Heart size={28} style={{ color: palette.celestial }} />
                <p className="text-2xl font-medium tracking-wide italic" style={{ color: palette.celestial }}>
                    Hvala na potvrdi!
                </p>
            </div>
        );
    }

    return (
        <div className="relative z-20 px-6 py-12" style={{ backgroundColor: palette.background, fontFamily: fonts.serif }}>
            <div
                className="mx-auto max-w-md rounded-2xl p-6 sm:p-8"
                style={{ backgroundColor: 'rgba(255, 255, 255, 0.3)', border: '1px solid rgba(67, 58, 102, 0.15)' }}
            >
                <h3 className="mb-2 text-center text-3xl font-medium tracking-wide" style={{ color: palette.celestial }}>
                    Potvrda dolaska
                </h3>
                <p className="mb-8 text-center text-base" style={{ color: palette.celestial }}>
                    Molimo vas da potvrdite do {rsvpDeadline}.
                </p>

                <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                    {/* Guest list */}
                    <div className="flex flex-col gap-2">
                        <label className="text-sm tracking-wider opacity-80" style={{ color: palette.dawn }}>
                            Potvrdite dolazak za:
                        </label>
                        <div className="flex flex-col gap-3">
                            {group.guests.map((guest) => {
                                const selected = data.confirmed_guest_ids.includes(guest.id);

                                return (
                                    <button
                                        key={guest.id}
                                        type="button"
                                        onClick={() => toggleGuest(guest.id)}
                                        className="relative flex w-full items-center justify-center rounded-lg px-4 py-3.5 text-base font-medium tracking-wide transition-all duration-300"
                                        style={{
                                            backgroundColor: selected ? palette.deep : 'rgba(255, 255, 255, 0.5)',
                                            color: selected ? palette.background : palette.celestial,
                                            border: '1px solid rgba(67, 58, 102, 0.2)',
                                        }}
                                    >
                                        <span className="absolute left-4 flex items-center">
                                            {selected ? (
                                                <CircleCheckBig size={18} style={{ color: palette.background }} />
                                            ) : (
                                                <CircleCheck size={18} style={{ color: palette.celestial }} />
                                            )}
                                        </span>
                                        <span className="truncate">{guest.full_name}</span>
                                    </button>
                                );
                            })}
                        </div>
                        {errors.confirmed_guest_ids && (
                            <p className="text-sm text-red-500">{errors.confirmed_guest_ids}</p>
                        )}
                    </div>

                    {/* Plus one */}
                    {group.has_plus_one && (
                        <div className="flex flex-col gap-2">
                            <button
                                type="button"
                                onClick={() => setPlusOneExpanded((expanded) => !expanded)}
                                className="flex items-center gap-2 self-start text-base font-medium transition-all duration-300"
                                style={{ color: palette.celestial }}
                            >
                                Imaš pratnju?
                                <Plus
                                    size={14}
                                    className="transition-transform duration-500"
                                    style={{ transform: plusOneExpanded ? 'rotate(45deg)' : 'rotate(0deg)' }}
                                />
                            </button>

                            {plusOneExpanded && (
                                <div className="animate-in fade-in slide-in-from-top-2 mt-5 flex flex-col gap-4 overflow-hidden duration-500">
                                    <FloatingLabelInput
                                        id="plus_one_first_name"
                                        label="Ime pratnje"
                                        value={data.plus_one.first_name}
                                        onChange={(event) =>
                                            setData('plus_one', { ...data.plus_one, first_name: event.target.value })
                                        }
                                        error={errors['plus_one.first_name']}
                                    />
                                    <FloatingLabelInput
                                        id="plus_one_last_name"
                                        label="Prezime pratnje"
                                        value={data.plus_one.last_name}
                                        onChange={(event) =>
                                            setData('plus_one', { ...data.plus_one, last_name: event.target.value })
                                        }
                                        error={errors['plus_one.last_name']}
                                    />
                                </div>
                            )}
                        </div>
                    )}

                    {/* Message */}
                    <FloatingLabelTextarea
                        id="message"
                        label="Poruka za mladence"
                        value={data.message}
                        onChange={(event) => setData('message', event.target.value)}
                        error={errors.message}
                    />

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-lg py-4 text-sm font-semibold tracking-widest uppercase transition-opacity duration-300 disabled:opacity-60"
                        style={{ backgroundColor: palette.deep, color: palette.background }}
                    >
                        {processing ? 'Slanje...' : 'Pošalji'}
                    </button>
                </form>
            </div>
        </div>
    );
}
