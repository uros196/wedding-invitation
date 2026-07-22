import { useForm } from '@inertiajs/react';
import { Circle, CircleCheckBig, Heart, UserRoundPlus} from 'lucide-react';
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
        full_name: string;
    };
}

/**
 * RSVP form. Guests toggle their attendance, may add a plus one (when the
 * group allows it) and can leave a message for the couple. The data is posted
 * to the `group.confirm` route via Inertia.
 */
export default function RSVPForm({ group, rsvpDeadline }: RSVPFormProps) {
    const [plusOneExpanded, setPlusOneExpanded] = useState(false);

    const {
        data,
        setData,
        post,
        processing,
        errors,
        wasSuccessful,
        transform,
    } = useForm<RSVPFormData>({
        confirmed_guest_ids: group.guests
            .filter((guest) => guest.is_accepted)
            .map((guest) => guest.id),
        message: '',
        plus_one: { full_name: '' },
    });

    const hasPlusOneData = Boolean(data.plus_one.full_name);
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

    const singleGuestLabel = (selected: bool) => {
        return selected ? 'Potvrdjeno' : 'Potvrdi dolazak';
    };

    if (wasSuccessful) {
        return (
            <div
                className="relative z-20 flex w-full flex-col items-center gap-4 px-0 py-12 text-center sm:px-6"
                style={{
                    backgroundColor: palette.background,
                    fontFamily: fonts.serif,
                }}
            >
                <Heart size={28} style={{ color: palette.celestial }} />
                <p
                    className="text-2xl font-medium tracking-wide italic"
                    style={{ color: palette.celestial }}
                >
                    Hvala na potvrdi!
                </p>
            </div>
        );
    }

    return (
        <div
            className="relative z-20 px-6 py-12"
            style={{
                backgroundColor: palette.background,
                fontFamily: fonts.serif,
            }}
        >
            <div
                className="mx-auto max-w-md rounded-2xl p-6 sm:p-8"
                style={{
                    backgroundColor: 'rgba(255, 255, 255, 0.3)',
                    border: '1px solid rgba(67, 58, 102, 0.15)',
                }}
            >
                <h3
                    className="mb-2 text-center text-3xl font-medium tracking-wide"
                    style={{ color: palette.deep }}
                >
                    Potvrda dolaska
                </h3>
                <p
                    className="mb-8 text-center text-base"
                    style={{ color: palette.dawn }}
                >
                    Molimo vas da potvrdite do {rsvpDeadline}.
                </p>

                <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                    {/* Guest list */}
                    <div className="flex flex-col gap-2">
                        <label
                            className="text-sm tracking-wider"
                            style={{ color: palette.deep }}
                        >
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
                        {errors.confirmed_guest_ids && (
                            <p className="text-sm text-red-500">
                                {errors.confirmed_guest_ids}
                            </p>
                        )}
                    </div>

                    {/* Plus one */}
                    {group.has_plus_one && (
                        <div className="flex flex-col gap-2">
                            <button
                                type="button"
                                onClick={() =>
                                    setPlusOneExpanded((expanded) => !expanded)
                                }
                                className="group mt-6 flex h-15 w-full cursor-pointer items-center justify-between rounded-2xl border border-gray-200 bg-gray-100 px-6 transition-all duration-200 hover:border-gray-300 hover:bg-gray-200"
                                style={{ color: palette.celestial }}
                            >
                                {/* Tekst */}
                                <span className="text-lg font-medium select-none">
                                    Imaš pratnju?
                                </span>

                                <UserRoundPlus
                                    size={30}
                                    className="text-#0B2F5B-100 group-hover:bg-#9875A6-50 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-none transition-transform duration-500 ease-in-out"
                                />
                            </button>

                            {plusOneExpanded && (
                                <div className="mt-2 flex animate-in flex-col gap-4 overflow-hidden pt-3 duration-500 fade-in slide-in-from-top-2">
                                    <FloatingLabelInput
                                        id="plus_one_first_name"
                                        label="Ime i prezime pratnje"
                                        value={data.plus_one.full_name}
                                        onChange={(event) =>
                                            setData('plus_one', {
                                                ...data.plus_one,
                                                full_name: event.target.value,
                                            })
                                        }
                                        error={errors['plus_one.full_name']}
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
                        onChange={(event) =>
                            setData('message', event.target.value)
                        }
                        error={errors.message}
                    />

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-lg py-4 text-sm tracking-widest uppercase transition-opacity duration-300 disabled:opacity-60"
                        style={{
                            backgroundColor: palette.deep,
                            color: palette.background,
                        }}
                    >
                        {processing ? (
                            'Slanje...'
                        ) : (
                            <span className="inline-flex items-center gap-4">
                                Pošalji
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="2"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                >
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </span>
                        )}
                    </button>
                </form>
            </div>
        </div>
    );
}
