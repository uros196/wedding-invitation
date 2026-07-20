import * as React from 'react';

import { cn } from '@/lib/utils';

/**
 * Shared classes for the floating label. The label starts as a placeholder and,
 * on focus or when the field holds a value, shrinks and moves up so it "cuts"
 * the input border. A solid background matching the field is what hides the
 * border line behind the label text.
 */
const floatingLabelBase =
    'pointer-events-none absolute left-3 z-10 bg-[#EEF1F5]  tracking-wider px-1  text-[#0B2F5B] transition-all duration-200 ease-out';

const fieldBase = 'peer w-full rounded-lg text-sm outline-none transition-colors border border-[rgba(67,58,102,0.2)] bg-white/50 text-base text-[#0B2F5B] focus:border-[#0B2F5B]';
interface FloatingLabelInputProps extends Omit<React.ComponentProps<'input'>, 'placeholder'> {
    id: string;
    label: string;
    error?: string | null;
}

/**
 * Text input with an elegant floating label.
 */
export function FloatingLabelInput({ id, label, error, className, ...props }: FloatingLabelInputProps) {
    return (
        <div className="flex flex-col">
            <div className="relative">
                <input
                    id={id}
                    placeholder=" "
                    aria-invalid={error ? true : undefined}
                    className={cn(
                        fieldBase,
                        'h-12 px-4',
                        error && 'border-red-500 focus:border-red-500',
                        className,
                    )}
                    {...props}
                />
                <label
                    htmlFor={id}
                    className={cn(
                        floatingLabelBase,
                        'backdrop-blur-sm bg-#EEF1F5 px-1 rounded',
                        'top-1/2 -translate-y-1/2 text-sm',
                        'peer-focus:top-0 peer-focus:bg-[#F3F5F8]',
                        'peer-[:not(:placeholder-shown)]:top-0 ',
                    )}
                >
                    {label}
                </label>
            </div>
            {error && <p className="mt-1 pl-1 text-xs text-red-500">{error}</p>}
        </div>
    );
}

interface FloatingLabelTextareaProps extends Omit<React.ComponentProps<'textarea'>, 'placeholder'> {
    id: string;
    label: string;
    error?: string | null;
}

/**
 * Multi-line input with the same floating label behaviour as {@link FloatingLabelInput}.
 */
export function FloatingLabelTextarea({ id, label, error, className, ...props }: FloatingLabelTextareaProps) {
    return (
        <div className="flex flex-col">
            <div className="relative">
                <textarea
                    id={id}
                    placeholder=" "
                    aria-invalid={error ? true : undefined}
                    className={cn(
                        fieldBase,
                        'min-h-24 resize-none px-4 pt-3',
                        error && 'border-red-500 focus:border-red-500',
                        className,
                    )}
                    {...props}
                />
                <label
                    htmlFor={id}
                    className={cn(
                        floatingLabelBase,
                        'backdrop-blur-sm bg-#EEF1F5 px-1 rounded',
                        'top-3 text-sm',
                        'peer-focus:-top-2 peer-focus:text-sm peer-focus:bg-[#F3F5F8]',
                        'peer-[:not(:placeholder-shown)]:-top-2 peer-[:not(:placeholder-shown)]:text-sm',
                    )}
                >
                    {label}
                </label>
            </div>
            {error && <p className="mt-1 pl-1 text-xs text-red-500">{error}</p>}
        </div>
    );
}
