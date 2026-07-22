import { Send } from 'lucide-react';
import type { ReactNode } from 'react';
import { palette } from './../theme';

interface RSVPSubmitButtonProps {
    processing: boolean;
    processingText?: ReactNode;
    children?: ReactNode;
}

export default function RSVPSubmitButton({
    processing,
    processingText = 'Slanje...',
    children
}: RSVPSubmitButtonProps) {
    return (
        <button
            type="submit"
            disabled={processing}
            className="w-full rounded-lg py-4 text-sm tracking-widest uppercase transition-opacity duration-300 hover:cursor-pointer disabled:opacity-60"
            style={{
                backgroundColor: palette.deep,
                color: palette.background,
            }}
        >
            {processing ? processingText : children ?? (
                <span className="inline-flex items-center gap-4">
                    Pošalji <Send size={14} />
                </span>
            )}
        </button>
    );
}
