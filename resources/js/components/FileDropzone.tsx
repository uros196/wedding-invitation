import { useRef, useState } from 'react';
import type { CSSProperties, DragEvent, KeyboardEvent, ReactNode } from 'react';

export interface FileDropzoneRenderProps {
    isDragActive: boolean;
}

export interface FileDropzoneProps {
    accept?: string;
    multiple?: boolean;
    onFilesSelected: (files: FileList) => void;
    children: ReactNode | ((props: FileDropzoneRenderProps) => ReactNode);
    className?: string | ((props: FileDropzoneRenderProps) => string);
    style?: CSSProperties | ((props: FileDropzoneRenderProps) => CSSProperties);
}

/** Reusable keyboard-accessible file picker with drag-and-drop support. */
export default function FileDropzone({
    accept,
    multiple = false,
    onFilesSelected,
    children,
    className,
    style,
}: FileDropzoneProps) {
    const fileInput = useRef<HTMLInputElement>(null);
    const [isDragActive, setIsDragActive] = useState(false);
    const openFileDialog = (): void => {
        fileInput.current?.click();
    };
    const renderProps = { isDragActive };
    const resolvedClassName =
        typeof className === 'function' ? className(renderProps) : className;
    const resolvedStyle =
        typeof style === 'function' ? style(renderProps) : style;
    const handleKeyDown = (event: KeyboardEvent<HTMLDivElement>): void => {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }

        event.preventDefault();
        openFileDialog();
    };
    const handleDragLeave = (event: DragEvent<HTMLDivElement>): void => {
        if (
            event.relatedTarget instanceof Node &&
            event.currentTarget.contains(event.relatedTarget)
        ) {
            return;
        }

        setIsDragActive(false);
    };

    return (
        <div
            role="button"
            tabIndex={0}
            onClick={openFileDialog}
            onKeyDown={handleKeyDown}
            onDragEnter={(event) => {
                event.preventDefault();
                setIsDragActive(true);
            }}
            onDragOver={(event) => event.preventDefault()}
            onDragLeave={handleDragLeave}
            onDrop={(event) => {
                event.preventDefault();
                setIsDragActive(false);
                onFilesSelected(event.dataTransfer.files);
            }}
            className={resolvedClassName}
            style={resolvedStyle}
        >
            <input
                ref={fileInput}
                type="file"
                multiple={multiple}
                accept={accept}
                className="hidden"
                onClick={(event) => event.stopPropagation()}
                onChange={(event) => {
                    if (event.target.files) {
                        onFilesSelected(event.target.files);
                    }

                    event.target.value = '';
                }}
            />
            {typeof children === 'function' ? children(renderProps) : children}
        </div>
    );
}
