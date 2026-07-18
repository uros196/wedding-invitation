import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Heart, Plus, CircleCheck, CircleCheckBig } from 'lucide-react';

const DEEP = '#0B2F5B';
const CELESTIAL = '#433A66';
const DAWN = '#9875A6';
const BG = '#EEF1F5';

const inputStyle = {
    color: CELESTIAL,
    backgroundColor: BG,
    borderColor: CELESTIAL,
};

interface GuestData {
    id: number;
    name: string;
}

interface RSVPFormProps {
    guestList?: GuestData[];
}

export default function RSVPForm({ guestList = [] }: RSVPFormProps) {
    const [guests, setGuests] = useState(() => {
        return guestList.length > 0
            ? guestList.map((g) => ({
                  id: g.id,
                  label: g.name,
                  selected: false,
              }))
            : [{ id: 0, label: 'Ime i prezime', selected: false }];
    });

    const [plusOneExpanded, setPlusOneExpanded] = useState(false);
    const [plusOneName, setPlusOneName] = useState('');
    const [message, setMessage] = useState('');

    const [submitted, setSubmitted] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const toggleGuest = (id: number) => {
        setGuests(
            guests.map((g) =>
                g.id === id ? { ...g, selected: !g.selected } : g,
            ),
        );
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError(null);

        const selectedGuests = guests
            .filter((g) => g.selected)
            .map((g) => g.label);

        if (selectedGuests.length === 0 && !plusOneName) return;

        setLoading(true);
        try {
            console.log('Slanje podataka:', {
                guest_names: selectedGuests.join(', '),
                plus_one_name: plusOneExpanded ? plusOneName : 'Nema pratnje',
                message: message || undefined,
            });
            setSubmitted(true);
        } catch {
            setError('Pokušajte ponovo.');
        } finally {
            setLoading(false);
        }
    };

    if (submitted) {
        return (
            <div
                className="relative z-20 flex w-full flex-col items-center gap-4 bg-[#EEF1F5] px-0 py-12 text-center sm:px-6"
                style={{ fontFamily: "'Playfair Display', serif" }}
            >
                <Heart size={28} style={{ color: CELESTIAL }} />
                <p
                    className="text-2xl font-medium tracking-wide italic"
                    style={{ color: CELESTIAL }}
                >
                    Hvala na potvrdi!
                </p>
            </div>
        );
    }

    return (
        <div
            className="relative z-20 bg-[#EEF1F5] px-6 py-12"
            style={{ fontFamily: "'Playfair Display', serif" }}
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
                    style={{ color: CELESTIAL }}
                >
                    Potvrda dolaska
                </h3>
                <p
                    className="mb-8 text-center text-base"
                    style={{ color: CELESTIAL }}
                >
                    Molimo vas da potvrdite do 01. septembra 2026.
                </p>

                <form onSubmit={handleSubmit} className="flex flex-col gap-5">

                    {/* DINAMIČKA LISTA GOSTIJU */}
                    <div className="flex flex-col gap-2">
                        <label
                            className="text-sm tracking-wider opacity-80"
                            style={{ color: DAWN }}
                        >
                            Potvrdite dolazak za:
                        </label>
                        <div className="flex flex-col gap-3">
                            {guests.map((guest) => (
                                <button
                                    key={guest.id}
                                    type="button"
                                    onClick={() => toggleGuest(guest.id)}

                                    className="relative flex w-full items-center justify-center rounded-lg px-4 py-3.5 text-base font-medium tracking-wide transition-all duration-300"
                                    style={{
                                        backgroundColor: guest.selected
                                            ? DEEP
                                            : 'rgba(255, 255, 255, 0.5)',
                                        color: guest.selected ? BG : CELESTIAL,
                                        border: '1px solid rgba(67, 58, 102, 0.2)',
                                    }}
                                >
                                    <div className="absolute left-4 flex items-center">
                                        {guest.selected ? (
                                            <CircleCheckBig
                                                size={18}
                                                style={{ color: BG }}
                                                className="flex-shrink-0"
                                            />
                                        ) : (
                                            <CircleCheck
                                                size={18}
                                                style={{ color: CELESTIAL }}
                                                className="flex-shrink-0"
                                            />
                                        )}
                                    </div>

                                    <span className="truncate">
                                        {guest.label}
                                    </span>
                                </button>
                               
                            ))}
                        </div>
                    </div>

                    {/* PLUS ONE LOGIKA */}
                    <div className="flex flex-col gap-2">
                        <button
                            type="button"
                            onClick={() => setPlusOneExpanded(!plusOneExpanded)}
                            className="flex items-center gap-2 self-start text-base font-medium transition-all duration-300"
                            style={{ color: CELESTIAL }}
                        >
                            Imaš pratnju?
                            <Plus
                                size={14}
                                className="transition-transform duration-500"
                                style={{
                                    transform: plusOneExpanded
                                        ? 'rotate(45deg)'
                                        : 'rotate(0deg)',
                                }}
                            />
                        </button>

                        {plusOneExpanded && (
                            <div className="animate-in overflow-hidden transition-all duration-500 ease-in-out fade-in slide-in-from-top-2">
                                <div className="relative mt-5">
                                    <Input
                                        id="plusOneInput"
                                        placeholder=" "
                                        value={plusOneName}
                                        onChange={(e) =>
                                            setPlusOneName(e.target.value)
                                        }
                                        className="peer h-11 w-full rounded-lg px-4 text-base"
                                        style={inputStyle}
                                    />
                                    <label
                                        htmlFor="plusOneInput"
                                        className="pointer-events-none absolute top-2.5 left-4 cursor-text text-xs text-gray-400 transition-all duration-200 peer-focus:-top-5 peer-focus:text-xs peer-focus:text-[#9875A6] peer-focus:not-italic peer-[:not(:placeholder-shown)]:-top-5 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:text-[#9875A6] peer-[:not(:placeholder-shown)]:not-italic"
                                    >
                                        Unesite ime i prezime pratnje
                                    </label>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* PORUKA GOSTA */}
                    <div className="flex flex-col gap-2">
                        <div className="relative mt-5">
                            <Textarea
                                id="messageInput"
                                placeholder=" "
                                value={message}
                                onChange={(e) => setMessage(e.target.value)}
                                className="peer min-h-[80px] rounded-lg px-4 pt-2.5 text-base"
                                style={inputStyle}
                            />
                            <label
                                htmlFor="messageInput"
                                className="pointer-events-none absolute top-2.5 left-4 cursor-text text-xs text-gray-400 transition-all duration-200 peer-focus:-top-5 peer-focus:text-xs peer-focus:text-[#9875A6] peer-focus:not-italic peer-[:not(:placeholder-shown)]:-top-5 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:text-[#9875A6] peer-[:not(:placeholder-shown)]:not-italic"
                            >
                                Poruka za mladence
                            </label>
                        </div>
                    </div>

                    {error && (
                        <p className="text-center text-sm text-red-500">
                            {error}
                        </p>
                    )}

                    <Button
                        type="submit"
                        disabled={loading}
                        className="w-full rounded-lg py-5 text-sm font-semibold tracking-widest uppercase"
                        style={{ backgroundColor: DEEP, color: BG }}
                    >
                        {loading ? 'Slanje...' : 'Pošalji'}
                    </Button>
                </form>
            </div>
        </div>
    );
}
