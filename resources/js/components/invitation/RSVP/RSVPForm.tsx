import { Form } from '@inertiajs/react';
import { useState } from 'react';
import { FloatingLabelTextarea } from '@/components/invitation/FloatingField';
import GuestSelection from '@/components/invitation/RSVP/GuestSelection';
import PlusOneField from '@/components/invitation/RSVP/PlusOneField';
import RSVPSubmitButton from '@/components/invitation/RSVP/RSVPSubmitButton';
import { fonts, palette } from '@/components/invitation/theme';
import { confirm } from '@/routes/group';
import type { Group } from '@/types';
import RSVPConfirmation from './RSVPConfirmation';

interface RSVPFormProps {
    group: Group;
    /** Formatted RSVP deadline shown in the intro copy. */
    rsvpDeadline: string;
}

/**
 * RSVP form. Guests toggle their attendance, may add a plus one (when the
 * group allows it) and can leave a message for the couple. The data is posted
 * to the `group.confirm` route via Inertia.
 */
export default function RSVPForm({ group, rsvpDeadline }: RSVPFormProps) {
    const [confirmedGuestIds, setConfirmedGuestIds] = useState<number[]>(
        group.guests
            .filter((guest) => guest.is_accepted)
            .map((guest) => guest.id),
    );
    const [plusOneExpanded, setPlusOneExpanded] = useState(false);
    const [plusOneName, setPlusOneName] = useState('');

    const includePlusOne =
        group.has_plus_one && plusOneExpanded && Boolean(plusOneName);

    const toggleGuest = (id: number) => {
        setConfirmedGuestIds((guestIds) =>
            guestIds.includes(id)
                ? guestIds.filter((guestId) => guestId !== id)
                : [...guestIds, id],
        );
    };

    const togglePlusOne = () => {
        setPlusOneExpanded((expanded) => !expanded);
    };

    return (
        <Form
            action={confirm.url(group.uuid)}
            method="post"
            options={{ preserveScroll: true }}
            transform={(formData) => ({
                confirmed_guest_ids: confirmedGuestIds,
                message:
                    typeof formData.message === 'string'
                        ? formData.message.trim() || null
                        : null,
                ...(includePlusOne
                    ? { plus_one: { full_name: plusOneName } }
                    : {}),
            })}
        >
            {({ errors, processing, wasSuccessful }) => {
                if (wasSuccessful) {
                    return <RSVPConfirmation />;
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

                            <div className="flex flex-col gap-5">
                                {confirmedGuestIds.map((guestId) => (
                                    <input
                                        key={guestId}
                                        type="hidden"
                                        name="confirmed_guest_ids[]"
                                        value={guestId}
                                    />
                                ))}

                                <GuestSelection
                                    group={group}
                                    confirmedGuestIds={confirmedGuestIds}
                                    onToggleGuest={toggleGuest}
                                    error={errors.confirmed_guest_ids}
                                />

                                {group.has_plus_one && (
                                    <PlusOneField
                                        expanded={plusOneExpanded}
                                        name={plusOneName}
                                        onToggle={togglePlusOne}
                                        onNameChange={setPlusOneName}
                                        error={errors['plus_one.full_name']}
                                    />
                                )}

                                <FloatingLabelTextarea
                                    id="message"
                                    label="Poruka za mladence"
                                    name="message"
                                    error={errors.message}
                                />

                                <RSVPSubmitButton processing={processing} />
                            </div>
                        </div>
                    </div>
                );
            }}
        </Form>
    );
}
