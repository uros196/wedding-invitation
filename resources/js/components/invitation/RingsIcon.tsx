interface RingsIconProps {
  size?: number;
  strokeWidth?: number;
  style?: React.CSSProperties;
}

export default function RingsIcon({ size = 24, strokeWidth = 1, style }: RingsIconProps) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth={strokeWidth}
      strokeLinecap="round"
      strokeLinejoin="round"
      style={style}
    >
      {/* Levi prsten - samo jedna linija */}
      <circle cx="8.5" cy="13.5" r="5" />

      {/* Desni prsten - samo jedna linija */}
      <circle cx="14.5" cy="15.5" r="5" />

      {/* Srce iznad prstenja */}
      <path 
        d="M14.5 5c-.6 0-1.1.3-1.5.8-.4-.5-.9-.8-1.5-.8-1.1 0-2 .9-2 2 0 1.6 2.3 3.4 3.1 4 .2.1.6.1.8 0 .8-.6 3.1-2.4 3.1-4 0-1.1-.9-2-2-2z" 
        fill="none"
      />
    </svg>
  );
}